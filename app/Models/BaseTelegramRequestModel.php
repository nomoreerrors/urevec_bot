<?php

namespace App\Models;

use App\Exceptions\TelegramModelException;
use App\Services\BotErrorNotificationService;
use DeepCopy\Exception\PropertyException;
use Error;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Services\CONSTANTS;
use PHPUnit\Event\Test\NoticeTriggeredSubscriber;
use Symfony\Component\HttpFoundation\Response;
use Exception;
use GuzzleHttp\Psr7\Message;
use Illuminate\Support\Facades\Log;

class BaseTelegramRequestModel extends Model
{
    use HasFactory;



    protected string $messageType = "";

    protected bool $fromAdmin = false;

    protected array $data;

    protected int $chatId = 0;

    protected string $fromUserName = "";
    /** Sender id */
    protected int $fromId = 0;



    public function __construct(array $data)
    {
        $this->data = $data;
            $this->setMessageType()
                ->setFromId()
                ->setFromAdmin()
                ->setChatId()
                ->setFromUserName();
    }

    /**
     * Summary of create
     * @throws TelegramModelException
     * @return \App\Models\BaseTelegramRequestModel
     */
    public function create()
    {
        try {
            
            $model = $this->createMessageModel()
                          ->createTextMessageModel()
                          ->createForwardMessageModel()
                          ->createStatusUpdateModel()
                          ->createNewMemberJoinUpdateModel()
                          ->createNewMemberJoinUpdateModel()
                          ->createMessageReactionUpdateModel()
                          ->createInvitedUserUpdateModel();

            return $model;

        } catch (Exception $e) {
            $this->propertyErrorHandler($e->getMessage(), $e->getLine(), __METHOD__);
        }
    }


    /**
     * Summary of propertyErrorHandler
     * @param string $message
     * @throws \App\Exceptions\TelegramModelException
     * @return never
     */
    protected function propertyErrorHandler(string $message = "", $line, $method)
    {
        $text =  CONSTANTS::EMPTY_PROPERTY . "DEFAULT EXCEPTION REASON: " . $message  . " LINE: " . $line . PHP_EOL . $method . PHP_EOL .
            "MESSAGE_TYPE PROPERTY: " . $this->messageType . PHP_EOL .
            "FROM_ADMIN PROPERTY: " . $this->fromAdmin . PHP_EOL .
            "FROM_ID PROPERTY: " . $this->fromId . PHP_EOL .
            "FROM_USER_NAME PROPERTY: " . $this->fromUserName . PHP_EOL .
            "CHAT_ID PROPERTY: " . $this->chatId . PHP_EOL;
        // dd($text);

        throw new TelegramModelException($text,__METHOD__);
    }



    private function createMessageReactionUpdateModel()
    {
        if ($this->messageType === "message_reaction") {
            return new MessageReactionModel($this->data);
        }
        return $this;
    }


    private function createForwardMessageModel()
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
            (array_key_exists("forward_from_chat", $this->data[$type]) ||
                array_key_exists("forward_origin", $this->data[$type]))
        ) {
            return new ForwardMessageModel($this->data);
        }
        return $this;
    }


    private function createTextMessageModel()
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
            array_key_exists("text", $this->data[$type]) &&
            !array_key_exists("forward_from_chat", $this->data[$type]) &&
            !array_key_exists("forward_origin", $this->data[$type])
        ) {

            return new TextMessageModel($this->data);
        }
        return $this;
    }

    private function createMessageModel()
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
            !array_key_exists("text", $this->data[$type]) &&
            !array_key_exists("forward_from_chat", $this->data[$type]) &&
            !array_key_exists("forward_origin", $this->data[$type])
        ) {

            return new MessageModel($this->data);
        }
        return $this;
    }



    public function createStatusUpdateModel()
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


    public function createNewMemberJoinUpdateModel()
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


    public function createInvitedUserUpdateModel()
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

        if(array_key_exists($type, $this->data[$this->messageType])) {
            $this->fromId = $this->data[$this->messageType][$type]["id"];
        }
        } catch (Exception $e) {
            $this->propertyErrorHandler($e->getMessage(), $e->getLine(), __METHOD__);
            
        }

        return $this;
    }


    protected function setFromAdmin()
    {
        $adminsIdArray = explode(",", env("TELEGRAM_CHAT_ADMINS_ID"));

        try {

            if ((string) in_array($this->fromId, $adminsIdArray)) {
                $this->fromAdmin = true;
                return $this;
            }
        } catch (Exception $e) {
            $this->propertyErrorHandler($e->getMessage(), $e->getLine(), __METHOD__);
        }

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


    public function getFromId(): int
    {
        return $this->fromId;
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
