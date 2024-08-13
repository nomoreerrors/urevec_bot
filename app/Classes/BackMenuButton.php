<?php

namespace App\Classes;

use App\Classes\PrivateChatCommandCore;
use App\Services\BotErrorNotificationService;
use Illuminate\Support\Facades\Cache;

/**
 * Save previous selected menu in private chat with bot as an array to cache and them 
 * get it from cache in order to return it in reverse order 
 */
class BackMenuButton
{
    private static ?string $command = null;
    private static ?int $adminId = null;

    /**
     * Use to remember last menu in private chat. Use back() method to jump back to previous menu.
     * @param string $command
     * @param int $adminId
     * @return void
     */
    public static function rememberBackMenu(string $command)
    {
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
        // BotErrorNotificationService::send($cacheKey);
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
        $backMenuArray = self::getBackMenuFromCache();
        $cacheKey = self::getBackMenuCacheKey();
        array_pop($backMenuArray);
        self::saveBackMenuToCache($backMenuArray, $cacheKey);
    }

    private static function getBackMenuCacheKey(): string
    {
        return "back_menu_" . self::$adminId;
    }
}
