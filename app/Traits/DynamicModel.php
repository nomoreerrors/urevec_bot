<?php

namespace App\Traits;

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
        $this->model = $this->botService->getChat()->{$modelName};
    }
}
