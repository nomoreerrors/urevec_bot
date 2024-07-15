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
     * Test that restricting a user by ID returns true.
     *
     * @return void
     */
    public function testRestrictUserByIdReturnsTrue()
    {
        $requestData = $this->getMessageModel()->getData();
        $requestModel = new BaseTelegramRequestModel($requestData);
        $requestModel->getModel();
        $service = new TelegramBotService($requestModel);

        $this->assertTrue($service->restrictChatMember());
    }


    public function testRestrictingNonExistentUserReturnsFalse(): void
    {
        $requestData = $this->getMessageModel()->getData();
        $requestData['message']['from']['id'] = 9999999;

        $requestModel = new BaseTelegramRequestModel($requestData);
        $requestModel->getModel();

        $service = new TelegramBotService($requestModel);

        $this->assertFalse($service->restrictChatMember());
    }
}
