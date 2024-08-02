<?php

namespace Tests\Feature\ServicesTests\TelegramBotService;

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

    protected array $data;
    protected function setUp(): void
    {
        parent::setUp();
        $this->data = $this->getMessageModelData();
    }

    public function testNewChatCreatesAndAdminsAttached(): void
    {
        //Prepare fake admins ids so that ModelBuilder can get them instead of calling api
        $this->fakeResponseWithAdminsIds(123, 456);
        $requesModel = (new TelegramRequestModelBuilder($this->data))->create();
        $botService = new TelegramBotService($requesModel);
        $chat = $botService->createChat();

        $admins = $chat->admins;

        $this->assertDatabaseHas('chats', ['chat_id' => $chat->chat_id]);
        $this->assertDatabaseHas('admins', ['admin_id' => 123]);
        $this->assertDatabaseHas('admins', ['admin_id' => 456]);
        foreach ($admins as $admin) {
            $this->assertEquals($admin->pivot->chat_id, $chat->id);
        }
    }

    /**
     * Testcase where an admin own a few chats, and previously added bot to his chat so that admin
     * and chat already exists in database, and now he added bot to another chat. Make sure that admin
     * won't duplicate in database but will be attached to the new chat
     * @return void
     */
    public function testAttachingAdminToANewChatAndNotDublicateifAdminAlreadyExists(): void
    {
        // Prepare fake admins ids so that ModelBuilder can get them instead of calling api
        $this->data["message"]["chat"]["id"] = -1234567890;
        $this->fakeResponseWithAdminsIds(123, 100);
        $requesModel = (new TelegramRequestModelBuilder($this->data))->create();
        $botService = new TelegramBotService($requesModel);
        // Mock that the admin already exists in database and attached to the chat
        $chat = $botService->createChat();
        $this->assertDatabaseHas('chats', ['chat_id' => $chat->chat_id]);
        $this->assertDatabaseHas('admins', ['admin_id' => 123]);

        // Mock adding bot to another chat and request with a new chat id is coming
        $this->data["message"]["chat"]["id"] = -1010101010;
        $this->fakeResponseWithAdminsIds(123, 888);
        // Set up model with fake ids
        $requesModel = (new TelegramRequestModelBuilder($this->data))->create();
        $botService = new TelegramBotService($requesModel);
        // Create a new chat
        $chat = $botService->createChat();
        $admins = Admin::where("admin_id", 123)->get();
        // Assert that admin id didn't added to a database admins table twice
        $this->assertEquals(1, $admins->count());
        // Assert that admin is attached to the both chats
        $admin = $admins->first();
        $this->assertEquals(2, $admin->chats->count());
    }
}
