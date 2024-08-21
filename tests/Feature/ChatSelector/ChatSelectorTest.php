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
use App\Enums\ResNewUsersEnum;
use Database\Seeders\SimpleSeeder;
use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Chat;

class ChatSelectorTest extends TestCase
{
    use RefreshDatabase;

    private $mockBotService;
    private $mockMenu;

    public function setUp(): void
    {
        parent::setUp();
        $this->mockBotService = $this->createMock(TelegramBotService::class);
        $this->admin = $this->setAdminWithMultipleChats(2);
        $this->chat = $this->admin->chats->first();
        $this->mockMenu = $this->createMock(Menu::class);
        $this->deleteSelectedChatFromCache($this->admin->admin_id);
        $this->deleteLastCommandFromCache($this->admin->admin_id);
    }

    public function testHasOnlyOneChatReturnsTrueCase(): void
    {
        $this->prepareAdminWithOneChat();
        $this->mockBotServiceGetPrivateChatCommandMethod("random command", $this->mockBotService);
        $this->mockBotServiceGetAdminMethod();
        $this->assertBotServiceChatWasSet($this->mockBotService, $this->admin->chats->first()->chat_id);
        new ChatSelector($this->mockBotService, $this->mockMenu);
    }

    /**
     * SELECT METHOD TEST
     * @return void
     */
    public function testTryToGetLastSelectedChatIdFromCacheIsTrueCase(): void
    {
        $this->putSelectedChatIdToCache($this->admin->admin_id, 12345);
        $this->mockBotServiceGetAdminMethod();
        $this->mockBotServiceGetPrivateChatCommandMethod("some command", $this->mockBotService);

        $this->assertBotServiceChatWasSet($this->mockBotService, 12345);
        $this->assertSelectChatButtonsWereNotSent();
        //Calling select method automatically inside the constructor
        new ChatSelector($this->mockBotService, $this->mockMenu);
    }

    /**
     * Testcase where user requested a select chat menu and the buttons with groups titles expected to be sent
     * @return void
     */
    public function testIsSelectChatMenuRequestIsTrueCase()
    {
        $this->mockBotServiceGetPrivateChatCommandMethod(ModerationSettingsEnum::SELECT_CHAT->value, $this->mockBotService);
        $this->mockBotServiceGetAdminMethod();
        $this->assertSelectChatMenuKeyBoardWasSent();
        new ChatSelector($this->mockBotService, new Menu($this->mockBotService));
    }

    public function testIsSelectedChatCommandReturnsTrueCase(): void
    {
        $this->mockConstructorDeps();
        $this->assertBotServiceChatWasSet($this->mockBotService, $this->chat->chat_id);
        $this->assertRepliesWithSelectedChatTitle();
        $this->assertThatLastCommandRestored();
        $this->assertBackMenuWasCalled();

        $chatSelector = new ChatSelector($this->mockBotService, $this->mockMenu);
        $this->assertUpdateFlagWasSetToTrue($chatSelector);
        $this->assertLastChatIdWasCached($this->admin->admin_id, $this->chat->chat_id);
    }

    public function testSendSelectChatButtonsAndStoreCommandInDefaultCase()
    {
        $command = "test test";
        $this->mockBotServiceGetPrivateChatCommandMethod($command, $this->mockBotService);
        $this->mockBotServiceGetAdminMethod();
        $this->assertSelectChatMenuKeyBoardWasSent();

        new ChatSelector($this->mockBotService, new Menu($this->mockBotService));
        $this->assertEquals($command, $this->getLastCommandFromCache($this->admin->admin_id));
    }


    public function assertThatLastCommandRestored()
    {
        $this->putLastCommandToCache($this->admin->admin_id, "test");
        $this->mockBotServiceGetPrivateChatCommandMethod("test", $this->mockBotService);
    }

    private function prepareRequestModelExpectations(): void
    {
        $mockRequestModel = $this->createMock(TextMessageModel::class);
        $mockRequestModel->expects($this->any())
            ->method('getFromId')
            ->willReturn($this->admin->admin_id);
        //Private chat chat id and from id are equal
        $mockRequestModel->expects($this->any())
            ->method('getChatId')
            ->willReturn($this->admin->admin_id);
        $this->mockBotService->expects($this->once())
            ->method('getRequestModel')
            ->willReturn($mockRequestModel);
    }

    private function prepareBotServiceExpectations(): void
    {
        $this->mockBotServiceGetPrivateChatCommandMethod($this->chat->chat_title, $this->mockBotService);
        $this->mockBotServiceGetAdminMethod();
        $this->mockBotServiceGetChatMethod($this->chat);
    }

    private function mockBotServiceGetAdminMethod()
    {
        $this->mockBotService->expects($this->any())
            ->method('getAdmin')
            ->willReturn($this->admin);
    }



    private function assertSelectChatButtonsWereNotSent(): void
    {
        $this->mockBotService->expects($this->never())
            ->method('sendMessage');
    }

    private function getSelectChatMenuKeyboard(): array
    {
        return (new Buttons())
            ->create($this->admin->chats()->pluck('chat_title')->toArray(), 1, true);
    }

    private function assertSelectChatMenuKeyBoardWasSent(): void
    {
        $this->assertMessageWasSent(
            $this->mockBotService,
            $this->stringContains(ModerationSettingsEnum::SELECT_CHAT->replyMessage()),
            $this->getSelectChatMenuKeyboard()
        );
    }

    private function assertUpdateFlagWasSetToTrue(ChatSelector $chatSelector): void
    {
        $this->assertTrue($this->getValueOfProtectedProperty('updated', $chatSelector));
    }

    private function assertRepliesWIthSelectedChatTitle(): void
    {
        $this->assertMessageWasSent(
            $this->mockBotService,
            $this->stringContains($this->chat->chat_title)
        );
    }

    private function mockBotServiceGetChatMethod(Chat $chat): void
    {
        $this->mockBotService->expects($this->any())
            ->method('getChat')
            ->willReturn($chat);
    }

    private function assertBackMenuWasCalled()
    {
        $this->mockMenu->expects($this->once())
            ->method('back');
    }

    private function mockConstructorDeps()
    {
        $this->prepareRequestModelExpectations();
        $this->prepareBotServiceExpectations();
    }

    private function prepareAdminWithOneChat(): void
    {
        $this->admin->chats()->first()->delete();
        $this->admin->refresh();
        $this->assertEquals(1, $this->admin->chats->count());
    }
}



