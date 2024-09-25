<?php

namespace Feature\PrivateChatCommandRegister;
use Tests\TestCase;
use App\Exceptions\SetCommandsFailedException;
use App\Classes\PrivateChatCommandRegister;

class CheckIfCommandsAreSetTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    public function testCheckIfCommandsAreSetWithMatchingCommands(): void
    {
        $commands = [
            [
                "command" => "test_command",
                "description" => "Test command"
            ]
        ];

        $updatedCommands = [
            [
                "command" => "test_command",
                "description" => "Test command"
            ]
        ];

        $privateChatCommandRegister = $this->getMockBuilder(PrivateChatCommandRegister::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $this->assertEmpty($privateChatCommandRegister->checkifCommandsAreSet($commands, $updatedCommands));
    }

    public function testCheckIfCommandsAreSetWithNonMatchingCommands(): void
    {
        $commands = [
            [
                "command" => "test_command",
                "description" => "Test command"
            ]
        ];

        $updatedCommands = [
            [
                "command" => "different_command",
                "description" => "Different command"
            ]
        ];

        $privateChatCommandRegister = $this->getMockBuilder(PrivateChatCommandRegister::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $this->expectException(SetCommandsFailedException::class);
        $privateChatCommandRegister->checkifCommandsAreSet($commands, $updatedCommands);
    }

    public function testCheckIfCommandsAreSetWithEmptyCommands(): void
    {
        $commands = [];
        $updatedCommands = [];

        $privateChatCommandRegister = $this->getMockBuilder(PrivateChatCommandRegister::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $this->assertEmpty($privateChatCommandRegister->checkifCommandsAreSet($commands, $updatedCommands));
    }

    public function testCheckIfCommandsAreSetWithExtraCommands(): void
    {
        $commands = [
            [
                "command" => "test_command",
                "description" => "Test command"
            ]
        ];

        $updatedCommands = [
            [
                "command" => "test_command",
                "description" => "Test command"
            ],
            [
                "command" => "extra_command",
                "description" => "Extra command"
            ]
        ];

        $privateChatCommandRegister = $this->getMockBuilder(PrivateChatCommandRegister::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $this->expectException(SetCommandsFailedException::class);
        $privateChatCommandRegister->checkifCommandsAreSet($commands, $updatedCommands);
    }
}
