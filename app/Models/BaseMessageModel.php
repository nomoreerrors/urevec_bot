<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Exception;
use Illuminate\Support\Facades\Log;

class BaseMessageModel extends Model
{
    use HasFactory;


    private string $messageType;

    /** Репост из другого чата @var bool */
    private bool $isForwardMessage = false;

    private string $text = "";

    private array $data;

    private array $entities = [];

    private bool $hasLink = false;

    /** Отдельное поле сущности: text_link  @var bool */
    private bool $hasTextLink = false;


    private bool $fromAdmin = false;

    private bool $isNewMemberJoinUpdate = false;

    private int $fromId = 0;


    private string $fromUserName;


    private int $messageId = 0;


    private array $invitedUsersId = [];


    public function __construct(array $data)
    {
        $this->data = $data;
        $this->setData($data);
    }


    public function setData(array $data): void
    {
        $this->data = $data;

        if (array_key_exists("message", $data)) {
            $this->messageType = "message";
            $this->setText();
            $this->setMessageId();
            $this->setUserData();
        }

        if (array_key_exists("edited_message", $data)) {
            $this->messageType = "edited_message";
            $this->setText();
            $this->setMessageId();
            $this->setUserData();
        }

        if (array_key_exists("chat_member", $data)) {
            $this->messageType = "chat_member";
            $this->setIsNewMemberJoinUpdate();
        }

        if (array_key_exists("my_chat_member", $data)) {
            $this->messageType = "my_chat_member";
        }

        $this->setEntities();
        $this->setUserData();
        $this->checkIfUserIsAdmin();
        $this->setIsForwardMessage();
    }

    private function setEntities(): void
    {
        if (array_key_exists("entities", $this->data[$this->messageType])) {
            $this->entities = $this->data[$this->messageType]["entities"];
            $this->setHasTextLink();
        } else
            $this->entities = [];
    }


    private function setText(): void
    {
        if (array_key_exists("text", $this->data[$this->messageType])) {
            $this->text = $this->data[$this->messageType]["text"];
            $this->setHasLink();
        }
    }


    private function setHasLink(): void
    {
        $links = ["http", ".рф", ".ру", ".ком", ".com", ".ru"];
        foreach ($links as $link)
            if (str_contains($this->text, $link)) {
                $this->hasLink = true;
            }
        if ($this->hasTextLink) {
            $this->hasLink = true;
        }
    }



    private function setHasTextLink(): void
    {
        if (
            json_encode(str_contains(json_encode($this->entities), "text_link")) ||
            json_encode(str_contains(json_encode($this->entities), "url"))
        ) {
            $this->hasTextLink = true;
        }
    }


    private function setUserData(): void
    {
        $this->fromId = $this->data[$this->messageType]["from"]["id"];
        // dd($this->fromId = $this->data[$this->messageType]["from"]["id"]);
        $this->fromUserName = $this->data[$this->messageType]["from"]["first_name"];
    }



    /**
     * Summary of checkIfUserIsAdmin
     * @return bool
     */
    private function checkIfUserIsAdmin(): void
    {
        $adminsIdArray = explode(",", env("TELEGRAM_CHAT_ADMINS_ID"));
        // dd(env("TELEGRAM_CHAT_ADMINS_ID"));
        // dd(env("CRON_TOKEN"));
        // dd($adminsIdArray);
        if ((string)in_array($this->fromId, $adminsIdArray)) {
            $this->fromAdmin = true;
        }
    }


    /**
     * Репост из другой группы или нет
     * @return bool
     */
    private function setIsForwardMessage(): void
    {
        if ($this->messageType === "message" || $this->messageType === "edited_message") {
            if (
                array_key_exists("forward_from_chat", $this->data[$this->messageType]) &&
                array_key_exists("forward_origin", $this->data[$this->messageType])
            ) {
                $this->isForwardMessage = true;
            }
        }
    }



    private function setIsNewMemberJoinUpdate(): void
    {
        if (empty($this->messageType)) {
            throw new Exception("Тип сообщения — пустая строка. Тип не задан в TelegramBotService.");
        }

        if ($this->messageType !== "chat_member") {
            $this->isNewMemberJoinUpdate = false;
        }

        if (!array_key_exists("new_chat_member", $this->data[$this->messageType])) {
            log::info("new_chat_member value не существует (blocknewvisitor");
            $this->isNewMemberJoinUpdate = false;
        }


        if ($this->data[$this->messageType]["new_chat_member"]["status"] !== "member") {
            //Не является новым подписчиком
            log::info("new_chat_member status !== member", $this->data);
            $this->isNewMemberJoinUpdate = false;
        }
        if ($this->data[$this->messageType]["new_chat_member"]["user"]["id"] !== $this->fromId) {
            $this->invitedUsersId[] = $this->data[$this->messageType]["new_chat_member"]["user"]["id"];
            $this->isNewMemberJoinUpdate = true;
        } else {
            $this->isNewMemberJoinUpdate = true;
        }
    }







    public function getType(): string
    {
        return $this->messageType;
    }


    public function getFromAdmin(): bool
    {
        return $this->fromAdmin;
    }

    public function getFromId(): int
    {
        return $this->fromId;
    }


    public function getText(): string
    {
        return $this->text;
    }


    public function getHasLink(): bool
    {
        return $this->hasLink;
    }


    public function getIsNewMemberJoinUpdate(): bool
    {
        return $this->isNewMemberJoinUpdate;
    }


    public function getIsForwardMessage(): bool
    {
        return $this->isForwardMessage;
    }


    public function getEntities(): array
    {
        return $this->entities;
    }

    public function getInvitedUsersId(): array
    {
        return $this->invitedUsersId;
    }

    public function getMessageType(): string
    {
        return $this->messageType;
    }

    public function setMessageId(): void
    {
        // dd($this->messageType);
        if (
            $this->messageType !== "message" &&
            $this->messageType !== "edited_message"
        ) {
            throw new Exception("Попытка установить message_id неверному типу сообщения");
        }

        $this->messageId = $this->data[$this->messageType]["message_id"];
    }


    public function getFromUserName(): string
    {
        return  $this->fromUserName;
    }


    public function getMessageId(): int
    {
        return $this->messageId;
    }
}
