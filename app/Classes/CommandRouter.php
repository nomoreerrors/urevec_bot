<?php

namespace App\Classes;
use App\Exceptions\ClassNotFoundException;
use App\Exceptions\EnumNotFoundException;
use App\Exceptions\InvalidCommandException;
use App\Exceptions\NamespaceException;

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
     * Dynamically retrieves the class name of a command based on a given command type.
     * 
     * Loops through all enum classes and checks if the command type exists. If a match is found,
     * returns the class name of the corresponding command class.
     * 
     * @return string|null The class name of the command if found, null otherwise.
     * @throws ClassNotFoundException If the class name is not found in the declared classes.
     * @throws InvalidCommandException If the command type is not found in any of the enum classes.
     * @throws NamespaceException If the class name does not start with the expected namespace.
     * @throws EnumNotFoundException If the enum class does not contain the expected enum value.
     */
    public function getCommandClassName(): ?string
    { {
            $this->namespace = self::ENUM_NAMESPACE;
            $this->path = app_path(self::COMMAND_ENUM_PATH);
            $enumClasses = scandir($this->path);

            foreach ($enumClasses as $enumClassFile) {
                if (substr($enumClassFile, -4) === '.php') {
                    $enumClass = $this->namespace . '\\' . substr($enumClassFile, 0, -4);

                    if ($this->enumHas($enumClass)) {
                        $this->enumClass = $enumClass;
                        $baseName = class_basename($enumClass);
                        $commandClass = str_replace('Enum', 'Command', $baseName);
                        $commandClassName = 'App\Classes\Commands\\' . $commandClass;

                        if (!$this->classExists($commandClassName)) {
                            throw new ClassNotFoundException("Class $commandClass not found.");
                        }

                        return $commandClassName;
                    }
                }
            }

            throw new EnumNotFoundException("Enum value " . $this->command . " not found in any enum class");
        }
    }

    protected function classExists(string $commandClassName): bool
    {
        $result = class_exists($commandClassName);
        return $result;
    }

    protected function enumHas(string $enumClass): bool
    {
        $result = $enumClass::exists($this->command);
        return $result;
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
