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

    // protected function rememberBackMenu()
    // {
    //     $cacheKey = $this->getBackMenuCacheKey();
    //     $backMenuArray = $this->getBackMenuFromCache();

    //     if (empty($backMenuArray)) {
    //         $backMenuArray = [$this->command];
    //         $this->saveBackMenuToCache($backMenuArray, $cacheKey);

    //     } elseif (in_array($this->command, $backMenuArray)) {
    //         $this->moveUpBackMenuPointer();
    //         return;

    //     } else {
    //         array_push($backMenuArray, $this->command);
    //         Cache::put(
    //             $cacheKey,
    //             json_encode($backMenuArray)
    //         );

    //     }
    // }

    // private function saveBackMenuToCache(array $backMenuArray, string $cacheKey)
    // {
    //     Cache::put($cacheKey, json_encode($backMenuArray));
    // }

    // protected function getBackMenuFromCache()
    // {
    //     $cacheKey = $this->getBackMenuCacheKey();
    //     return json_decode(Cache::get($cacheKey), true);
    // }

    // private function getLastBackMenuFromCache()
    // {
    //     $backMenuArray = $this->getBackMenuFromCache();
    //     $backTo = array_pop($backMenuArray);
    //     return $backTo;
    // }



    // /**
    //  * Decrease menu pointer by analogy with stack pointer
    //  * (remove last element from back menu array in cache)
    //  * @return void
    //  */
    // protected function moveUpBackMenuPointer()
    // {
    //     $backMenuArray = $this->getBackMenuFromCache();
    //     $cacheKey = $this->getBackMenuCacheKey();
    //     array_pop($backMenuArray);
    //     Cache::put(
    //         $cacheKey,
    //         json_encode($backMenuArray)
    //     );
    // }

    // protected function getBackMenuCacheKey(): string
    // {
    //     return "back_menu_" . $this->botService->getAdmin()->admin_id;
    // }

    abstract protected function handle(): static;

    abstract public function send(): void;

    // abstract protected function rememberBackMenu();


}