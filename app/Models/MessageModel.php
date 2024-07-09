<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Exception;

class MessageModel extends BaseTelegramRequestModel
{
    use HasFactory;



    protected bool $hasTextLink = false;

    protected int $messageId = 0;

    protected bool $hasEntities = false;

    protected int $fromId = 0;

    protected bool $hasLink = false;


    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->setHasEntities()
            ->setMessageId()
            ->setHasTextLink();
    }


    protected function setHasEntities(): static
    {

        if (array_key_exists("entities", $this->data[$this->messageType])) {
            // dd("here");
            $this->hasEntities = true;
            $this->setHasTextLink();
            return $this;
        } else
            $this->hasEntities = false;
        return $this;
    }


    protected function setMessageId()
    {

        if (array_key_exists("message_id", $this->data[$this->messageType])) {

            $this->messageId = $this->data[$this->messageType]["message_id"];
        }

        if (empty($this->messageId)) {
            dd($this->data);
        }

        return $this;
    }




    public function getMessageId(): int
    {
        if (empty($this->messageId)) {
            dd($this->data);
            $this->errorLog(__METHOD__);
        }
        return $this->messageId;
    }


    protected function setHasTextLink(): static
    {
        if ($this->hasEntities) {

            $entitiesToString = json_encode($this->data[$this->messageType]["entities"]);

            // dd($entitiesToString);

            if (str_contains($entitiesToString, "text_link") || str_contains($entitiesToString, "url")) {
                $this->hasTextLink = true;

                return $this;
            }
        }
        return $this;
    }


    public function getText(): string
    {
        return $this->text;
    }


    public function getHasLink(): bool
    {
        return $this->hasLink;
    }


    public function getHasEntities(): bool
    {
        return $this->hasEntities;
    }


    public function hasTextLink()
    {
        return $this->hasTextLink;
    }
}
