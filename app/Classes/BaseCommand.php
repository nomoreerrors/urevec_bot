<?php

namespace App\Classes;

use App\Models\Chat;
use App\Services\BotErrorNotificationService;
use App\Services\TelegramBotService;
use App\Enums\MainMenuCmd;
use Illuminate\Support\Facades\Cache;

abstract class BaseCommand
{
    protected TelegramBotService $botService;
    protected Chat $chat;

    public function __construct(private string $command)
    {
        $this->botService = app("botService");
        $this->chat = $this->botService->getChat();
        $this->handle();        //
    }

    protected function rememberBackMenu()
    {
        $cacheKey = "back_menu_" . $this->botService->getAdmin()->admin_id;
        $lol = Cache::has($cacheKey);
        $backMenuArray = json_decode(Cache::get($cacheKey), true);

        if (empty($backMenuArray)) {
            $backMenuArray = [$this->command];
            Cache::put(
                "back_menu_" . $this->botService->getAdmin()->admin_id,
                json_encode($backMenuArray)
            );
        } else {
            $backMenuArray;
            array_push($backMenuArray, $this->command);
            Cache::put(
                "back_menu_" . $this->botService->getAdmin()->admin_id,
                json_encode($backMenuArray)
            );
        }
    }

    protected function getBackMenuFromCache()
    {
        $cacheKey = "back_menu_" . $this->botService->getAdmin()->admin_id;
        $backMenuArray = json_decode(Cache::get($cacheKey), true);

        $backTo = array_pop($backMenuArray);
        return $backTo;
    }

    /**
     * Decrease menu pointer by analogy with stack pointer
     * (remove last element from back menu array in cache)
     * @return void
     */
    protected function moveBackMenuPointer()
    {
        $cacheKey = "back_menu_" . $this->botService->getAdmin()->admin_id;
        $backMenuArray = json_decode(Cache::get($cacheKey), true);

        array_pop($backMenuArray);
        Cache::put(
            $cacheKey,
            json_encode($backMenuArray)
        );
    }

    abstract protected function handle(): static;

    abstract public function send(): void;

    // abstract protected function rememberBackMenu();


}