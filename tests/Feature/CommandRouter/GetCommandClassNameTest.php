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

    public function testEnumCaseNotFoundReturnNull()
    {
        $commandRouter = new CommandRouter('invalid_command');
        $this->assertNull($commandRouter->getCommandClassName());
    }

    /**
     * Testcase where the command class does not exist
     * @return void
     */
    public function testGetCommandClassNameThrowsClassNotFoundExceptionWhenClassDoesNotExist()
    {
        $commandRouter = $this->getMockBuilder(CommandRouter::class)
            ->setConstructorArgs(['some command'])
            ->onlyMethods(['enumHas', 'commandClassExists'])
            ->getMock();
        $commandRouter->method('enumHas')->willReturn(true);
        $commandRouter->method('commandClassExists')->willReturn(false);

        $this->expectException(ClassNotFoundException::class);
        $commandRouter->getCommandClassName();
    }

    /**
     * Testcase where the enum class does not exist
     * @return void
     */
    public function testGetCommandClassNameReturnsNullWhenEnumClassDoesNotExist()
    {
        $commandRouter = $this->getMockBuilder(CommandRouter::class)
            ->setConstructorArgs(['some_command'])
            ->onlyMethods(['isValidEnumClass'])
            ->getMock();
        $commandRouter->method('isValidEnumClass')->willReturn(false);

        $this->assertNull($commandRouter->getCommandClassName());
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