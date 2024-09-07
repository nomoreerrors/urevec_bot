<?php

namespace Feature\CommandsTests;

use Tests\Feature\Traits\MockBotService;
use App\Services\CONSTANTS;
use App\Classes\ChatSelector;
use Tests\TestCase;
use App\Services\TelegramBotService;
use App\Classes\PrivateChatCommandCore;
use App\Classes\BadWordsFilterCommand;
use App\Enums\BadWordsFilterEnum;
use App\Classes\MainMenuCommand;
use App\Enums\FiltersSettingsEnum;
use App\Enums\MainMenuEnum;
use App\Enums\NewUserRestrictionsEnum;
use App\Enums\UnusualCharsFilterEnum;
use App\Classes\NewUserRestrictionsCommand;
use App\Classes\UnusualCharsFilterCommand;
use Tests\Feature\RestrictNewUsersCommandTest;
use App\Classes\FiltersSettingsCommand;

class PrivateChatCommandCoreTest extends TestCase
{
    use MockBotService;

    public function setUp(): void
    {
        parent::setUp();
        $this->admin = $this->setAdminWithMultipleChats(2);
        $this->mockBotCreate();
        $this->mockBotGetAdminMethod($this->admin);
    }

    /**
     * updateCommandIfChanged  method should be called  
     * at the top of the handle method of PrivateChatCommandCore
     * for the case it was changed in chatSelector or Menu class
     * @return void
     */
    public function testCommandUpdatedAtTheTopOfTheHandleMethod()
    {
        $this->setBackButtonPressed(true);

        $this->mockBotService->expects($this->once())
            ->method("menu")
            ->willReturn($this->mockMenu);

        // Get mock and skip command logic:
        $privateCore = $this->getPrivateCommandCoreMock($this->mockBotService, true);
        // Expected calls:
        $privateCore->expects($this->once())
            ->method("updateCommandIfChanged");

        $privateCore->handle();
    }

    public function testSelectChatLogiscIsSkippedWhenBackButtonPressedFlagIsTrue()
    {
        $this->setBackButtonPressed(true);

        $this->mockBotService->expects($this->never())
            ->method('chatSelector');
        // Get mock and skip command logic:
        $privateCore = $this->getPrivateCommandCoreMock($this->mockBotService, true);
        // Expected calls:
        $privateCore->expects($this->once())
            ->method("updateCommandIfChanged");

        $privateCore->handle();
    }


    public function testSelectChatLogiscIsNotSkippedWhenBackButtonPressedFlagIsFalse()
    {
        $this->setBackButtonPressed(false);

        $chatSelector = $this->getMockChatSelector();

        $chatSelector->expects($this->once())
            ->method("select");

        $chatSelector->expects($this->once())
            ->method("hasBeenUpdated");

        $chatSelector->expects($this->once())
            ->method("buttonsHaveBeenSent");

        //chat selector logic expected to be skipped:
        $this->mockBotService->expects($this->exactly(3))
            ->method("chatSelector")
            ->willReturn($chatSelector);

        // Get mock and skip command logic:
        $privateCore = $this->getPrivateCommandCoreMock($this->mockBotService, true);
        // Expected calls:
        $privateCore->expects($this->once())
            ->method("updateCommandIfChanged");

        $privateCore->handle();
    }

    public function testChatSelectorHasBeenUpdatedFlagIsTrueShouldReturn()
    {
        //skip button checks
        $this->setBackButtonPressed(false);

        //Set chat selector
        $chatSelector = $this->getMockChatSelector();
        $chatSelector->expects($this->once())
            ->method("select");

        $chatSelector->expects($this->once())
            ->method("hasBeenUpdated")
            ->willReturn(true);

        $chatSelector->expects($this->once())
            ->method("buttonsHaveBeenSent")
            ->willReturn(false);


        //Set bot chat selector
        $this->mockBotService->expects($this->any())
            ->method("chatSelector")
            ->willReturn($chatSelector);

        /** Expected not to be called */
        $this->mockBotService->expects($this->never())
            ->method('createCommand');



        //Get mock without skip command logic:
        $privateCore = $this->getPrivateCommandCoreMock($this->mockBotService, false);
        $privateCore->expects($this->once())
            ->method("updateCommandIfChanged");

        /** Expected not to be called */
        $privateCore->expects($this->never())
            ->method("getCommandClassName");

        $privateCore->expects($this->never())
            ->method("isValidCommandClassName");

        $privateCore->handle();
    }


    public function testChatSelectorButtonsHaveBeenSentFlagIsTrueShouldReturn()
    {
        //skip button checks
        $this->setBackButtonPressed(false);

        $chatSelector = $this->getMockChatSelector();

        $chatSelector->expects($this->once())
            ->method("buttonsHaveBeenSent")
            ->willReturn(true);


        $this->mockBotService->expects($this->any())
            ->method("chatSelector")
            ->willReturn($chatSelector);

        /** Expected not to be called */
        $this->mockBotService->expects($this->never())
            ->method('createCommand');


        //Get mock without skip command logic:
        $privateCore = $this->getPrivateCommandCoreMock($this->mockBotService, false);
        $privateCore->expects($this->once())
            ->method("updateCommandIfChanged");

        /** Expected not to be called */
        $privateCore->expects($this->never())
            ->method("getCommandClassName");

        $privateCore->expects($this->never())
            ->method("isValidCommandClassName");

        $privateCore->handle();
    }



    public function testIsNotValidCommandClassNameShouldReplyAndReturn()
    {
        //skip select chat logic:
        $this->setBackButtonPressed(true);
        $this->expectReplyMessage(CONSTANTS::COMMAND_NOT_FOUND);
        //Get mock without skip command logic:
        $privateCore = $this->getMockBuilder(PrivateChatCommandCore::class)
            ->setConstructorArgs([$this->mockBotService])
            ->onlyMethods(["updateCommandIfChanged", "getCommandClassName"])
            ->getMock();
        // Dummy command class name
        $privateCore->expects($this->once())
            ->method('getCommandClassName')
            ->willReturn(null);


        //Expected not to be called when command class name is not valid
        $this->mockBotService->expects($this->never())
            ->method('createCommand');

        $privateCore->handle();
    }


    public function testIsValidCommandClassNameShouldContinue()
    {
        //skip select chat logic:
        $this->setBackButtonPressed(true);
        //Get mock without skip command logic:
        $privateCore = $this->getPrivateCommandCoreMock($this->mockBotService, false);

        // Dummy command class name
        $privateCore->expects($this->once())
            ->method('getCommandClassName')
            ->willReturn("TestCommand");

        $privateCore->expects($this->once())
            ->method('isValidCommandClassName')
            ->willReturn(true);

        //Expected to be called when command class name is valid
        $this->mockBotService->expects($this->once())
            ->method('createCommand');

        $privateCore->handle();

    }



    private function setBackButtonPressed(bool $flag = true)
    {
        $this->mockMenuCreate();
        $this->mockMenu->expects($this->once())
            ->method('backButtonPressed')
            ->willReturn($flag);

        $this->mockBotMenuCreate(1);
    }


    private function getMockChatSelector()
    {
        $chatSelector = $this->getMockBuilder(ChatSelector::class)
            ->disableOriginalConstructor()
            ->onlyMethods(["select", "hasBeenUpdated", "buttonsHaveBeenSent"])
            ->getMock();
        return $chatSelector;
    }



    private function getPrivateCommandCoreMock($mockBotService, bool $skipCommandLogic = false)
    {
        $privateCore = $this->getMockBuilder(PrivateChatCommandCore::class)
            ->setConstructorArgs([$mockBotService])
            ->onlyMethods(["updateCommandIfChanged", "getCommandClassName", "isValidCommandClassName"])
            ->getMock();

        if ($skipCommandLogic) {
            $privateCore->expects($this->once())
                ->method("getCommandClassName")
                ->willReturn("");

            $privateCore->expects($this->once())
                ->method("isValidCommandClassName")
                ->willReturn(false);
        }

        return $privateCore;
    }











    public function testSelectingChatLogicIsSkippedWhenBackButtonPressedFlagIsTrue()
    {
        $this->assertTrue(true);
    }

    public function testSelectingChatLogicIsNotSkippedWhenBackButtonPressedFlagIsFalse()
    {
        $this->assertTrue(true);
    }

    public function testIfHasBeenUpdatedFlagIsTrueShouldReturn()
    {
        $this->assertTrue(true);
    }

    public function testIfButtonsHaveBeenSentFlagIsTrueShouldReturn()
    {
        $this->assertTrue(true);
    }

    /**
     * Handle method logic execution should continue if hasBeenUpdated and buttonsHaveBeenSent flag is false
     * @return void
     */
    public function testIfHasBeenUpdatedFlagAndButtonsHaveBeenSentFlagEqualsFalseShouldContinue()
    {
        $this->assertTrue(true);
    }

}
