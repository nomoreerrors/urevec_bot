<?php

namespace Tests\Feature\Middleware;

use App\Models\Chat;
use App\Models\ChatAdmins;
use App\Models\StatusUpdates\StatusUpdateModel;
use App\Models\StatusUpdates\InvitedUserUpdateModel;
use App\Services\TelegramBotService;
use Database\Seeders\SimpleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Services\ChatRulesService;
use App\Models\TelegramRequestModelBuilder;
use Illuminate\Support\Facades\Http;
use App\Services\CONSTANTS;
use App\Models\Admin;
use Tests\TestCase;

/**
 * Test where an admin that is existed in database writes command in a bot private chat and request
 * jumps to the command handler bypassing ChatRulesMiddleware
 */
class ExistedAdminCommandInPrivateChatTest extends TestCase
{
    use RefreshDatabase;

    public function testAdminAttachedToMultipleChatsReturnsSelectChatButtons()
    {
        // Prepare an admin in database with multiple chats
        // values are not shown during use RefreshDatabase trait
        (new SimpleSeeder())
            ->run(1, 4);

        $admin = Admin::first();
        $data = $this->getTextMessageModelData();
        unset($data["message"]["chat"]["title"]);
        $data["message"]["from"]["id"] = $admin->admin_id;
        $data["message"]["chat"]["type"] = 'private';
        $data["message"]["chat"]["id"] = $admin->admin_id;
        $data["message"]["text"] = '/moderation_settings';

        $this->postJson('api/webhook', $data);

    }
}

