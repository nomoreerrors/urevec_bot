<?php

namespace App\Classes;

use App\Classes\Buttons;
use App\Models\Admin;
use App\Classes\BaseCommand;
use Illuminate\Container\Container;
use Illuminate\Http\Response;
use App\Models\Chat;
use App\Models\MessageModels\TextMessageModel;
use App\Services\TelegramBotService;

abstract class BaseBotCommandCore
{
    protected $command;

    protected Buttons $buttons;

    protected ?Admin $admin;

    protected $selectedChat = null;

    protected $requestModel;

    public function __construct(protected TelegramBotService $botService)
    {
        $this->checkUserAccess();
    }

    abstract protected function checkUserAccess(): static;

    abstract protected function handle(): void;



}
