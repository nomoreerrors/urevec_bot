<?php

namespace App\Models\MessageModels\MediaModels;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Exception;
use App\Models\MessageModels\MessageModel;


class BaseMediaModel extends MessageModel
{
    private string $caption = "";

    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->setCaption()
            ->setHasLink();
    }


    private function setCaption(): static
    {
        if (array_key_exists("caption", $this->data[$this->messageType])) {
            $this->caption = $this->data[$this->messageType]["caption"];
        }
        return $this;
    }


    public function getCaption()
    {
        return $this->caption;
    }


    protected function setHasLink()
    {
        $links = ["http", ".рф", ".ру", ".ком", ".com", ".ru"];
        if (!empty($this->caption)) {

            foreach ($links as $link)
                if (str_contains($this->caption, $link)) {
                    $this->hasLink = true;
                }
            if ($this->hasTextLink) {
                $this->hasLink = true;
            }
            return $this;
        }
    }
}
