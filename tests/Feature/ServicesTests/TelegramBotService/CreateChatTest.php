<?php

namespace Tests\Feature\ServicesTests\TelegramBotService;

use App\Models\MessageModels\TextMessageModel;
use App\Models\Chat;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Admin;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\TelegramRequestModelBuilder;
use App\Services\TelegramBotService;
use App\Classes\CommandsList;

class CreateChatTest extends TestCase
{
    use RefreshDatabase;

    private array $firstAdmin = [
        'admin_id' => 99999,
        'is_bot' => false,
        'first_name' => 'Dolph Lundgren',
        'username' => 'DolphLundgren'
    ];

    private array $secondAdmin = [
        'admin_id' => 88888,
        'is_bot' => false,
        'first_name' => 'Antonio Banderos',
        'username' => 'AntonioBanderos'
    ];

    // protected TelegramBotService $botService;
    // protected $requestModel;
    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Test that if some relationships models were created, it'll be added to the existed chat  automatically
     * @return void
     */
    public function testUnexistedRelationshipsAreCreated(): void
    {
        $chatId = 123;
        $chat = $this->getChatWithAdmins($chatId);


        $this->botService = $this->getMockBuilder(TelegramBotService::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getRequestModel', 'getChatRelations', 'findChat', 'setMyCommands', 'createChatAdmins'])
            ->getMock();

        $this->botService->expects($this->any())
            ->method('getChatRelations')
            ->willReturn(['newUserRestrictions', 'admins', 'linksFilter', 'badWordsFilter', 'unusualCharsFilter']);

        $this->botService->expects($this->once())
            ->method('findChat')
            ->willReturn($chat);

        //Expected to be skipped
        $this->botService->expects($this->never())
            ->method('setMyCommands');
        $this->botService->expects($this->never())
            ->method('createChatAdmins');


        //Assert that all relationships are null before the test 
        $this->assertNull($chat->newUserRestrictions()->first());
        $this->assertNull($chat->linksFilter()->first());
        $this->assertNull($chat->badWordsFilter()->first());
        $this->assertNull($chat->unusualCharsFilter()->first());

        $this->botService->createChat();
        //Assert that all relationships are not null after the test
        $this->assertNotNull($chat->newUserRestrictions()->first());
        $this->assertNotNull($chat->linksFilter()->first());
        $this->assertNotNull($chat->badWordsFilter()->first());
        $this->assertNotNull($chat->unusualCharsFilter()->first());
    }


    public function testNewChatCreatedWithAdminsAttachedAndAllRelationshipsAreAdded(): void
    {
        $chatId = 123;
        $chatTitle = 'some title';
        //Make so that the create new chat part of code won't be skipped 
        $this->prepareCreateNewChat($chatId, $chatTitle);

        $this->botService->createChat();
        $chat = Chat::where('chat_id', $chatId)->first();

        $this->assertAdminsAttached($chat);
        // Assert that all relationships are not null after the test
        $this->assertNotNull($chat->newUserRestrictions()->first());
        $this->assertNotNull($chat->linksFilter()->first());
        $this->assertNotNull($chat->badWordsFilter()->first());
        $this->assertNotNull($chat->unusualCharsFilter()->first());
    }

    private function assertAdminsAttached(Chat $chat): void
    {
        $admins = $chat->admins()->get();
        $adminsIds = $admins->pluck('admin_id')->toArray();
        $this->assertContains(99999, $adminsIds);
        $this->assertContains(88888, $adminsIds);
    }


    /**
     *  Test that if admin already exists in database, because he has been attached to another chat previously
     *  the copy of admin in Admins table won't be created
     * @return void
     */
    public function testNoAdminsDublicates(): void
    {
        $chatId = 123;
        $chatTitle = 'some title';
        //prepare chat with two admins
        $this->prepareCreateNewChat($chatId, $chatTitle);

        $this->botService->createChat();
        $chat = Chat::where('chat_id', $chatId)->first();
        $this->assertAdminsAttached($chat);



        //Prepare to create a  new chat but with the same admins as in the previous chat
        $secondChatId = 555;
        $secondChatTitle = 'another title';
        $this->prepareCreateNewChat($secondChatId, $secondChatTitle);
        $this->botService->createChat();

        $secondChat = Chat::where('chat_id', $secondChatId)->first();
        $this->assertAdminsAttached($secondChat);

        $this->assertEquals(2, Admin::all()->count());
        $this->assertEquals(2, Admin::first()->chats->count());
    }

    public function testNoChatsDublicates(): void
    {
        $chatId = 123;
        $chat = $this->getChatWithAdmins($chatId);


        $this->botService = $this->getMockBuilder(TelegramBotService::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getRequestModel', 'getChatRelations', 'findChat', 'setMyCommands', 'createChatAdmins'])
            ->getMock();

        $this->botService->expects($this->any())
            ->method('getChatRelations')
            ->willReturn(['newUserRestrictions', 'admins', 'linksFilter', 'badWordsFilter', 'unusualCharsFilter']);

        //Fake that chat is already in DB
        $this->botService->expects($this->once())
            ->method('findChat')
            ->willReturn($chat);

        //Expected to be skipped
        $this->botService->expects($this->never())
            ->method('setMyCommands');
        $this->botService->expects($this->never())
            ->method('createChatAdmins');

        $this->botService->createChat();
    }


    private function prepareCreateNewChat(int $chatId, string $chatTitle): void
    {

        // Prepare request model
        $this->requestModel = $this->createMock(TextMessageModel::class);
        $this->requestModel->expects($this->any())
            ->method('getChatId')
            ->willReturn($chatId);

        $this->requestModel->expects($this->any())
            ->method('getChatTitle')
            ->willReturn($chatTitle);

        $this->requestModel->expects($this->any())
            ->method('getAdmins')
            ->willReturn(
                [$this->firstAdmin, $this->secondAdmin]
            );


        $this->botService = $this->getMockBuilder(TelegramBotService::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getRequestModel', 'getChatRelations', 'findChat', 'setMyCommands'])
            ->getMock();


        $this->botService->expects($this->any())
            ->method('getChatRelations')
            ->willReturn(['newUserRestrictions', 'admins', 'linksFilter', 'badWordsFilter', 'unusualCharsFilter']);

        $this->botService->expects($this->once())
            ->method('findChat')
            ->willReturn(null);

        $this->botService->expects($this->once())
            ->method('setMyCommands');

        $this->botService->expects($this->any())
            ->method('getRequestModel')
            ->willReturn($this->requestModel);
    }

    /**
     * Get a chat with 2 admins but without any relationships
     * @return \App\Models\Chat
     */
    private function getChatWithAdmins(int $id): Chat
    {
        $chat = Chat::factory()->create([
            'chat_id' => $id,
            'chat_title' => 'some title'
        ]);

        $admins[] = Admin::create($this->firstAdmin);
        $admins[] = Admin::create($this->secondAdmin);

        foreach ($admins as $admin) {
            $admin->chats()->attach($chat->id);
        }
        return $chat;
    }

}
