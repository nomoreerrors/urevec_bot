<?php

namespace Tests\Feature\Middleware;

use App\Models\Chat;
use App\Models\ChatAdmins;
use App\Models\StatusUpdates\StatusUpdateModel;
use App\Models\StatusUpdates\InvitedUserUpdateModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Services\ChatRulesService;
use App\Models\TelegramRequestModelBuilder;
use Illuminate\Support\Facades\Http;
use App\Services\CONSTANTS;
use Tests\TestCase;

class NewSupergroupChatAddedTest extends TestCase
{
    use RefreshDatabase;

    public function testNewSupergroupChatCreatesAndBotCommandsAreSet()
    {
        $requestData = $this->getMessageModelData();
        $this->postJson('api/webhook', $requestData);

        //!! RefreshDatabase isn't working while using xdebug and tables are always empty
        $requestModel = (new TelegramRequestModelBuilder($requestData))->create();

        $chat = Chat::where('chat_id', $requestModel->getChatId())->first();

        $this->assertEquals($chat->chat_title, $requestModel->getChatTitle());

        foreach ($chat->admins as $admin) {
            $this->assertNotNull($admin->admin_id);
            $this->assertNotNull($admin->first_name);
            $this->assertNotNull($admin->username);
            $this->assertEquals(1, $admin->pivot->private_commands_access);
            $this->assertEquals(1, $admin->pivot->group_commands_access);
            $this->assertEquals(1, $admin->pivot->my_commands_set);
        }
    }
}

