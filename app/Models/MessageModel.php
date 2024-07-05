<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Exception;

class MessageModel extends BaseTelegramRequestModel
{
    use HasFactory;



    protected bool $hasTextLink = false;

    protected int $fromId = 0;


    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->data = $data;

        $this->setHasTextLink()
            ->setEntities()
            ->setMessageId()
            ->setFromAdmin();
    }


    protected function setEntities()
    {
        if (array_key_exists("entities", $this->data[$this->messageType])) {
            $this->entities = $this->data[$this->messageType]["entities"];
            $this->setHasTextLink();

            return $this;
        } else
            $this->entities = [];
        return $this;
    }


    protected function setHasTextLink()
    {
        if (
            json_encode(str_contains(json_encode($this->entities), "text_link")) ||
            json_encode(str_contains(json_encode($this->entities), "url"))
        ) {
            $this->hasTextLink = true;
            return $this;
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


    public function getEntities(): array
    {
        return $this->entities;
    }


    public function hasTextLink()
    {
        $this->hasTextLink = true;
        return $this;
    }
}
