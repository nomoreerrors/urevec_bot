<?php

namespace App\Traits;
use App\Services\BotErrorNotificationService;

trait DynamicModel
{
    /**
     * Setting model of a class based on it's name
     * For example: if class name is App\Commands\SuperCommand
     *  it'll be $this->botService->getChat()->super
     * @throws \Exception
     * @return void
     */
    protected function setModelFromClassName(): void
    {
        $className = class_basename(get_class($this));
        $modelName = str_replace('Command', '', $className);
        $modelName = lcfirst($modelName);
        // $j = $this->botService->getChat()->{$modelName};
        if (empty($this->botService->getChat()->{$modelName})) {
            throw new \Exception("Property '$modelName' does not exist in getChat()");
        }
        $this->model = $this->botService->getChat()->{$modelName};
    }
}
