<?php

namespace App\Classes;

use App\Classes\PrivateChatCommandCore;
use App\Services\TelegramBotService;
use Illuminate\Support\Facades\Log;
use App\Services\CONSTANTS;
use App\Services\BotErrorNotificationService;
use Illuminate\Support\Facades\Cache;

class Menu
{
    private ?int $adminId = null;

    private string $command;

    /**
     * @var bool  Indicates that menu title should be refreshed after making changes in private chat
     */
    private bool $isMenuRefresh = false;


    public function __construct(private TelegramBotService $botService)
    {
        $this->command = $this->botService->getPrivateChatCommand();
        $this->adminId = $this->botService->getAdmin()->admin_id;
    }

    /**
     * Use to remember last menu in private chat. Use back() method to jump back to previous menu.
     * @return void
     */
    public function save(): void
    {
        if ($this->isMenuRefresh) {
            return;
        }

        $cacheKey = $this->getBackMenuCacheKey();
        $backMenuArray = $this->getBackMenuFromCache();

        if (empty($backMenuArray)) {
            $backMenuArray = [$this->command];
            $this->saveBackMenuToCache($backMenuArray, $cacheKey);

        } elseif (in_array($this->command, $backMenuArray)) {
            $this->moveUpBackMenuPointer();
            return;

        } else {
            array_push($backMenuArray, $this->command);
            $this->saveBackMenuToCache($backMenuArray, $cacheKey);
        }
    }

    /**
     * Return to previous menu that was saved in rememberBackMenu() method
     * @return void
     */
    public function back()
    {
        $lastBackMenuCommand = $this->getLastBackMenuFromCache();

        if (empty($lastBackMenuCommand)) {
            return;
        }
        $this->botService->setPrivateChatCommand($lastBackMenuCommand);
        (new PrivateChatCommandCore())->handle();
    }

    private function saveBackMenuToCache(array $backMenuArray, string $cacheKey)
    {
        Cache::put($cacheKey, json_encode($backMenuArray, JSON_UNESCAPED_UNICODE));
    }

    private function getBackMenuFromCache()
    {
        $cacheKey = $this->getBackMenuCacheKey();
        return json_decode(Cache::get($cacheKey), true);
    }

    private function getLastBackMenuFromCache()
    {
        $backMenuArray = $this->getBackMenuFromCache();

        if (!empty($backMenuArray)) {
            if (count($backMenuArray) === 1) {
                return $backMenuArray[0];
            }
            $index = count($backMenuArray) - 2;
            return $backMenuArray[$index];
        }
    }

    /**
     * Decrease menu pointer by analogy with stack pointer
     * (remove last element from back menu array in cache)
     * @return void
     */
    private function moveUpBackMenuPointer()
    {
        $backMenuArray = $this->getBackMenuFromCache();
        $cacheKey = $this->getBackMenuCacheKey();
        array_pop($backMenuArray);
        $this->saveBackMenuToCache($backMenuArray, $cacheKey);
    }

    private function getBackMenuCacheKey(): string
    {
        return "back_menu_" . $this->adminId;
    }

    /**
     * Refresh menu that was saved in rememberBackMenu() method after making changes in private chat
     * returning the same menu but with different titles according to an updated status in database
     * @return void
     */
    public function refresh()
    {
        $this->isMenuRefresh = true;

        $menu = $this->getBackMenuFromCache();

        if (empty($menu)) {
            log::error("Menu refresh failed");
            throw new \Exception(CONSTANTS::REFRESH_BACK_MENU_FAILED);
        }
        $this->botService->setPrivateChatCommand(end($menu));
        (new PrivateChatCommandCore())->handle();
    }
}

