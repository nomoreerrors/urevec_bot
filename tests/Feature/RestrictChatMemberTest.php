<?php

namespace Tests\Feature;

use App\Models\BaseTelegramRequestModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Services\TelegramBotService;
use App\Models\TelegramMessageModel;




class RestrictChatMemberTest extends TestCase
{
    /**
     * $this->testObjects["4"] - содержит id 100% существующего бота в группе
     * @return void
     */
    public function test_restrict_user_by_id_return_true(): void
    {


        $message = (new BaseTelegramRequestModel($this->testObjects["4"]))->create();

        $service = new TelegramBotService($message);


        $this->assertTrue($service->restrictChatMember());
    }


    public function test_restrict_user_by_id_user_not_found_return_false(): void
    {
        $message = (new BaseTelegramRequestModel($this->testObjects["19"]))->create();
        $service = new TelegramBotService($message);

        $this->assertFalse($service->restrictChatMember());
    }
}
