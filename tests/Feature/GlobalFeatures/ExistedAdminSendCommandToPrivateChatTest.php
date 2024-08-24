<?php

namespace Tests\Feature\Middleware;

use App\Models\Chat;
use Illuminate\Support\Facades\Cache;
use App\Services\TelegramBotService;
use Database\Seeders\SimpleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Services\ChatRulesService;
use App\Models\TelegramRequestModelBuilder;
use Illuminate\Support\Facades\Http;
use App\Services\CONSTANTS;
use App\Enums\ModerationSettingsEnum;
use App\Models\Admin;
use Tests\TestCase;
use Illuminate\Support\Facades\Config;

/**
 * Test where an admin that is existed in database writes command in a bot private chat  request
 * jumps to the command handler bypassing ChatRulesMiddleware
 */
class ExistedAdminSendCommandToPrivateChatTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->fakeSendMessageSucceedResponse();
        $this->clearTestLogFile();
        (new SimpleSeeder())->run();
        $this->admin = Admin::first();
        $this->data = $this->getPrivateChatMessage($this->admin->admin_id);
        // Prepare an admin in database with multiple chats
        // values won't shown during the RefreshDatabase trait is been used
    }

    /**
     * Testcase where chat is not selected and commands not executed untill the chat is selected
     * @return void
     */
    public function testAdminAttachedToMultipleChatsAndChatNotSelectedRepliesWithSelectChatButtons()
    {
        $this->deleteLastCommandFromCache($this->admin->admin_id);
        $this->setCommand(ModerationSettingsEnum::SETTINGS->value);

        $this->postJson('api/webhook', $this->data);
        $titles = $this->admin->chats->pluck("chat_title")->toArray();
        //Assert that all expected buttons titles were sent  and write in log file inside sendMessages() method
        $this->assertButtonsWereSent($titles);
        //Assert that last command was set in cache so that it can be used after the chat is selected
        $this->assertEquals(ModerationSettingsEnum::SETTINGS->value, $this->getLastCommandFromCache($this->admin->admin_id));
        $this->clearTestLogFile();
    }
}


