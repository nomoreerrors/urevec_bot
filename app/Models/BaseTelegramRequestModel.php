<?php

namespace App\Models;

use Error;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Symfony\Component\HttpFoundation\Response;
use Exception;
use Illuminate\Support\Facades\Log;

class BaseTelegramRequestModel extends Model
{
    use HasFactory;



    protected string $messageType = "";


    protected int $messageId = 0;


    protected bool $fromAdmin = false;


    protected array $data;


    protected string $fromUserName = "";

    /** Sender id */
    protected int $fromId = 0;



    public function __construct(array $data)
    {
        $this->data = $data;
        $this->setMessageType()
            ->setFromId()
            ->setFromAdmin()
            ->setMessageId()
            ->setFromUserName();
    }


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

        response(Response::$statusTexts[500], Response::HTTP_INTERNAL_SERVER_ERROR);
        throw new Exception("Неопознанный тип объекта. Невозможно создать экземпляр модели ");
    }



    public function getType(): string
    {
        return $this->messageType;
    }


    public function getMessageId(): int
    {
        return $this->messageId;
    }



    protected function setMessageId()
    {
        if (array_key_exists("message_id", $this->data[$this->messageType])) {

            $this->messageId = $this->data[$this->messageType]["message_id"];
        }

        return $this;
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

        if ($type === "" || $this->messageType === "") {

            response(Response::$statusTexts[500], Response::HTTP_INTERNAL_SERVER_ERROR);
            throw new Exception("Ключ from || user отсутствует. Неизвестный объект.");
        }

        $this->fromId = $this->data[$this->messageType][$type]["id"];


        return $this;
    }


    protected function setFromAdmin()
    {
        $adminsIdArray = explode(",", env("TELEGRAM_CHAT_ADMINS_ID"));
        if ((string) in_array($this->fromId, $adminsIdArray)) {
            $this->fromAdmin = true;
            return $this;
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
            response(Response::$statusTexts[500], Response::HTTP_INTERNAL_SERVER_ERROR);
            throw new Exception("Неопознанный тип сообщения");
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

        if ($type === "" || $this->messageType === "") {

            response(Response::$statusTexts[500], Response::HTTP_INTERNAL_SERVER_ERROR);
            throw new Exception("Ключ from || user отсутствует. Неизвестный объект.");
        }

        $this->fromUserName = $this->data[$this->messageType][$type]["first_name"];


        return $this;
    }


    public function getFromUserName(): string
    {
        return $this->fromUserName;
    }


    public function getFromAdmin(): bool
    {
        return $this->fromAdmin;
    }


    public function getFromId(): int
    {
        return $this->fromId;
    }
}
