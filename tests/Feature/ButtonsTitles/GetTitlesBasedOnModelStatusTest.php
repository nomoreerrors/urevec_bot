<?php

namespace Feature\ButtonsTitles;

use App\Exceptions\EmptyTitlesArrayException;
use App\Enums\CommandEnums\BadWordsFilterEnum;
use App\Exceptions\TableColumnNotExistsException;
use Tests\TestCase;
use Illuminate\Database\Eloquent\Model;
use App\Classes\ButtonsTitles;


enum FakeEnum: string
{
    case FAKE_DISABLE = 'abracadabra';
    case FAKE_ENABLE = 'bracadabra';
    case FAKE_SOME_TEXT = 'some text';
    case FAKE_SOME_TEXT_TOO = 'some text too';
}


class GetTitlesBasedOnModelStatusTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->admin = $this->setAdminWithMultipleChats(2);
    }

    /**
     * Cases with postfix _DISABLE and _ENABLE should be included
     * and based on database status
     * @return void
     */
    public function testAssertThatAllToggledColumnsTitlesBasedOnDatabaseStatus()
    {
        $filter = $this->admin->chats()->first()->badWordsFilter()->first();

        $enableDisable = $filter->enabled === 1 ?
            BadWordsFilterEnum::ENABLED_DISABLE->value :
            BadWordsFilterEnum::ENABLED_ENABLE->value;

        $deleteMessage = $filter->delete_message === 1 ?
            BadWordsFilterEnum::DELETE_MESSAGE_DISABLE->value :
            BadWordsFilterEnum::DELETE_MESSAGE_ENABLE->value;

        $titles = (new ButtonsTitles($filter, BadWordsFilterEnum::class))->getBadWordsFilterTitles();


        $this->assertContains($enableDisable, $titles);
        $this->assertNotContains($enableDisable === BadWordsFilterEnum::ENABLED_ENABLE->value ?
            BadWordsFilterEnum::ENABLED_DISABLE->value :
            BadWordsFilterEnum::ENABLED_ENABLE->value, $titles);

        $this->assertContains($deleteMessage, $titles);
        $this->assertNotContains($deleteMessage === BadWordsFilterEnum::DELETE_MESSAGE_ENABLE->value ?
            BadWordsFilterEnum::DELETE_MESSAGE_DISABLE->value :
            BadWordsFilterEnum::DELETE_MESSAGE_ENABLE->value, $titles);
    }

    /**
     * Cases without "ENABLE" or "DISABLE" postfix should be included too
     * which are the buttons that cannot be toggled  
     * @return void
     */
    public function testAssertThatTitlesWithoutPostfixAreIncludedToTitlesArray()
    {
        $filter = $this->admin->chats()->first()->badWordsFilter()->first();

        $titles = (new ButtonsTitles($filter, BadWordsFilterEnum::class))->getBadWordsFilterTitles();

        $this->assertContains(BadWordsFilterEnum::DELETE_WORDS->value, $titles);
        $this->assertContains(BadWordsFilterEnum::EDIT_RESTRICTIONS->value, $titles);
        $this->assertContains(BadWordsFilterEnum::ADD_WORDS->value, $titles);
        $this->assertContains(BadWordsFilterEnum::GET_WORDS->value, $titles);
    }


    public function testIfColumnDoesNotExistThrowsException()
    {
        $this->expectException(TableColumnNotExistsException::class);

        $modelMock = $this->createMock(Model::class);
        $modelMock->expects($this->any())
            ->method('getTable')
            ->willReturn('non_existent_table');

        $buttonsTitles = new ButtonsTitles($modelMock, FakeEnum::class);

        $buttonsTitles->getTitlesBasedOnModelStatus([FakeEnum::FAKE_DISABLE, FakeEnum::FAKE_ENABLE]);
    }


    public function testNoEnabledAndDisabledTogether(): void
    {
        $filter = $this->admin->chats()->first()->badWordsFilter()->first();
        $cases = [BadWordsFilterEnum::ENABLED_DISABLE, BadWordsFilterEnum::ENABLED_ENABLE, BadWordsFilterEnum::DELETE_MESSAGE_DISABLE, BadWordsFilterEnum::DELETE_MESSAGE_ENABLE];
        $titles = (new ButtonsTitles($filter, BadWordsFilterEnum::class))->getTitlesBasedOnModelStatus($cases);
        $this->assertCount(2, $titles);

        $this->assertNoEnabledAndDisabledTogglesAreTogether($titles, BadWordsFilterEnum::ENABLED_DISABLE, BadWordsFilterEnum::ENABLED_ENABLE);
        $this->assertNoEnabledAndDisabledTogglesAreTogether($titles, BadWordsFilterEnum::DELETE_MESSAGE_DISABLE, BadWordsFilterEnum::DELETE_MESSAGE_ENABLE);
    }

    public function testIfEmptyArgumentsArrayOrEmptyResultArrayThrowsException()
    {
        $filter = $this->admin->chats()->first()->badWordsFilter()->first();
        $this->expectException(EmptyTitlesArrayException::class);
        $cases = [];
        (new ButtonsTitles($filter, BadWordsFilterEnum::class))->getTitlesBasedOnModelStatus($cases);
    }


    public function testNoDuplicates()
    {
        $filter = $this->admin->chats()->first()->badWordsFilter()->first();
        $cases = [BadWordsFilterEnum::ENABLED_DISABLE, BadWordsFilterEnum::ENABLED_DISABLE, BadWordsFilterEnum::DELETE_MESSAGE_DISABLE, BadWordsFilterEnum::DELETE_MESSAGE_DISABLE];
        $result = (new ButtonsTitles($filter, BadWordsFilterEnum::class))->getTitlesBasedOnModelStatus($cases);
        $this->assertCount(2, $result);
    }


    private function assertNoEnabledAndDisabledTogglesAreTogether(array $titles, BadWordsFilterEnum $enabled, BadWordsFilterEnum $disabled): void
    {
        if (in_array($enabled->value, $titles)) {
            $this->assertNotContains($disabled->value, $titles);
        }

        if (in_array($disabled->value, $titles)) {
            $this->assertNotContains($enabled->value, $titles);
        }
    }
}

