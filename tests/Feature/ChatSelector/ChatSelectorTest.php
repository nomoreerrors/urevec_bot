<?php

namespace Feature\ChatSelector;

use App\Classes\PrivateChatCommandCore;
use App\Models\MessageModels\TextMessageModel;
use App\Classes\Buttons;
use Mockery;
use Mockery\MockInterface;
use ReflectionProperty;
use ReflectionClass;
use ReflectionMethod;
use App\Classes\ChatSelector;
use App\Enums\ModerationSettingsEnum;
use App\Services\TelegramBotService;
use App\Classes\Menu;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Enums\NewUserRestrictionsEnum;
use Database\Seeders\SimpleSeeder;
use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Chat;
use Tests\Feature\Traits\MockBotService;

class ChatSelectorTest extends TestCase
{
    use RefreshDatabase;
    use MockBotService;

    public function setUp(): void
    {
        parent::setUp();
        $this->mockBotCreate();
        $this->admin = $this->setAdminWithMultipleChats(2);
        $this->chat = $this->admin->chats->first();
        $this->deleteSelectedChatFromCache($this->admin->admin_id);
        $this->deleteLastCommandFromCache($this->admin->admin_id);
    }

    public function testHasOnlyOneChatReturnsTrueCase(): void
    {
        $this->prepareAdminWithOneChat();
        $this->mockBotCommand("random command");
        $this->mockBotGetAdminMethod($this->admin);

        $this->expectBotServiceChatWillBeSet($this->admin->chats->first()->chat_id);
        new ChatSelector($this->mockBotService);
    }

    /**
     * SELECT METHOD TEST
     * @return void
     */
    public function testTryToGetLastSelectedChatIdFromCacheIsTrueCase(): void
    {
        $this->putSelectedChatIdToCache($this->admin->admin_id, 12345);
        $this->mockBotCommand("random command");
        $this->mockBotGetAdminMethod($this->admin);

        $this->expectBotServiceChatWillBeSet($this->admin->chats->first()->chat_id);
        $this->expectSelectChatButtonsWillNotSent();
        //Calling select method automatically inside the constructor
        new ChatSelector($this->mockBotService);
    }

    /**
     * Testcase where user requested a select chat menu and the buttons with groups titles expected to be sent
     * @return void
     */
    public function testSelectChatMenuRequestIsTrueSelectChatButtonsWillBeSent()
    {
        $this->mockBotCommand(ModerationSettingsEnum::SELECT_CHAT->value);
        $this->mockBotGetAdminMethod($this->admin);
        $this->mockBotMenuCreate(1);

        $titles = $this->admin->chats()->pluck('chat_title')->toArray();
        $buttons = (new Buttons())->getSelectChatButtons($titles);

        $this->expectReplyMessageWillBeSent(
            ModerationSettingsEnum::SELECT_CHAT->replyMessage(),
            $buttons
        );

        (new ChatSelector($this->mockBotService))->select();
    }

    public function testIsSelectedChatCommandReturnsTrueCase(): void
    {
        $this->mockBotGetAdminMethod($this->admin);
        $this->mockBotCommand($this->admin->chats()->first()->chat_title);
        $this->mockBotGetChatMethod($this->admin->chats()->first());

        $this->mockMenuCreate();
        $this->mockMenu->expects($this->once())
            ->method("back");

        $this->mockBotService->expects($this->once())
            ->method("menu")
            ->willReturn($this->mockMenu);

        $this->expectBotServiceChatWillBeSet($this->admin->chats->first()->chat_id);
        $this->expectReplyMessageWillBeSent($this->stringContains($this->admin->chats->first()->chat_title));

        $chatSelector = new ChatSelector($this->mockBotService);
        $chatSelector->select();

        $this->assertUpdateFlagWasSetToTrue($chatSelector);
        $this->assertLastChatIdWasCached($this->admin->admin_id, $this->chat->chat_id);
    }

    public function testSendSelectChatButtonsInDefaultCase()
    {
        $command = "test test";
        $this->mockBotCommand($command);
        $this->mockBotGetAdminMethod($this->admin);


        $titles = $this->admin->chats()->pluck('chat_title')->toArray();
        $buttons = (new Buttons())->getSelectChatButtons($titles);

        $this->expectReplyMessageWillBeSent(
            ModerationSettingsEnum::SELECT_CHAT->replyMessage(),
            $buttons
        );

        $this->mockMenuCreate();
        $this->mockMenu->expects($this->once())
            ->method("save");

        $this->mockBotService->expects($this->once())
            ->method("menu")
            ->willReturn($this->mockMenu);

        (new ChatSelector($this->mockBotService))->select();
        // $this->assertEquals($command, $this->getLastCommandFromCache($this->admin->admin_id));
    }


    private function expectSelectChatButtonsWillNotSent(): void
    {
        $this->mockBotService->expects($this->never())
            ->method('sendMessage');
    }

    private function assertUpdateFlagWasSetToTrue(ChatSelector $chatSelector): void
    {
        $this->assertTrue($this->getValueOfProtectedProperty('updated', $chatSelector));
    }


    private function mockBotServiceGetChatMethod(Chat $chat): void
    {
        $this->mockBotService->expects($this->any())
            ->method('getChat')
            ->willReturn($chat);
    }


    private function prepareAdminWithOneChat(): void
    {
        $this->admin->chats()->first()->delete();
        $this->admin->refresh();
        $this->assertEquals(1, $this->admin->chats->count());
    }
}



