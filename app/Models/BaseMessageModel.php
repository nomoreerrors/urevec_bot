<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class BaseMessageModel extends Model
{
    use HasFactory;


    public string $messageType;

    /** Репост из другого чата @var bool */
    public bool $isForwardMessage;

    public string $text;

    private array $data;

    public array $entities;

    public bool $hasLink = false;

    public bool $hasTextLink = false;

    public bool $userIsAdmin = false;


    public int $userId = 0;


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
        }

        if (array_key_exists("edited_message", $data)) {
            $this->messageType = "edited_message";
            $this->setText();
        }

        if (array_key_exists("chat_member", $data)) {
            $this->messageType = "chat_member";
        }

        if (array_key_exists("my_chat_member", $data)) {
            $this->messageType = "my_chat_member";
        }

        $this->setEntities();
        $this->setUserId();
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


    private function setUserId(): void
    {
        $this->userId = $this->data[$this->messageType]["from"]["id"];
    }



    /**
     * Summary of checkIfUserIsAdmin
     * @return bool
     */
    private function checkIfUserIsAdmin(): void
    {
        $adminsIdArray = explode(",", env("TELEGRAM_CHAT_ADMINS_ID"));

        if ((string) in_array($this->userId, $adminsIdArray)) {
            $this->userIsAdmin = true;
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
}
