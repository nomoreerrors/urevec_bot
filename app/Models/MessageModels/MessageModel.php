<?php

namespace App\Models\MessageModels;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\TelegramRequestModelBuilder;
use Exception;

class MessageModel extends TelegramRequestModelBuilder
{
    use HasFactory;



    protected bool $hasTextLink = false;

    protected bool $isForward = false;

    protected int $messageId = 0;

    protected bool $hasEntities = false;

    protected bool $hasLink = false;


    public function __construct()
    {
        $this->setHasEntities()
            ->setMessageId()
            ->setIsForward()
            ->setHasTextLink();
    }

    protected function setHasEntities(): static
    {
        if (
            array_key_exists("entities", self::$data[self::$messageType]) ||
            array_key_exists("caption_entities", self::$data[self::$messageType])
        ) {
            // dd("here");
            $this->hasEntities = true;
            $this->setHasTextLink();
            return $this;
        } else
            $this->hasEntities = false;
        return $this;
    }

    private function setIsForward(): static
    {
        if (array_key_exists("forward_origin", self::$data[self::$messageType])) {
            $this->isForward = true;
        }
        return $this;
    }

    protected function setMessageId()
    {

        if (array_key_exists("message_id", self::$data[self::$messageType])) {

            $this->messageId = self::$data[self::$messageType]["message_id"];
        }

        if (empty($this->messageId)) {
            dd(self::$data);
        }

        return $this;
    }

    public function getMessageId(): int
    {
        return $this->messageId;
    }

    public function getIsForward(): bool
    {
        return $this->isForward;
    }

    protected function setHasTextLink(): static
    {
        if ($this->hasEntities) {

            if (array_key_exists("entities", self::$data[self::$messageType])) {
                $entitiesToString = json_encode(self::$data[self::$messageType]["entities"]);
            }

            if (array_key_exists("caption_entities", self::$data[self::$messageType])) {
                $entitiesToString = json_encode(self::$data[$this->messageType]["caption_entities"]);
            }


            if (str_contains($entitiesToString, "text_link") || str_contains($entitiesToString, "url")) {
                $this->hasTextLink = true;

                return $this;
            }
        }
        if (self::$data["update_id"] === 117305689) {
            dd(self::$data);
        }
        return $this;
    }


    public function getHasLink(): bool
    {
        return $this->hasLink;
    }


    public function getHasEntities(): bool
    {
        return $this->hasEntities;
    }


    public function getHasTextLink()
    {
        return $this->hasTextLink;
    }
}
