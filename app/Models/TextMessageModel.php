<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Exception;
use Illuminate\Support\Facades\Log;

class TextMessageModel extends MessageModel
{
    use HasFactory;


    protected string $messageType = "message";

    protected bool $isTextMessage = true;





    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->setText()
            ->setHasLink();
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


    protected function setText()
    {
        $this->text = $this->data[$this->messageType]["text"];
        $this->setHasLink();
        return $this;
    }




    public function getFromAdmin(): bool
    {
        return $this->fromAdmin;
    }


    protected function setHasLink()
    {
        $links = ["http", ".рф", ".ру", ".ком", ".com", ".ru"];
        foreach ($links as $link)
            if (str_contains($this->text, $link)) {
                $this->hasLink = true;
            }
        if ($this->hasTextLink) {
            $this->hasLink = true;
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


    public function getFromUserName(): string
    {
        return  $this->fromUserName;
    }


    public function getMessageId(): int
    {
        return $this->messageId;
    }
}
