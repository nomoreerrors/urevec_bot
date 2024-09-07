<?php

namespace App\Classes\Commands;

use App\Services\TelegramBotService;
use App\Classes\Buttons;

abstract class BaseCommand
{
    // use RestrictUsers;

    protected $enum;

    protected $model;

    protected string $command;

    /**
     * Command class name must be compatible with it's enum name and database model name 
     * Some examples: NewUserRestrictionsCommand, NewUserRestrictionsEnum, HasMany: $chat->newUserRestrictions 
     * @param \App\Services\TelegramBotService $botService
     */
    public function __construct(protected TelegramBotService $botService)
    {
        $this->botService = $botService;
        $this->command = $this->botService->getPrivateChatCommand();
        $this->setEnum();
        $this->handle();
    }

    /**
     *  Sends base menu settings of each command class
     * @return void
     */
    protected function handle(): void
    {
        switch ($this->command) {
            case $this->enum::SETTINGS->value:
                $this->send();
                break;
        }
    }

    /**
     * Sending main menu settings of each command class
     * @return void
     */
    public function send(): void
    {
        $this->botService->menu()->save();
        $keyBoard = $this->getSettingsButtons();
        $this->botService->sendMessage($this->enum::SETTINGS->replyMessage(), $keyBoard);
    }


    /**
     * Setting settings buttons of a child class based on it's name
     * For example: if class name is App\Commands\SuperFilterCommand  
     * it'll call App\Classes\Buttons::getSuperFilterButtons()
     * Additional buttons for each command can be added by adding titles in App\Classes\ButtonsTitles 
     * @throws \Exception
     * @return array
     */
    protected function getSettingsButtons(): array
    {
        $className = class_basename(get_class($this));
        $methodName = 'get' . str_replace('Command', '', $className) . 'Buttons';
        // BotErrorNotificationService::send("");
        // return response("ok");

        if (method_exists('App\Classes\Buttons', $methodName)) {
            return (new Buttons())->$methodName($this->model, $this->enum);
        }

        throw new \Exception('Method ' . $methodName . ' not found in App\Classes\Buttons');
    }

    /**
     * Setting enum property value of a child class based on it's name
     * For example: class name is App\Commands\SettingsCommand, enum name is App\Enums\SettingsEnum
     * @throws \Exception
     * @return void
     */
    protected function setEnum()
    {
        $className = class_basename(get_class($this));
        $enumName = str_replace('Command', 'Enum', $className);

        if (class_exists('App\Enums\CommandEnums\\' . $enumName)) {
            $this->enum = 'App\Enums\CommandEnums\\' . $enumName;
        } else {
            throw new \Exception('Enum ' . $enumName . ' not found in App\Enums');
        }
    }
}
