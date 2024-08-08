<?php

namespace App\Services;

use App\Classes\Buttons;
use App\Classes\BaseCommand;
use Illuminate\Container\Container;
use Illuminate\Http\Response;
use App\Models\Chat;
use App\Models\MessageModels\TextMessageModel;

abstract class BaseBotCommandCore
{
    protected $command;

    protected Buttons $buttons;

    protected $botService;

    protected $selectedChat = null;

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



}
