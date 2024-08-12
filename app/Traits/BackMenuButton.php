<?php

namespace App\Traits;

use Illuminate\Support\Facades\Cache;

/**
 * Save previous selected menu as an array to cache and them 
 * get it from cache in order to return it in reverse order 
 */
trait BackMenuButton
{
    protected function rememberBackMenu()
    {
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

    private function saveBackMenuToCache(array $backMenuArray, string $cacheKey)
    {
        Cache::put($cacheKey, json_encode($backMenuArray));
    }

    protected function getBackMenuFromCache()
    {
        $cacheKey = $this->getBackMenuCacheKey();
        return json_decode(Cache::get($cacheKey), true);
    }

    private function getLastBackMenuFromCache()
    {
        $backMenuArray = $this->getBackMenuFromCache();
        $backTo = array_pop($backMenuArray);
        return $backTo;
    }



    /**
     * Decrease menu pointer by analogy with stack pointer
     * (remove last element from back menu array in cache)
     * @return void
     */
    protected function moveUpBackMenuPointer()
    {
        $backMenuArray = $this->getBackMenuFromCache();
        $cacheKey = $this->getBackMenuCacheKey();
        array_pop($backMenuArray);
        $this->saveBackMenuToCache($backMenuArray, $cacheKey);
    }

    protected function getBackMenuCacheKey(): string
    {
        return "back_menu_" . $this->botService->getAdmin()->admin_id;
    }

}
