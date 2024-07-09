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
    public function create(): BaseTelegramRequestModel
    {
        if (array_key_exists("message", $this->data)) {
            if (
                array_key_exists("forward_from_chat", $this->data["message"]) ||
                array_key_exists("forward_origin", $this->data["message"])
            ) {
                return new ForwardMessageModel($this->data);
            }
            if (!array_key_exists("text", $this->data["message"])) {
                return new MessageModel($this->data);
            } else return new TextMessageModel($this->data);
        }



        if (array_key_exists("edited_message", $this->data)) {
            if (
                array_key_exists("forward_from_chat", $this->data["edited_message"]) ||
                array_key_exists("forward_origin", $this->data["edited_message"])
            ) {
                return new ForwardMessageModel($this->data);
            }
            if (!array_key_exists("text", $this->data["edited_message"])) {
                return new MessageModel($this->data);
            } else return new TextMessageModel($this->data);
        }


        if (array_key_exists("chat_member", $this->data)) {
            if (
                $this->data["chat_member"]["new_chat_member"]["status"] !==
                "member"
            ) {
                return new StatusUpdateModel($this->data);
            } elseif (
                $this->data["chat_member"]["from"]["id"] !==
                $this->data["chat_member"]["new_chat_member"]["user"]["id"]
            ) {
                return new InvitedUserUpdateModel($this->data);
            } else {

                return new NewMemberJoinUpdateModel($this->data);
            }
        }


        if (array_key_exists("message_reaction", $this->data)) {
            return new MessageReactionModel($this->data);
        }


        if (array_key_exists("message_reaction_count", $this->data)) {
            return new MessageReactionCountModel($this->data);
        }
        $this->propertyErrorHandler(CONSTANTS::UNKNOWN_OBJECT_TYPE);
    }



    public function getType(): string
    {
        if (empty($this->messageType)) {

            $this->propertyErrorHandler();
        }
        return $this->messageType;
    }

    /**
     * Summary of propertyErrorHandler
     * @param string $message
     * @throws \App\Exceptions\TelegramModelException
     * @return never
     */
    protected function propertyErrorHandler(string $message = "")
    {
        throw new TelegramModelException(
            $message !== "" ? $message : CONSTANTS::EMPTY_PROPERTY .
                "MESSAGE_TYPE PROPERTY: " . $this->messageType . PHP_EOL .
                "FROM_ADMIN PROPERTY: " . $this->fromAdmin . PHP_EOL .
                "FROM_ID PROPERTY: " . $this->fromId . PHP_EOL .
                "FROM_USER_NAME PROPERTY: " . $this->fromUserName . PHP_EOL .
                "CHAT_ID PROPERTY: " . $this->chatId . PHP_EOL,
            $this->getJsonData(),
            __METHOD__
        );
    }







    protected function setFromId()
    {
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


        try {
            $this->fromId = $this->data[$this->messageType][$type]["id"];
        } catch (Exception) {
            $this->propertyErrorHandler();
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
        } catch (Exception) {
            $this->propertyErrorHandler();
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
            $this->propertyErrorHandler();
        }


        return $this;
    }



    protected function setChatId(): static
    {
        try {

            $this->chatId = $this->data[$this->messageType]["chat"]["id"];
        } catch (Exception $e) {

            $this->propertyErrorHandler();
        }
        return $this;
    }



    protected function setFromUserName()
    {
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

        try {

            $this->fromUserName =
                $this->data[$this->messageType][$type][$type == "actor_chat" ? "title" : "first_name"];
        } catch (Exception) {

            $this->propertyErrorHandler();
        }

        return $this;
    }


    public function getFromId(): int
    {
        if (empty($this->fromId)) {

            $this->propertyErrorHandler();
        }
        return $this->fromId;
    }



    public function getFromUserName(): string
    {
        if (empty($this->fromUserName)) {
            $this->propertyErrorHandler();
        }
        return $this->fromUserName;
    }


    public function getFromAdmin(): bool
    {
        return $this->fromAdmin;
    }


    public function getChatId(): int
    {
        if (empty($this->chatId)) {
            $this->propertyErrorHandler();
        }
        return $this->chatId;
    }


    public function getData(): array
    {
        if (empty($this->data)) {
            $this->propertyErrorHandler();
        }
        return $this->data;
    }


    public function getJsonData(): string
    {

        return json_encode($this->data);
    }
}
