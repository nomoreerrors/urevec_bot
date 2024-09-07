<?php

namespace Tests\Feature\Traits;

use App\Classes\PrivateChatCommandCore;
use App\Classes\ChatSelector;
use App\Models\Chat;
use App\Services\TelegramBotService;
use App\Models\Admin;

trait MockBotService
{
    use BaseMockTrait;
    use MockMenu;

    protected $mockBotService;

    protected $mockPrivateChatCommandHandler;

    protected function mockBotCreate()
    {
        $this->mockBotService = $this->createMock(TelegramBotService::class);
    }

    /**
     * Recreate mockBotService 
     * @return void
     */
    protected function mockBotRefresh()
    {
        $this->mockBotCreate();
    }


    /**
     * Set botService menu method to return $this->mockMenu 
     * @param mixed $count
     * @return void
     */
    protected function mockBotMenuCreate(?int $count = null)
    {
        if (empty($this->mockMenu)) {
            $this->mockMenuCreate();
        }
        $this->setupExpectation("menu", $this->mockMenu, $count);
    }



    protected function mockBotGetAdminMethod(Admin $admin, ?int $count = null)
    {
        $this->setupExpectation("getAdmin", $admin, $count);
    }

    protected function mockBotGetChatMethod(Chat $chat, ?int $count = null)
    {
        $this->setupExpectation("getChat", $chat, $count);
    }


    protected function mockBotCommand(string $command, ?int $count = null)
    {
        $this->setupExpectation("getPrivateChatCommand", $command, $count);
    }

    /**
     * BotService setPrivateChatCommand method expect to be called
     * @param string $command
     * @param mixed $count
     * @return void
     */
    protected function expectBotSetPrivateChatCommand(string $command, ?int $count = null)
    {
        $this->expectWith("setPrivateChatCommand", $command);
    }

    /**
     * Summary of mockBotCommandHandler
     * @param string $type only "private" for now
     * @param mixed $count  expected call count
     * @param mixed $commandHandler should be mocked or null
     * @return void
     */
    protected function mockBotCommandHandler(string $type, ?int $count = null, ?object $commandHandler = null)
    {
        if ($type == "private") {
            $handler = $commandHandler ?? new PrivateChatCommandCore($this->mockBotService);
        }
        $this->setupExpectation("commandHandler", $handler, $count);
    }



    /**
     *  Expected to be created after the mockBotService privatechatcommand is set
     * @param mixed $service
     * @param mixed $count
     * @return void
     */
    protected function mockBotChatSelector($service = null, ?int $count = null)
    {
        $arg = $service ?? $this->mockBotService;
        $this->setupExpectation("chatSelector", new ChatSelector($arg), $count);
    }


    protected function expectReplyMessage(mixed $message, ?array $params = null, ?int $count = null)
    {
        $this->expectWith("sendMessage", $message, $params, $count);
    }


    protected function expectBotServiceChatWillBeSet(int $chatId): void
    {
        $this->expectWith("setChat", $chatId);
    }


    protected function expectMockMenuMethod(string $method, ?int $count = null)
    {
        if (empty($this->mockMenu)) {
            $this->mockMenuCreate();
        }

        $this->mockMenu->expects($this->countOrAny($count))
            ->method($method);
    }

    /**
     * Summary of createFakeBotCommandHandler
     * @param string $type "private" for now
     * @return void
     */
    protected function fakeBotCommandHandlerCreate(string $type)
    {
        if ($type == "private") {
            $fakeCommandHandler = $this->createMock(PrivateChatCommandCore::class);
            $this->mockBotCommandHandler("private", null, $fakeCommandHandler);
        }
    }

    // protected function expectMockBotCommandHandlerWillCallMethod(string $method, ?int $count = null)
    // {
    //     $this->mockPrivateChatCommandHandler()
    //         ->expects($this->countOrAny($count))
    //         ->method($method);
    // }

    // protected function mockPrivateChatCommandHandler()
    // {
    //     $this->mockCommandHandler = $this->createMock(PrivateChatCommandCore::class);
    // }

}

