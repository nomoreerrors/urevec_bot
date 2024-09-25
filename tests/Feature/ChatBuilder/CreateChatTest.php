<?php

namespace Tests\Feature\ChatBuilder;

use App\Models\MessageModels\TextMessageModel;
use App\Classes\PrivateChatCommandRegister;
use App\Classes\ChatBuilder;
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

    private $chatBuilder = null;

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
    public function testUnexistedRelationshipsAreAddedToAnExistingChat(): void
    {
        $chatId = 123;
        $chat = $this->getChatWithAdmins($chatId);


        $this->botService = $this->getMockBuilder(TelegramBotService::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getRequestModel', 'chatBuilder'])
            ->getMock();


        $this->chatBuilder = $this->getMockBuilder(ChatBuilder::class)
            ->setConstructorArgs([$this->botService])
            ->onlyMethods(['getChatRelationsNames', 'findChat', 'createChatAdmins'])
            ->getMock();

        //Expected to be skipped
        $this->chatBuilder->expects($this->never())
            ->method('createChatAdmins');


        //Prepare relatioins for the updateChatRelations() method     
        $this->chatBuilder->expects($this->any())
            ->method('getChatRelationsNames')
            ->willReturn(['newUserRestrictions', 'admins', 'linksFilter', 'badWordsFilter', 'unusualCharsFilter']);

        $this->chatBuilder->expects($this->once())
            ->method('findChat')
            ->willReturn($chat);

        $this->botService->expects($this->any())
            ->method('chatBuilder')->willReturn($this->chatBuilder);


        //Assert that all relationships are null before the test 
        $this->assertNull($chat->newUserRestrictions()->first());
        $this->assertNull($chat->linksFilter()->first());
        $this->assertNull($chat->badWordsFilter()->first());
        $this->assertNull($chat->unusualCharsFilter()->first());

        // $j = $chat->newUserRestrictions();
        $this->chatBuilder->createChat();
        //Assert that all relationships are not null after the test
        $this->assertNotNull($chat->newUserRestrictions()->first());
        $this->assertNotNull($chat->linksFilter()->first());
        $this->assertNotNull($chat->badWordsFilter()->first());
        $this->assertNotNull($chat->unusualCharsFilter()->first());
    }


    public function testCreateNewChatWithAdminsAttached(): void
    {
        //Prepare request model with chat id and title and also admins that will be attached to the chat
        $this->mockRequestModel(123, 'some title');

        //Prepare bot with 'setChat' to be skipped to not test it this time 
        // and 'getRequestModel' to assign chatId and chatTitle  to the newly created chat
        $this->botService = $this->getMockBuilder(TelegramBotService::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getRequestModel', 'setChat'])
            ->getMock();


        $this->botService->expects($this->any())->method('getRequestModel')->willReturn($this->requestModel);

        //Prepare chatBuilder with botService in constructor and with list of methods that should be skipped
        $this->chatBuilder = $this->getMockBuilder(ChatBuilder::class)
            ->setConstructorArgs([$this->botService])
            ->onlyMethods(['getChatRelationsNames', 'findChat', 'setMyCommands'])
            ->getMock();


        $this->chatBuilder->createChat();
        //Find newly created chat
        $chat = Chat::where('chat_id', 123)->first();

        $this->assertAdminsAttached($chat);
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
        //Prepare request model with chat id and title and also admins that will be attached to the chat
        $this->mockRequestModel($chatId, $chatTitle);
        //Prepare bot with request model and with 'setChat' method to skip it
        $this->botService = $this->getMockBuilder(TelegramBotService::class)
            ->onlyMethods(['getRequestModel', 'setChat'])
            ->getMock();

        $this->botService->expects($this->any())->method('getRequestModel')->willReturn($this->requestModel);

        //Prepare chatBuilder with botService in constructor and 
        //With list of methods that should be skipped because we're only interested that admins are created without duplicates
        $this->chatBuilder = $this->getMockBuilder(ChatBuilder::class)
            ->setConstructorArgs([$this->botService])
            ->onlyMethods(['updateChatRelations', 'findChat', 'setMyCommands'])
            ->getMock();


        //First time create a new chat
        $this->chatBuilder->createChat();
        $chat = Chat::where('chat_id', $chatId)->first();
        $this->assertAdminsAttached($chat);




        //Prepare to create a new chat but with the same admins as in the previous chat
        $secondChatId = 555;
        $secondChatTitle = 'another title';

        //Prepare request model with chat id and title and also admins that will be attached to the chat
        $this->mockRequestModel($secondChatId, $secondChatTitle);
        //Prepare bot with request model and set 'setChat' method  to be skipped
        $this->botService = $this->getMockBuilder(TelegramBotService::class)
            ->onlyMethods(['getRequestModel', 'setChat'])
            ->getMock();

        $this->botService->expects($this->any())->method('getRequestModel')->willReturn($this->requestModel);

        //Prepare chatBuilder with botService in constructor and set methods that should be skipped
        $this->chatBuilder = $this->getMockBuilder(ChatBuilder::class)
            ->setConstructorArgs([$this->botService])
            ->onlyMethods(['updateChatRelations', 'findChat', 'setMyCommands'])
            ->getMock();


        //Second time create a new chat
        $this->chatBuilder->createChat();


        // Find chats to assert that there are 2 chats and both have 2 admins
        $chat = Chat::where('chat_id', $chatId)->first();
        $this->assertAdminsAttached($chat);

        $secondChat = Chat::where('chat_id', $secondChatId)->first();
        $this->assertAdminsAttached($secondChat);



        // Assert that there are still only 2 admins in the database at all
        $this->assertEquals(2, Admin::all()->count());
        // Assert that there are 2 chats
        $this->assertEquals(2, Admin::first()->chats->count());
    }


    public function testNoChatsDublicates(): void
    {
        $chatId = 777;
        $chat = $this->getChatWithAdmins($chatId);

        //Set chat method should be skipped
        $this->botService = $this->getMockBuilder(TelegramBotService::class)
            ->onlyMethods(['setChat'])
            ->getMock();


        //Prepare chatBuilder with botService in constructor
        // And methods that should be skipped
        $this->chatBuilder = $this->getMockBuilder(ChatBuilder::class)
            ->setConstructorArgs([$this->botService])
            ->onlyMethods(['findChat', 'createChatAdmins', 'setMyCommands'])
            ->getMock();


        //Fake that chat is already in DB to assert that creating a new one will be skipped 
        $this->chatBuilder->expects($this->once())->method('findChat')->willReturn($chat);
        //Just expect to be called once

        //Expected to be skipped
        $this->chatBuilder->expects($this->never())->method('setMyCommands');
        $this->chatBuilder->expects($this->never())->method('createChatAdmins');

        $this->chatBuilder->createChat();
        // Assert that there are only one chat that we put in DB at the top of the test
        // And no more chats will be created
        $this->assertEquals(1, Chat::all()->count());
    }


    private function mockRequestModel(int $chatId, string $chatTitle): void
    {
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
    }


    private function mockBotWithRequestModel($chatId, $chatTitle)
    {
        $this->mockRequestModel($chatId, $chatTitle);
        //Prepare bot
        $this->botService = $this->getMockBuilder(TelegramBotService::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getRequestModel'])
            ->getMock();


        $this->botService->expects($this->any())
            ->method('getRequestModel')
            ->willReturn($this->requestModel);
    }


    /**
     * Get chat with 2 admins but without any relationships
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

    private function assertAdminsAttached(Chat $chat): void
    {
        $admins = $chat->admins()->get();
        $adminsIds = $admins->pluck('admin_id')->toArray();
        $this->assertContains($this->firstAdmin['admin_id'], $adminsIds);
        $this->assertContains($this->secondAdmin['admin_id'], $adminsIds);
    }

}
