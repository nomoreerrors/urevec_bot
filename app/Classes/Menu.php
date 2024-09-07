<?php

namespace App\Classes;

use App\Classes\PrivateChatCommandCore;
use App\Exceptions\BaseTelegramBotException;
use App\Services\TelegramBotService;
use Illuminate\Support\Facades\Log;
use App\Services\CONSTANTS;
use App\Services\BotErrorNotificationService;
use Illuminate\Support\Facades\Cache;

class Menu
{
    private ?int $adminId = null;

    private string $command;

    private string $cacheKey;

    private static bool $backButtonPressed = false;

    /**
     * @var bool  Indicates that menu title should be refreshed after making changes in private chat
     */
    private static bool $isMenuRefresh = false;


    public function __construct(private TelegramBotService $botService)
    {
        $this->command = $this->botService->getPrivateChatCommand();
        $this->adminId = $this->botService->getAdmin()->admin_id;
        $this->cacheKey = "back_menu_" . $this->adminId;
    }

    /**
     * Use to remember last menu in private chat. Use back() method to jump back to previous menu.
     * @return void
     */
    public function save(): void
    {
        if (self::$isMenuRefresh) {
            self::$isMenuRefresh = false;
            return;
        }

        try {
            $backMenuArray = $this->getBackMenuFromCache();

            if (empty($backMenuArray)) {
                $backMenuArray = [$this->command];
                $this->saveBackMenuToCache($backMenuArray);
                return;
            }

            if (in_array("back", $backMenuArray)) {
                array_pop($backMenuArray);
                $this->saveBackMenuToCache($backMenuArray);
                return;
            }

            if (in_array($this->command, $backMenuArray)) {
                return;
            }

            array_push($backMenuArray, $this->command);
            $this->saveBackMenuToCache($backMenuArray);
        } catch (\Exception $e) {
            Log::error("Error saving menu: " . $e->getMessage());
            throw new BaseTelegramBotException("Error saving menu", $e->getMessage());
        }
    }

    /**
     * Return to previous menu that was saved in rememberBackMenu() method
     * @return void
     */
    public function back()
    {
        $backMenuArray = $this->getBackMenuFromCache();

        if (empty($backMenuArray)) {
            throw new \Exception('No previous menu to go back to');
        }

        $lastBackMenuCommand = $this->getLastBackMenu($backMenuArray);

        if (!$lastBackMenuCommand) {
            throw new \Exception('Failed to retrieve last back menu command');
        }

        array_pop($backMenuArray);
        array_push($backMenuArray, "back");

        if (!$this->saveBackMenuToCache($backMenuArray)) {
            throw new \Exception('Failed to save back menu to cache');
        }

        self::$backButtonPressed = true;

        // BotErrorNotificationService::send($lastBackMenuCommand . " pressed back button");
        $this->botService->setPrivateChatCommand($lastBackMenuCommand);

        $this->botService->commandHandler()->handle();
    }


    protected function saveBackMenuToCache(array $backMenuArray): bool
    {
        $result = Cache::put($this->cacheKey, json_encode($backMenuArray, JSON_UNESCAPED_UNICODE));
        return $result;
    }

    protected function getBackMenuFromCache(): ?array
    {
        $result = json_decode(Cache::get($this->cacheKey), true);
        return $result;
    }

    protected function getLastBackMenu(array $backMenuArray): string
    {
        if (count($backMenuArray) == 1) {
            return $backMenuArray[0];
        }

        $index = count($backMenuArray) - 2;
        return $backMenuArray[$index];
    }


    /**
     * Update private chat menu button title with the new status
     * simply rerunning the open menu command and load buttons with new titles
     * after toggle filter for example. Titles should be updated in certain command classes.
     *
     * @return void
     */
    public function refresh()
    {
        if (self::$isMenuRefresh) {
            self::$isMenuRefresh = false;
            return;
        }

        self::$isMenuRefresh = true;
        $menu = $this->getBackMenuFromCache();

        if (empty($menu)) {
            log::error("Menu refresh failed");
            throw new \Exception(CONSTANTS::REFRESH_BACK_MENU_FAILED);
        }
        // dd($menu);

        $this->botService->setPrivateChatCommand(end($menu));

        $this->botService->commandHandler()->handle();
    }


    public function getIsMenuRefresh()
    {
        return self::$isMenuRefresh;
    }

    public function setIsMenuRefresh(bool $isMenuRefresh)
    {
        self::$isMenuRefresh = $isMenuRefresh;
    }

    public function backButtonPressed(): bool
    {
        return self::$backButtonPressed;
    }
}


