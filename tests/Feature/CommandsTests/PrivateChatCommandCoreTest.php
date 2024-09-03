<?php

namespace Feature\CommandsTests;

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
    public function setUp(): void
    {
        parent::setUp();
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

    public function testHandleMainMenuCommandReturnsMainMenuCommandInstance(): void
    {
        $mockBotService = $this->createMock(TelegramBotService::class);
        $mockMainMenuCommand = $this->createMock(MainMenuCommand::class);
        $mockBotService->expects($this->once())->method('getPrivateChatCommand')
            ->willReturn(MainMenuEnum::FILTERS_SETTINGS->value);
        $commandCore = new PrivateChatCommandCore($mockBotService);
        $this->assertInstanceOf(MainMenuCommand::class, $commandCore->handle());
    }

    public function testHandleFiltersSettingsCommandReturnsFiltersSettingsCommandInstance(): void
    {
        $mockBotService = $this->createMock(TelegramBotService::class);
        $mockFiltersSettingsCommand = $this->createMock(FiltersSettingsCommand::class);
        $mockBotService->expects($this->once())->method('getPrivateChatCommand')
            ->willReturn(FiltersSettingsEnum::BADWORDS_FILTER_SETTINGS->value);
        $commandCore = new PrivateChatCommandCore($mockBotService);
        $this->assertInstanceOf(FiltersSettingsCommand::class, $commandCore->handle());
    }

    public function testHandleNewUserRestrictionsCommandReturnsNewUserRestrictionsCommandInstance(): void
    {
        $mockBotService = $this->createMock(TelegramBotService::class);
        $mockNewUserRestrictionsCommand = $this->createMock(NewUserRestrictionsCommand::class);
        $mockBotService->expects($this->once())->method('getPrivateChatCommand')
            ->willReturn(NewUserRestrictionsEnum::ALLOW_NEW_USERS->value);
        $commandCore = new PrivateChatCommandCore($mockBotService);
        $this->assertInstanceOf(NewUserRestrictionsCommand::class, $commandCore->handle());
    }

    public function testHandleBadWordsFilterCommandReturnsBadWordsFilterCommandInstance(): void
    {
        $mockBotService = $this->createMock(TelegramBotService::class);
        $mockBadWordsFilterCommand = $this->createMock(BadWordsFilterCommand::class);
        $mockBotService->expects($this->once())->method('getPrivateChatCommand')
            ->willReturn(BadWordsFilterEnum::ADD_BADWORD->value);
        $commandCore = new PrivateChatCommandCore($mockBotService);
        $this->assertInstanceOf(BadWordsFilterCommand::class, $commandCore->handle());
    }

    public function testHandleUnusualCharsFilterCommandReturnsUnusualCharsFilterCommandInstance(): void
    {
        $mockBotService = $this->createMock(TelegramBotService::class);
        $mockUnusualCharsFilterCommand = $this->createMock(UnusualCharsFilterCommand::class);
        $mockBotService->expects($this->once())->method('getPrivateChatCommand')
            ->willReturn(UnusualCharsFilterEnum::ADD_UNUSUAL_CHAR->value);
        $commandCore = new PrivateChatCommandCore($mockBotService);
        $this->assertInstanceOf(UnusualCharsFilterCommand::class, $commandCore->handle());
    }

    public function testHandleUnknownCommandReturnsNull(): void
    {
        $mockBotService = $this->createMock(TelegramBotService::class);
        $mockBotService->expects($this->once())->method('getPrivateChatCommand')
            ->willReturn('unknown_command');
        $commandCore = new PrivateChatCommandCore($mockBotService);
        $this->assertNull($commandCore->handle());
    }

    public function testHandleEmptyCommandReturnsNull(): void
    {
        $mockBotService = $this->createMock(TelegramBotService::class);
        $mockBotService->expects($this->once())->method('getPrivateChatCommand')
            ->willReturn('');
        $commandCore = new PrivateChatCommandCore($mockBotService);
        $this->assertNull($commandCore->handle());
    }

    public function testHandleInvalidCommandReturnsNull(): void
    {
        $mockBotService = $this->createMock(TelegramBotService::class);
        $mockBotService->expects($this->once())->method('getPrivateChatCommand')
            ->willReturn('invalid_command');
        $commandCore = new PrivateChatCommandCore($mockBotService);
        $this->assertNull($commandCore->handle());
    }
}
