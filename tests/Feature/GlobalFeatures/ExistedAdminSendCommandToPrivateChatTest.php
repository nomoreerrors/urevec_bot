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
use App\Enums\MainMenuCmd;
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

    protected TelegramBotService $botService;


    /**
     * Testcase where chat is not selected and commands not executed untill the chat is selected
     * @return void
     */
    public function testAdminAttachedToMultipleChatsAndChatNotSelectedRepliesWithSelectChatButtons()
    {
        $this->fakeSendMessageSucceedResponse();
        $this->clearTestLogFile();
        // Prepare an admin in database with multiple chats
        // values are not shown during the RefreshDatabase trait is been used
        (new SimpleSeeder())->run();

        $admin = Admin::first();
        $data = $this->getTextMessageModelData();
        // Private messages have no chat title
        unset($data["message"]["chat"]["title"]);
        $data["message"]["from"]["id"] = $admin->admin_id;
        $data["message"]["chat"]["id"] = $admin->admin_id;
        $data["message"]["chat"]["type"] = 'private';
        // Mock sending a  random command to our server and expect that it won't be executed because chat is not selected 
        $data["message"]["text"] = MainMenuCmd::MODERATION_SETTINGS->value;

        $this->postJson('api/webhook', $data);
        //Storage facade is not working in tests. I set a file path in .env.testing file and in logging.php file
        // so it stores the logs in that file only while testing
        $logContents = $this->getTestLogFile();
        //Assert that all expected buttons titles were sent  and write in log file inside sendMessages() method
        foreach ($admin->chats as $chat) {
            $this->assertStringContainsString($chat->chat_title, $logContents);
        }
        $this->assertStringNotContainsString(MainMenuCmd::MODERATION_SETTINGS->value, $logContents);
        //Assert that last command was set in cache so that it can be used after the chat is selected
        $lastCommand = Cache::get(CONSTANTS::CACHE_LAST_COMMAND . $admin->admin_id);
        $this->assertEquals(MainMenuCmd::MODERATION_SETTINGS->value, $lastCommand);
        $this->clearTestLogFile();
    }
}


