<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Exception;

class MessageModel extends BaseTelegramRequestModel
{
    use HasFactory;



    protected bool $hasTextLink = false;

    protected bool $hasEntities = false;

    protected int $fromId = 0;

    protected bool $hasLink = false;


    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->data = $data;
        $this->setHasEntities()
            ->setHasTextLink();
    }


    protected function setHasEntities()
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


    protected function setHasTextLink()
    {
        if ($this->hasEntities) {

            $entitiesToString = json_encode($this->data[$this->messageType]["entities"]);

            // dd($entitiesToString);

            if (str_contains($entitiesToString, "text_link") || str_contains($entitiesToString, "url")) {
                $this->hasTextLink = true;

                return $this;
            }
            return $this;
        }
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
