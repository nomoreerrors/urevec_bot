<?php

namespace App\Classes;

use App\Classes\PrivateChatCommandCore;
use App\Services\CONSTANTS;
use App\Services\BotErrorNotificationService;
use Illuminate\Support\Facades\Cache;

/**
 * Save previous selected menu in private chat with bot as an array to cache and then 
 * get it from cache in order to return it in reverse order 
 */
class Menu
{
    private static ?string $command = null;

    private static ?int $adminId = null;

    /**
     * @var bool  Indicates that menu title should be refreshed after making changes in private chat
     */
    private static bool $isMenuRefresh = false;

    /**
     * Use to remember last menu in private chat. Use back() method to jump back to previous menu.
     * @param string $command
     * @param int $adminId
     * @return void
     */
    public static function save(string $command)
    {
        if (self::$isMenuRefresh) {
            return;
        }

        self::$command = $command;
        self::$adminId = app("botService")->getAdmin()->admin_id;

        $cacheKey = self::getBackMenuCacheKey();
        $backMenuArray = self::getBackMenuFromCache();

        if (empty($backMenuArray)) {
            $backMenuArray = [self::$command];
            self::saveBackMenuToCache($backMenuArray, $cacheKey);

        } elseif (in_array(self::$command, $backMenuArray)) {
            self::moveUpBackMenuPointer();
            return;

        } else {
            array_push($backMenuArray, self::$command);
            self::saveBackMenuToCache($backMenuArray, $cacheKey);
        }
    }

    /**
     * Return to previous menu that was saved in rememberBackMenu() method
     * @return void
     */
    public static function back()
    {
        self::$adminId = app("botService")->getAdmin()->admin_id;
        $lastBackMenuCommand = self::getLastBackMenuFromCache();
        // BotErrorNotificationService::send($lastBackMenuCommand);
        if (empty($lastBackMenuCommand)) {
            return;
        }
        app("botService")->setPrivateChatCommand($lastBackMenuCommand);
        new PrivateChatCommandCore();
    }

    private static function saveBackMenuToCache(array $backMenuArray, string $cacheKey)
    {
        Cache::put($cacheKey, json_encode($backMenuArray, JSON_UNESCAPED_UNICODE));
    }

    private static function getBackMenuFromCache()
    {
        $cacheKey = self::getBackMenuCacheKey();
        return json_decode(Cache::get($cacheKey), true);
    }

    private static function getLastBackMenuFromCache()
    {
        $backMenuArray = self::getBackMenuFromCache();

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
    private static function moveUpBackMenuPointer()
    {
        // BotErrorNotificationService::send("moveUpBackMenuPointer");
        $backMenuArray = self::getBackMenuFromCache();
        $cacheKey = self::getBackMenuCacheKey();
        array_pop($backMenuArray);
        self::saveBackMenuToCache($backMenuArray, $cacheKey);
    }

    private static function getBackMenuCacheKey(): string
    {
        return "back_menu_" . self::$adminId;
    }

    /**
     * Refresh menu that was saved in rememberBackMenu() method after making changes in private chat
     * returning the same menu but with different titles according to an updated status in database
     * @return void
     */
    public static function refresh()
    {
        self::$adminId = app("botService")->getAdmin()->admin_id;
        self::$isMenuRefresh = true;

        $menu = self::getBackMenuFromCache();

        if (empty($menu)) {
            throw new \Exception(CONSTANTS::REFRESH_BACK_MENU_FAILED);
        }
        app("botService")->setPrivateChatCommand(end($menu));
        new PrivateChatCommandCore();
    }
}
