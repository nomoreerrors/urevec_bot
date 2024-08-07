<?php

namespace App\Models;


use App\Models\Chat;
use App\Models\MessageModels\MediaModels\BaseMediaModel;
use App\Models\StatusUpdates\StatusUpdateModel;
use App\Services\BotErrorNotificationService;
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

class TelegramRequestModelBuilder
{
    use HasFactory;

    protected static array $data;

    protected static string $messageType = "";

    protected static string $chatTitle = "";

    protected static bool $fromAdmin = false;

    protected static string $chatType = "";

    protected static int $chatId = 0;

    protected static array $adminsIds = [];

    /** Contains all admins statuses and permissions */
    protected static array $admins = [];

    protected static string $fromUserName = "";
    /** Message sender id */
    protected static int $fromId = 0;

    protected $http;

    public function __construct(array $data)
    {
        self::$data = $data;
        $this->http = new Http();
        $this->setMessageType()
            ->setChatId()
            ->setChatType()
            ->setAdmins()
            ->setFromId()
            ->setFromAdmin()
            ->setChatTitle()
            ->setFromUserName();

    }

    public function __get($key)
    {
        if (!($this instanceof self)) {
            throw new Exception("Свойство: " . (string) $key . ". " . "Попытка обратиться к свойству напрямую без get,
         к несуществующему или приватному свойству.");
        }
    }

    /**
     * Summary of create
     * @return mixed
     * @throws BaseTelegramBotException
     */
    public function create()
    {
        try {
            $model = $this->createMessageModel();
            if (get_class($model) !== TelegramRequestModelBuilder::class) {
                return $model;
            }
            $model = $this->createMessageModel();
            if (get_class($model) !== TelegramRequestModelBuilder::class) {
                return $model;
            }
            $model = $this->createTextMessageModel();
            if (get_class($model) !== TelegramRequestModelBuilder::class) {
                return $model;
            }
            $model = $this->createStatusUpdateModel();
            if (get_class($model) !== TelegramRequestModelBuilder::class) {
                return $model;
            }
            $model = $this->createNewMemberJoinUpdateModel();
            if (get_class($model) !== TelegramRequestModelBuilder::class) {
                return $model;
            }
            $model = $this->createInvitedUserUpdateModel();
            if (get_class($model) !== TelegramRequestModelBuilder::class) {
                return $model;
            }
            $model = $this->createMessageReactionUpdateModel();
            if (get_class($model) !== TelegramRequestModelBuilder::class) {
                return $model;
            }
            $model = $this->createMediaModel();
            if (get_class($model) !== TelegramRequestModelBuilder::class) {
                return $model;
            }

            $this->propertyErrorHandler("UNKNOWN_MODEL", __LINE__, __METHOD__);

        } catch (Exception $e) {
            $this->propertyErrorHandler($e->getMessage(), $e->getLine(), __METHOD__);
        }
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
            "MESSAGE_TYPE PROPERTY: " . self::$messageType . PHP_EOL .
            "FROM_ADMIN PROPERTY: " . self::$fromAdmin . PHP_EOL .
            "FROM_ID PROPERTY: " . self::$fromId . PHP_EOL .
            "FROM_USER_NAME PROPERTY: " . self::$fromUserName . PHP_EOL .
            "CHAT_ID PROPERTY: " . self::$chatId . PHP_EOL;
        // dd($text);

        throw new BaseTelegramBotException($text, __METHOD__);
    }

    /**
     * Summary of createMessageReactionUpdateModel
     * @return TelegramRequestModelBuilder|MessageReactionModel
     */
    private function createMessageReactionUpdateModel(): TelegramRequestModelBuilder
    {
        if (self::$messageType === "message_reaction") {
            return new MessageReactionModel();
        }
        return $this;
    }


    /**
     * Summary of createTextMessageModel
     * @return \App\Models\TelegramRequestModelBuilder|\App\Models\MessageModels\TextMessageModel
     */
    private function createTextMessageModel(): TelegramRequestModelBuilder|TextMessageModel
    {
        $type = "";
        if (self::$messageType === "message") {
            $type = "message";
        }

        if (self::$messageType === "edited_message") {
            $type = "edited_message";
        }

        if (
            array_key_exists($type, self::$data) &&
            array_key_exists("text", self::$data[$type])
        ) {
            return new TextMessageModel();
        }
        return $this;
    }

    /**
     * Summary of createMessageModel
     * @return \App\Models\TelegramRequestModelBuilder|\App\Models\MessageModels\MessageModel
     */
    private function createMessageModel(): TelegramRequestModelBuilder|MessageModel
    {
        $type = "";


        if (self::$messageType === "message") {
            $type = "message";
        }

        if (self::$messageType === "edited_message") {
            $type = "edited_message";
        }

        if (empty($type)) {
            return $this;
        }

        $hasVIdeo = array_key_exists("video", self::$data[$type]);
        $hasPhoto = array_key_exists("photo", self::$data[$type]);
        $hasVoice = array_key_exists("voice", self::$data[$type]);
        $hasText = array_key_exists("text", self::$data[$type]);

        if (
            !$hasText &&
            !$hasVIdeo &&
            !$hasPhoto &&
            !$hasVoice
        ) {
            return new MessageModel();
        }
        return $this;
    }

    private function createStatusUpdateModel(): TelegramRequestModelBuilder|StatusUpdateModel
    {
        if (self::$messageType === "chat_member") {
            if (
                self::$data["chat_member"]["old_chat_member"]["status"] !== "left" ||
                self::$data["chat_member"]["new_chat_member"]["status"] !== "member"
            ) {
                return new StatusUpdateModel();
            }
        }
        return $this;
    }

    /**
     * Summary of createNewMemberJoinUpdateModel
     * @return \App\Models\TelegramRequestModelBuilder
     */
    private function createNewMemberJoinUpdateModel(): TelegramRequestModelBuilder
    {
        if (self::$messageType === "chat_member") {
            if (
                !self::$fromAdmin &&
                self::$data["chat_member"]["from"]["id"] ===
                self::$data["chat_member"]["new_chat_member"]["user"]["id"] &&
                self::$data["chat_member"]["old_chat_member"]["status"] === "left" &&
                self::$data["chat_member"]["new_chat_member"]["status"] === "member"
            ) {
                return new NewMemberJoinUpdateModel();
            }
        }
        return $this;
    }

    /**
     * Summary of createInvitedUserUpdateModel
     * @return TelegramRequestModelBuilder|InvitedUserUpdateModel
     */
    private function createInvitedUserUpdateModel(): TelegramRequestModelBuilder
    {
        if (self::$messageType === "chat_member") {
            if (
                self::$data["chat_member"]["from"]["id"] !==
                self::$data["chat_member"]["new_chat_member"]["user"]["id"] &&
                self::$data["chat_member"]["new_chat_member"]["status"] === "member" &&
                self::$data["chat_member"]["old_chat_member"]["status"] !== "restricted"
            ) {
                return new InvitedUserUpdateModel();
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
        $type = self::$messageType;
        $hasVideoKey = array_key_exists("video", self::$data[$type]);
        $hasVoiceKey = array_key_exists("voice", self::$data[$type]);
        $hasVideoKey = array_key_exists("video", self::$data[$type]);
        $hasPhotoKey = array_key_exists("photo", self::$data[$type]);

        if ($hasVideoKey && $hasPhotoKey) {
            return new MultiMediaModel();
        }

        if ($hasPhotoKey && !$hasVideoKey) {
            return new PhotoMediaModel();
        }

        if ($hasVideoKey && !$hasPhotoKey) {
            return new VideoMediaModel();
        }

        if ($hasVoiceKey) {
            return new VoiceMediaModel();
        }

        if ($hasVideoKey && $hasPhotoKey) {
            new MultiMediaModel();
        }

        return $this;
    }

    protected function setFromId()
    {
        try {
            // log::info(json_encode(self::$data));
            $type = "";
            if (array_key_exists("from", self::$data[self::$messageType])) {
                $type = "from";
            }

            if (array_key_exists("user", self::$data[self::$messageType])) {
                $type = "user";
            }

            if (array_key_exists("actor_chat", self::$data[self::$messageType])) {
                $type = "actor_chat";
            }

            if (array_key_exists($type, self::$data[self::$messageType])) {
                self::$fromId = self::$data[self::$messageType][$type]["id"];
            }
        } catch (Exception $e) {
            $this->propertyErrorHandler($e->getMessage(), $e->getLine(), __METHOD__);
        }
        return $this;
    }

    protected function setFromAdmin()
    {
        try {
            if ((string) in_array(self::$fromId, self::$adminsIds)) {
                self::$fromAdmin = true;
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
        if (!array_key_exists("chat", self::$data[self::$messageType])) {
            $this->propertyErrorHandler("chatType свойство не установлено", __LINE__, __METHOD__);
        }

        self::$chatType = self::$data[self::$messageType]["chat"]["type"];
        return $this;
    }

    protected function setMessageType()
    {
        if (array_key_exists("message", self::$data)) {
            self::$messageType = "message";
        } elseif (array_key_exists("edited_message", self::$data)) {
            self::$messageType = "edited_message";
        } elseif (array_key_exists("my_chat_member", self::$data)) {
            self::$messageType = "my_chat_member";
        } elseif (array_key_exists("chat_member", self::$data)) {
            self::$messageType = "chat_member";
        } elseif (array_key_exists("message_reaction", self::$data)) {
            self::$messageType = "message_reaction";
        } elseif (array_key_exists("message_reaction_count", self::$data)) {
            self::$messageType = "message_reaction_count";
        } else {
            self::$messageType = "unknown_type";
            $this->propertyErrorHandler("messageType", __LINE__, __METHOD__);
        }

        return $this;
    }

    protected function setChatId(): static
    {
        try {
            self::$chatId = self::$data[self::$messageType]["chat"]["id"];
        } catch (Exception $e) {
            $this->propertyErrorHandler($e->getMessage(), $e->getLine(), __METHOD__);
        }
        return $this;
    }

    protected function setFromUserName()
    {
        try {
            $type = "";
            if (array_key_exists("from", self::$data[self::$messageType])) {
                $type = "from";
            }

            if (array_key_exists("user", self::$data[self::$messageType])) {
                $type = "user";
            }

            if (array_key_exists("actor_chat", self::$data[self::$messageType])) {
                $type = "actor_chat";
            }

            self::$fromUserName = self::$data[self::$messageType][$type][$type == "actor_chat" ? "title" : "first_name"];
        } catch (Exception $e) {
            $this->propertyErrorHandler($e->getMessage(), $e->getLine(), __METHOD__);
        }
        return $this;
    }

    /**
     * Summary of setAdminsIds
     * @param \Illuminate\Support\Facades\Http $http
     * @return TelegramRequestModelBuilder
     * @method  json($key = null, $default = null)
     */
    private function setAdmins(): static
    {
        if (self::$chatType === "private") {
            return $this;
        }

        $chat = Chat::where("chat_id", self::$chatId)->first();
        self::$adminsIds = $chat?->admins?->pluck("admin_id")->toArray() ?? [];

        if (!empty(self::$adminsIds)) {
            return $this;
        }

        $response = $this->http::post(
            env('TELEGRAM_API_URL') . env('TELEGRAM_API_TOKEN') . "/getChatAdministrators",
            ['chat_id' => self::$chatId]
        )->json();
        // BotErrorNotificationService::send("getChatAdministrators: " . json_encode($response));
        // dd($response);

        if (!$response['ok']) {
            throw new BaseTelegramBotException(CONSTANTS::GET_ADMINS_FAILED, __METHOD__);
        }

        self::$admins = array_map(function ($item) {
            $item["user"]["admin_id"] = $item["user"]["id"];
            unset($item["user"]["id"]);

            return $item['user'];
        }, $response['result']);

        self::$adminsIds = array_map(function ($item) {
            return $item['user']['id'];
        }, $response['result']);

        return $this;
    }

    public function setChatTitle()
    {
        if (self::$chatType === "private") {
            self::$chatTitle = "";
            return $this;
        }

        try {
            self::$chatTitle = self::$data[self::$messageType]["chat"]["title"];
            return $this;
        } catch (Exception $e) {
            $this->propertyErrorHandler($e->getMessage(), $e->getLine(), __METHOD__);
        }
        return $this;
    }

    public function getChatType(): string
    {
        return self::$chatType;
    }

    public function getFromId(): int
    {
        return self::$fromId;
    }

    public function getAdminsIds(): array
    {
        return self::$adminsIds;
    }

    public function getAdmins(): array
    {
        return self::$admins;
    }

    public function getMessageType(): string
    {
        return self::$messageType;
    }

    public function getFromUserName(): string
    {
        return self::$fromUserName;
    }

    public function getFromAdmin(): bool
    {
        return self::$fromAdmin;
    }

    public function getChatTitle(): string
    {
        return self::$chatTitle;
    }

    public function getChatId(): int
    {
        return self::$chatId;
    }

    public function getData(): array
    {
        return self::$data;
    }

    public function getJsonData(): string
    {
        return json_encode(self::$data);
    }

}
