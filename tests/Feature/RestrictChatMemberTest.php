<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Services\TelegramBotService;
use App\Models\TelegramMessageModel;




class RestrictChatMemberTest extends TestCase
{
    public function test_restrict_user_by_id_user_found_return_true(): void
    {


        $message = new TelegramMessageModel($this->testObjects["3"]);
        $service = new TelegramBotService($message);

        $this->assertTrue($service->restrictChatMember());
    }


    public function test_restrict_user_by_id_user_not_found_return_false(): void
    {
        $message = new TelegramMessageModel($this->testObjects["19"]);
        $service = new TelegramBotService($message);

        $this->assertFalse($service->restrictChatMember());
    }
}
