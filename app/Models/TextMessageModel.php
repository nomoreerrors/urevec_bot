<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Exception;
use Illuminate\Support\Facades\Log;

class TextMessageModel extends MessageModel
{
    use HasFactory;


    protected string $text = "";


    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->setText()
            ->setHasLink();
    }


    protected function setText()
    {
        $this->text = $this->data[$this->messageType]["text"];
        $this->setHasLink();
        return $this;
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
        if (empty($this->text)) {
            $this->errorLog(__METHOD__);
        }
        return $this->text;
    }


    public function getHasLink(): bool
    {
        return $this->hasLink;
    }
}
