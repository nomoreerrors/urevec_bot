<?php

namespace App\Models;

use App\Models\Eloquent\BotChat;
use App\Models\MessageModels\MediaModels\BaseMediaModel;
use App\Models\StatusUpdates\StatusUpdateModel;
use Illuminate\Support\Facades\Log;
use App\Models\StatusUpdates\NewMemberJoinUpdateModel;
use App\Models\StatusUpdates\InvitedUserUpdateModel;
use App\Models\MessageModels\MediaModels\PhotoMediaModel;
use App\Models\Reactions\MessageReactionModel;
use App\Models\MessageModels\MediaModels\VideoMediaModel;
use App\Models\MessageModels\MediaModels\MultiMediaModel;
use App\Models\MessageModels\MediaModels\VoiceMediaModel;
use App\Models\MessageModels\MessageModel;
use App\Models\MessageModels\TextMessageModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Exceptions\BaseTelegramBotException;
use App\Services\TelegramBotService;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
use App\Services\CONSTANTS;
use Exception;

class BaseTelegramRequestModel extends Model
{
    use HasFactory;

    protected string $messageType = "";

    protected string $chatTitle = "";

    protected bool $fromAdmin = false;

    protected string $chatType = "";

    protected int $chatId = 0;

    protected array $adminsIds = [];

    protected string $fromUserName = "";
    /** Message sender id */
    protected int $fromId = 0;

    protected $http;

    public function __construct(protected array $data)
    {
        $this->data = $data;
        $this->http = new Http();
        $this->setMessageType()
            ->setChatId()
            ->setChatType()
            ->setAdminsIds()
            ->setFromId()
            ->setFromAdmin()
            ->setChatTitle()
            ->setFromUserName();
    }

    public function __get($key)
    {
        throw new Exception("Свойство: " . (string) $key . ". " . "Попытка обратиться к свойству напрямую без get,
         к несуществующему или приватному свойству.");
    }

    /**
     * Summary of create
     * @throws BaseTelegramBotException
     * @return \App\Models\BaseTelegramRequestModel
     */
    function getModel(): BaseTelegramRequestModel
    {
        try {
            $model = $this->createMessageModel()
                ->createTextMessageModel()
                ->createStatusUpdateModel()
                ->createNewMemberJoinUpdateModel()
                ->createInvitedUserUpdateModel()
                ->createMessageReactionUpdateModel()
                ->createMediaModel();

            return $model;

        } catch (Exception $e) {
            $this->propertyErrorHandler($e->getMessage(), $e->getLine(), __METHOD__);
        }
        return $model;
    }

    /**
     * Summary of propertyErrorHandler
     * @param string $message
     * @throws \App\Exceptions\BaseTelegramBotException
     * @return never
     */
    protected function propertyErrorHandler(string $message = "", $line, $method): void
    {
        $text = CONSTANTS::EMPTY_PROPERTY . "DEFAULT EXCEPTION REASON: " . $message . " LINE: " . $line . PHP_EOL . $method . PHP_EOL .
            "MESSAGE_TYPE PROPERTY: " . $this->messageType . PHP_EOL .
            "FROM_ADMIN PROPERTY: " . $this->fromAdmin . PHP_EOL .
            "FROM_ID PROPERTY: " . $this->fromId . PHP_EOL .
            "FROM_USER_NAME PROPERTY: " . $this->fromUserName . PHP_EOL .
            "CHAT_ID PROPERTY: " . $this->chatId . PHP_EOL;
        // dd($text);

        throw new BaseTelegramBotException($text, __METHOD__);
    }

    /**
     * Summary of createMessageReactionUpdateModel
     * @return BaseTelegramRequestModel|MessageReactionModel
     */
    private function createMessageReactionUpdateModel(): BaseTelegramRequestModel
    {
        if ($this->messageType === "message_reaction") {
            return new MessageReactionModel($this->data);
        }
        return $this;
    }


    /**
     * Summary of createTextMessageModel
     * @return \App\Models\BaseTelegramRequestModel|\App\Models\MessageModels\TextMessageModel
     */
    private function createTextMessageModel(): BaseTelegramRequestModel|TextMessageModel
    {
        $type = "";
        if ($this->messageType === "message") {
            $type = "message";
        }

        if ($this->messageType === "edited_message") {
            $type = "edited_message";
        }

        if (
            array_key_exists($type, $this->data) &&
            array_key_exists("text", $this->data[$type])
        ) {
            return new TextMessageModel($this->data);
        }
        return $this;
    }

    /**
     * Summary of createMessageModel
     * @return \App\Models\BaseTelegramRequestModel|\App\Models\MessageModels\MessageModel
     */
    private function createMessageModel(): BaseTelegramRequestModel|MessageModel
    {
        $type = "";


        if ($this->messageType === "message") {
            $type = "message";
        }

        if ($this->messageType === "edited_message") {
            $type = "edited_message";
        }

        if (empty($type)) {
            return $this;
        }

        $hasVIdeo = array_key_exists("video", $this->data[$type]);
        $hasPhoto = array_key_exists("photo", $this->data[$type]);
        $hasVoice = array_key_exists("voice", $this->data[$type]);
        $hasText = array_key_exists("text", $this->data[$type]);

        if (
            !$hasText &&
            !$hasVIdeo &&
            !$hasPhoto &&
            !$hasVoice
        ) {
            return new MessageModel($this->data);
        }
        return $this;
    }



    private function createStatusUpdateModel(): BaseTelegramRequestModel|StatusUpdateModel
    {
        if ($this->messageType === "chat_member") {
            if (
                $this->data["chat_member"]["new_chat_member"]["status"] !==
                "member" ||
                ($this->fromAdmin &&
                    $this->data["chat_member"]["old_chat_member"]["status"] === "restricted" &&
                    $this->data["chat_member"]["new_chat_member"]["status"] === "member")
            ) {
                return new StatusUpdateModel($this->data);
            }
        }
        return $this;
    }

    /**
     * Summary of createNewMemberJoinUpdateModel
     * @return \App\Models\BaseTelegramRequestModel
     */
    private function createNewMemberJoinUpdateModel(): BaseTelegramRequestModel
    {
        if ($this->messageType === "chat_member") {
            if (
                !$this->fromAdmin &&
                $this->data["chat_member"]["from"]["id"] ===
                $this->data["chat_member"]["new_chat_member"]["user"]["id"] &&
                $this->data["chat_member"]["old_chat_member"]["status"] === "left" &&
                $this->data["chat_member"]["new_chat_member"]["status"] === "member"
            ) {
                return new NewMemberJoinUpdateModel($this->data);
            }
        }
        return $this;
    }

    /**
     * Summary of createInvitedUserUpdateModel
     * @return BaseTelegramRequestModel|InvitedUserUpdateModel
     */
    private function createInvitedUserUpdateModel(): BaseTelegramRequestModel
    {
        if ($this->messageType === "chat_member") {
            if (
                $this->data["chat_member"]["from"]["id"] !==
                $this->data["chat_member"]["new_chat_member"]["user"]["id"] &&
                $this->data["chat_member"]["new_chat_member"]["status"] === "member" &&
                $this->data["chat_member"]["old_chat_member"]["status"] !== "restricted"

            ) {

                return new InvitedUserUpdateModel($this->data);
            }
        }
        return $this;
    }

    /**
     * Summary of createMediaModel
     * @return mixed
     */
    private function createMediaModel(): mixed
    {
        $type = $this->messageType;
        $hasVideoKey = array_key_exists("video", $this->data[$type]);
        $hasVoiceKey = array_key_exists("voice", $this->data[$type]);
        $hasVideoKey = array_key_exists("video", $this->data[$type]);
        $hasPhotoKey = array_key_exists("photo", $this->data[$type]);

        if ($hasVideoKey && $hasPhotoKey) {
            return new MultiMediaModel($this->data);
        }

        if ($hasPhotoKey && !$hasVideoKey) {
            return new PhotoMediaModel($this->data);
        }

        if ($hasVideoKey && !$hasPhotoKey) {
            return new VideoMediaModel($this->data);
        }

        if ($hasVoiceKey) {
            return new VoiceMediaModel($this->data);
        }

        if ($hasVideoKey && $hasPhotoKey) {
            new MultiMediaModel($this->data);
        }

        return $this;
    }

    protected function setFromId()
    {
        try {
            // log::info(json_encode($this->data));
            $type = "";
            if (array_key_exists("from", $this->data[$this->messageType])) {
                $type = "from";
            }

            if (array_key_exists("user", $this->data[$this->messageType])) {
                $type = "user";
            }

            if (array_key_exists("actor_chat", $this->data[$this->messageType])) {
                $type = "actor_chat";
            }

            if (array_key_exists($type, $this->data[$this->messageType])) {
                $this->fromId = $this->data[$this->messageType][$type]["id"];
            }
        } catch (Exception $e) {
            $this->propertyErrorHandler($e->getMessage(), $e->getLine(), __METHOD__);
        }
        return $this;
    }

    protected function setFromAdmin()
    {
        try {
            if ((string) in_array($this->fromId, $this->adminsIds)) {
                $this->fromAdmin = true;
                return $this;
            }
        } catch (Exception $e) {
            $this->propertyErrorHandler($e->getMessage() . "1. Возможно не установлено свойство chatId или 
            не удалены объекты из тестовой базы", $e->getLine(), __METHOD__);
        }

        return $this;
    }

    protected function setChatType(): static
    {
        if (!array_key_exists("chat", $this->data[$this->messageType])) {
            $this->propertyErrorHandler("chatType свойство не установлено", __LINE__, __METHOD__);
        }

        $this->chatType = $this->data[$this->messageType]["chat"]["type"];
        return $this;
    }

    protected function setMessageType()
    {
        if (array_key_exists("message", $this->data)) {
            $this->messageType = "message";
        } elseif (array_key_exists("edited_message", $this->data)) {
            $this->messageType = "edited_message";
        } elseif (array_key_exists("my_chat_member", $this->data)) {
            $this->messageType = "my_chat_member";
        } elseif (array_key_exists("chat_member", $this->data)) {
            $this->messageType = "chat_member";
        } elseif (array_key_exists("message_reaction", $this->data)) {
            $this->messageType = "message_reaction";
        } elseif (array_key_exists("message_reaction_count", $this->data)) {
            $this->messageType = "message_reaction_count";
        } else {
            $this->messageType = "unknown_type";
            $this->propertyErrorHandler("messageType", __LINE__, __METHOD__);
        }

        return $this;
    }

    protected function setChatId(): static
    {
        try {
            $this->chatId = $this->data[$this->messageType]["chat"]["id"];
        } catch (Exception $e) {
            $this->propertyErrorHandler($e->getMessage(), $e->getLine(), __METHOD__);
        }
        return $this;
    }

    protected function setFromUserName()
    {
        try {
            $type = "";
            if (array_key_exists("from", $this->data[$this->messageType])) {
                $type = "from";
            }

            if (array_key_exists("user", $this->data[$this->messageType])) {
                $type = "user";
            }

            if (array_key_exists("actor_chat", $this->data[$this->messageType])) {
                $type = "actor_chat";
            }

            $this->fromUserName = $this->data[$this->messageType][$type][$type == "actor_chat" ? "title" : "first_name"];
        } catch (Exception $e) {
            $this->propertyErrorHandler($e->getMessage(), $e->getLine(), __METHOD__);
        }
        return $this;
    }

    /**
     * Summary of setAdminsIds
     * @param \Illuminate\Support\Facades\Http $http
     * @return BaseTelegramRequestModel
     * @method  json($key = null, $default = null)
     */
    private function setAdminsIds(): static
    {
        if ($this->chatType === "private") {
            return $this;
        }

        $this->adminsIds = (new BotChat())
            ->where("chat_id", $this->chatId)
            ->first()?->chat_admins ?? [];

        if (!empty($this->adminsIds)) {
            return $this;
        }

        $response = $this->http::post(
            env('TELEGRAM_API_URL') . env('TELEGRAM_API_TOKEN') . "/getChatAdministrators",
            ['chat_id' => $this->chatId]
        )->json();
        // dd($response);

        if (!$response['ok']) {
            throw new BaseTelegramBotException(CONSTANTS::GET_ADMINS_FAILED, __METHOD__);
        }

        $this->adminsIds = array_map(function ($item) {
            return $item['user']['id'];
        }, $response['result']);

        return $this;
    }

    public function setChatTitle()
    {
        $this->chatTitle = $this->data[$this->messageType]["chat"]["title"];
        return $this;
    }

    public function getChatType(): string
    {
        return $this->chatType;
    }

    public function getFromId(): int
    {
        return $this->fromId;
    }

    public function getAdminsIds(): array
    {
        return $this->adminsIds;
    }

    public function getType(): string
    {
        return $this->messageType;
    }

    public function getFromUserName(): string
    {
        return $this->fromUserName;
    }

    public function getFromAdmin(): bool
    {
        return $this->fromAdmin;
    }

    public function getChatTitle(): string
    {
        return $this->chatTitle;
    }

    public function getChatId(): int
    {
        return $this->chatId;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getJsonData(): string
    {
        return json_encode($this->data);
    }

}
