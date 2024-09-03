<?php

namespace Feature\CommandRouter;

use App\Classes\BadWordsFilterCommand;
use Tests\TestCase;
use App\Exceptions\ClassNotFoundException;
use App\Classes\CommandRouter;
use App\Exceptions\EnumNotFoundException;
use App\Exceptions\InvalidCommandException;
use App\Exceptions\NamespaceException;
use App\Enums\CommandEnums\BadWordsFilterEnum;


class GetCommandClassNameTest extends TestCase
{
    public function testGetCommandClassNameValidCommand()
    {
        $commandRouter = new CommandRouter(BadWordsFilterEnum::ADD_WORDS->value);
        $commandClassName = $commandRouter->getCommandClassName();
        $this->assertNotNull($commandClassName);
        $this->assertEquals('App\Classes\Commands\BadWordsFilterCommand', $commandClassName);
    }

    public function testEnumNotFoundException()
    {
        $commandRouter = new CommandRouter('invalid_command');
        $this->expectException(EnumNotFoundException::class);
        $commandRouter->getCommandClassName();
    }

    public function testGetCommandClassNameThrowsClassNotFoundExceptionWhenClassDoesNotExist()
    {
        $commandRouter = $this->getMockBuilder(CommandRouter::class)
            ->setConstructorArgs(['some command'])
            ->onlyMethods(['enumHas', 'classExists'])
            ->getMock();
        $commandRouter->method('enumHas')->willReturn(true);
        $commandRouter->method('classExists')->willReturn(false);

        $this->expectException(ClassNotFoundException::class);
        $commandRouter->getCommandClassName();
    }

    public function testEnumNamespace(): void
    {
        $commandRouter = new CommandRouter('some_command');
        $this->assertEquals('App\Enums\CommandEnums', $commandRouter->getEnumNamespace());
    }

    public function testCommandEnumPath(): void
    {
        $commandRouter = new CommandRouter('some_command');
        $this->assertEquals('Enums/CommandEnums', $commandRouter->getCommandEnumPath());
    }
}