<?php

namespace App\Classes;

use App\Exceptions\ClassNotFoundException;
use App\Exceptions\EnumNotFoundException;
use App\Exceptions\InvalidCommandException;
use App\Exceptions\NamespaceException;
use App\Services\BotErrorNotificationService;

class CommandRouter
{
    private const ENUM_NAMESPACE = 'App\Enums\CommandEnums';
    private const COMMAND_ENUM_PATH = 'Enums/CommandEnums';
    private string $namespace;
    private string $path;
    private string $enumClass;

    public function __construct(private string $command)
    {
        $this->command = $command;
    }


    /**
     * Loops through all enum classes and checks if the command type exists. If a match is found,
     * returns the class name of the corresponding command class.
     * 
     * @return string|null The class name of the command if found, null otherwise.
     * @throws ClassNotFoundException If the class name is not found in the declared classes.
     */
    public function getCommandClassName(): ?string
    {
        $this->namespace = self::ENUM_NAMESPACE;
        $this->path = app_path(self::COMMAND_ENUM_PATH);
        $enumClasses = scandir($this->path);

        foreach ($enumClasses as $enumClassFile) {
            if (substr($enumClassFile, -4) === '.php') {
                $enumClass = $this->namespace . '\\' . substr($enumClassFile, 0, -4);

                if ($this->isValidEnumClass($enumClass)) {
                    if ($this->enumHas($enumClass)) {
                        $baseName = class_basename($enumClass);
                        $commandClass = str_replace('Enum', 'Command', $baseName);
                        $commandClassName = 'App\Classes\Commands\\' . $commandClass;

                        if (!$this->commandClassExists($commandClassName)) {
                            throw new ClassNotFoundException("Class $commandClass not found.");
                        }

                        return $commandClassName;
                    }
                }
            }
        }
        return null;
    }

    protected function commandClassExists(string $commandClassName): bool
    {
        $result = class_exists($commandClassName);
        return $result;
    }

    protected function enumHas(string $enumClass): bool
    {
        $result = $enumClass::exists($this->command);
        return $result;
    }

    protected function isValidEnumClass(string $enumClass): bool
    {
        return class_exists($enumClass) && strpos($enumClass, $this->namespace) === 0;
    }

    public function getEnumNamespace(): string
    {
        return self::ENUM_NAMESPACE;
    }

    public function getCommandEnumPath(): string
    {
        return self::COMMAND_ENUM_PATH;
    }
}
