<?php

namespace App\Models\MessageModels;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Exception;
use Illuminate\Support\Facades\Log;

class TextMessageModel extends MessageModel
{
    use HasFactory;


    protected string $text = "";

    protected bool $isCommand = false;

    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->setText()
            ->setHasLink()
            ->setIsCommand();
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
            if (str_contains(mb_strtolower($this->text), $link)) {
                $this->hasLink = true;
            }
        if ($this->hasTextLink) {
            $this->hasLink = true;
        }
        return $this;
    }

    protected function setIsCommand(): static
    {
        if (str_starts_with($this->text, "/")) {
            $this->isCommand = true;
        }
        return $this;
    }

    public function getIsCommand(): bool
    {
        return $this->isCommand;
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
