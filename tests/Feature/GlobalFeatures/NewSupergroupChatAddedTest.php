<?php

namespace Tests\Feature\Middleware;

use App\Models\Chat;
use App\Models\ChatAdmins;
use Illuminate\Support\Facades\Schema;
use App\Enums\ResTime;
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

        $this->createAdminsTable($chat);
        $this->createNewUsersRestrictionsTable($chat);
        $this->createBadWordsFilterTable($chat);
        $this->createUnusualCharsFilterTable($chat);
    }

    private function createBadWordsFilterTable($chat)
    {
        $this->assertEquals($chat->id, $chat->badWordsFilter->chat_id);
        $this->assertEquals(1, $chat->badWordsFilter->enabled);
        $this->assertEquals(0, $chat->badWordsFilter->delete_user);
        $this->assertEquals(1, $chat->badWordsFilter->enabled);
        $this->assertEquals(1, $chat->badWordsFilter->delete_message);
        $this->assertEquals(0, $chat->badWordsFilter->can_send_messages);
        $this->assertEquals(0, $chat->badWordsFilter->can_send_media);
        $this->assertEquals(ResTime::TWO_HOURS->value, $chat->badWordsFilter->restriction_time);
    }

    private function createNewUsersRestrictionsTable($chat)
    {
        $this->assertEquals($chat->id, $chat->newUserRestrictions->chat_id);
        $this->assertEquals(1, $chat->newUserRestrictions->enabled);
        $this->assertEquals(0, $chat->newUserRestrictions->can_send_messages);
        $this->assertEquals(0, $chat->newUserRestrictions->can_send_media);
        $this->assertEquals(ResTime::DAY->value, $chat->newUserRestrictions->restriction_time);
    }

    private function createAdminsTable($chat)
    {
        foreach ($chat->admins as $admin) {
            $this->assertNotNull($admin->admin_id);
            $this->assertNotNull($admin->first_name);
            $this->assertNotNull($admin->username);
            $this->assertEquals($chat->id, $admin->pivot->chat_id);
            $this->assertEquals(1, $admin->pivot->private_commands_access);
            $this->assertEquals(1, $admin->pivot->group_commands_access);
            $this->assertEquals(1, $admin->pivot->my_commands_set);
        }
    }

    private function createUnusualCharsFilterTable($chat)
    {
        $this->assertEquals($chat->id, $chat->unusualCharsFilter->chat_id);
        $this->assertEquals(1, $chat->unusualCharsFilter->enabled);
        $this->assertEquals(0, $chat->unusualCharsFilter->delete_user);
        $this->assertEquals(1, $chat->unusualCharsFilter->enabled);
        $this->assertEquals(1, $chat->unusualCharsFilter->delete_message);
        $this->assertEquals(0, $chat->unusualCharsFilter->can_send_messages);
        $this->assertEquals(0, $chat->unusualCharsFilter->can_send_media);
        $this->assertEquals(ResTime::TWO_HOURS->value, $chat->unusualCharsFilter->restriction_time);
    }
}



