<?php

namespace Feature\Containertest;

use App\Models\Chat;
use App\Classes\PrivateChatCommandRegister;
use App\Classes\ChatBuilder;
use App\Exceptions\BaseTelegramBotException;
use App\Classes\ChatSelector;
use App\Services\TelegramBotService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FeatureCreateNewChatTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that the method PrivateChatCommandRegister::setMyCommands is called in ChatBuilder class
     * when a new chat is created 
     * @return void
     */
    public function testSetMyCommandsMethodIsCalled()
    {
        // To create request model without an API call
        $this->fakeResponseWithAdminsIds(1111, 5555);

        $privateChatCommandRegister = $this->getMockBuilder(PrivateChatCommandRegister::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['setMyCommands'])
            ->getMock();


        //Expected to be called once for each admin
        $privateChatCommandRegister->expects($this->exactly(2))->method('setMyCommands')
            ->will($this->returnCallback(function () {
                return;
            }));


        $botService = $this->getMockBuilder(TelegramBotService::class)
            ->onlyMethods(['privateChatCommandRegister'])
            ->getMock();

        $botService->method('privateChatCommandRegister')->willReturn($privateChatCommandRegister);

        $data = $this->getMessageModelData();
        // Put mocked bot service in the container so it is available in TelegramApiMiddleware
        app()->instance(TelegramBotService::class, $botService);

        $this->postJson('/api/webhook', $data);
    }


    /**
     * Test only that unexisted relationships are created
     * no matter that setMyCommands exception throws or not
     * @return void
     */
    public function testRelationshipsCreated()
    {
        // To create request model without an API call
        $this->fakeResponseWithAdminsIds(1111, 5555);

        $data = $this->getMessageModelData();
        $this->postJson('/api/webhook', $data);
        $chat = Chat::with('badWordsFilter', 'linksFilter', 'newUserRestrictions')->first();
        $this->assertNotEmpty($chat->badWordsFilter()->first());
        $this->assertNotEmpty($chat->linksFilter()->first());
        $this->assertNotEmpty($chat->newUserRestrictions()->first());
    }
}
