<?php

namespace Tests\Feature\Middleware;

use App\Models\Eloquent\BotChat;
use App\Models\StatusUpdates\StatusUpdateModel;
use App\Models\StatusUpdates\InvitedUserUpdateModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Services\ChatRulesService;
use App\Models\BaseTelegramRequestModel;
use Illuminate\Support\Facades\Http;
use App\Services\CONSTANTS;
use Tests\TestCase;

class NewSupergroupChatAddedTest extends TestCase
{
    use RefreshDatabase;
    protected function setUp(): void
    {
        parent::setUp();
    }

    /** 
     * Test case where new supergroup request comes and new chat is created in the database
     * as soon as the bot is added to a new group and gets admin rights and first message is sent in that chat by anyone
     * request is comes and new chat is created in the database with bot commands set
     */
    public function testNewSupergroupRequestCreatesNewChatInDatabaseWithBotCommandsSet(): void
    {
        $data = $this->getMessageModelData();
        $this->post("api/webhook", $data);

        $requestModel = (new BaseTelegramRequestModel($data))->getModel();
        $chat = new BotChat();
        $newChat = $chat->where("chat_id", $requestModel->getChatId())->first();
        // dd($newChat);
        $this->assertEquals($requestModel->getChatId(), $newChat->chat_id);
        $this->assertEquals($requestModel->getChatTitle(), $newChat->chat_title);
        $this->assertEquals($requestModel->getAdminsIds(), $newChat->chat_admins);
        $this->assertEquals($requestModel->getAdminsIds(), $newChat->private_commands_access);
        $this->assertEquals("admins", $newChat->group_commands_access);
    }
}
