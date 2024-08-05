<?php

namespace Tests\Feature\Middleware;

use App\Models\Chat;
use Illuminate\Support\Facades\Cache;
use App\Models\ChatAdmins;
use App\Services\PrivateChatCommandService;
use Illuminate\Support\Facades\Storage;
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
use Illuminate\Support\Facades\Config;

/**
 * Test where an admin that is existed in database writes command in a bot private chat  request
 * jumps to the command handler bypassing ChatRulesMiddleware
 */
class ExistedAdminSendCommandToPrivateChatTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Testcase where chat is not selected and commands not executed untill the chat is selected
     * @return void
     */
    public function testAdminAttachedToMultipleChatsAndChatNotSelectedRepliesWithSelectChatButtons()
    {
        // Prepare an admin in database with multiple chats
        // values are not shown during the RefreshDatabase trait is been used
        (new SimpleSeeder())
            ->run(1, 4);
        // Do not forget to use --env=testing flag while running test
        Http::fake([
            env('TELEGRAM_API_URL') . env('TELEGRAM_API_TOKEN') . "/sendMessage" => Http::response([
                'ok' => true
            ])
        ]);

        $admin = Admin::first();
        $data = $this->getTextMessageModelData();
        $command = "/moderation_settings";
        // Private messages have no chat title
        unset($data["message"]["chat"]["title"]);
        $data["message"]["from"]["id"] = $admin->admin_id;
        $data["message"]["chat"]["id"] = $admin->admin_id;
        $data["message"]["chat"]["type"] = 'private';
        // Mock sending a  random command to our server and expect that it won't be executed because chat is not selected 
        $data["message"]["text"] = $command;

        $this->postJson('api/webhook', $data);
        //Make sure that there is no chat selected 
        $this->assertNull((new PrivateChatCommandService())->getSelectedChat());
        //Storage facade is not working in tests. I set a file path in .env.testing file and in logging.php file
        // so it stores the logs in that file only while testing
        $logContents = file_get_contents(storage_path('logs/testing.log'));

        $chatId = "\"chat_id\":{$admin->admin_id}";
        $selectChat = "\"Select chat\"";
        // Asserting that TelegramBotService::sendMessage() was called with the right parameters and response is ok
        // which means that select chat buttons were sent to the private chat
        $this->assertStringContainsString($chatId, $logContents);
        $this->assertStringContainsString($selectChat, $logContents);
        foreach ($admin->chats as $chat) {
            $this->assertStringContainsString($chat->chat_title, $logContents);
        }
        //Assert that last command was set in cache so that it can be used after the chat is selected
        $lastCommand = Cache::get(CONSTANTS::CACHE_LAST_COMMAND . $admin->admin_id);
        $this->assertEquals($command, $lastCommand);
        unlink(storage_path('logs/testing.log'));
    }
}


