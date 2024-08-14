<?php

namespace App\Classes;

use App\Classes\Buttons;
use App\Models\Admin;
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

    protected ?Admin $admin;

    protected $selectedChat = null;

    protected TextMessageModel $requestModel;

    public function __construct()
    {
        $this->requestModel = app("requestModel");
        $this->botService = app("botService");
        $this->admin = $this->botService->getAdmin();
        $this->buttons = new Buttons();
    }

    abstract protected function checkUserAccess(): static;

    abstract protected function handle(): void;



}
