<?php

namespace App\Services;

use App\Classes\Buttons;
use App\Classes\ReplyInterface;
use Illuminate\Container\Container;
use Illuminate\Http\Response;
use App\Models\Chat;
use App\Models\MessageModels\TextMessageModel;

abstract class BotCommandService
{
    protected $command;

    protected Buttons $buttons;

    protected $botService;

    protected $selectedChat = null;

    protected ReplyInterface $settings;

    protected TextMessageModel $requestModel;

    public function __construct()
    {
        $this->requestModel = app("requestModel");
        $this->botService = app("botService");
        $this->command = $this->requestModel->getText();
        $this->buttons = new Buttons();
    }

    abstract protected function checkUserAccess(): static;

    abstract protected function handle(): static;


    public function getSelectedChat(): ?Chat
    {
        return $this->selectedChat;
    }

}
