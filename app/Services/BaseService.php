<?php

namespace App\Services;

use GuzzleHttp\Psr7\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Support\Facades\Storage;

class BaseService
{
    public string $messageType;

    public string $text;

    protected array $data;

    public array $entities = [];


    public bool $textLink = false;




    // public function setData(array $data): void
    // {
    //     $this->data = $data;

    //     if (array_key_exists("message", $data)) {
    //         $this->messageType = "message";
    //         $this->setText();
    //     }

    //     if (array_key_exists("edited_message", $data)) {
    //         $this->messageType = "edited_message";
    //         $this->setText();
    //     }

    //     if (array_key_exists("chat_member", $data)) {
    //         $this->messageType = "chat_member";
    //     }

    //     if (array_key_exists("my_chat_member", $data)) {
    //         $this->messageType = "my_chat_member";
    //     }

    //     $this->setEntities();
    // }

    // private function setEntities(): void
    // {
    //     if (array_key_exists("entities", $this->data[$this->messageType])) {
    //         $this->entities = $this->data[$this->messageType]["entities"];
    //         $this->setTextLink();
    //     } else
    //         $this->entities = [];
    // }


    // private function setText(): void
    // {
    //     if (array_key_exists("text", $this->data[$this->messageType])) {
    //         $this->text = $this->data[$this->messageType]["text"];
    //     }
    // }


    // private function setTextLink(): void
    // {
    //     $result = str_contains(json_encode($this->entities), "text_link");
    //     if ($result) {
    //         $this->textLink = true;
    //     } else {
    //         $this->textLink = false;
    //     }
    // }
}
