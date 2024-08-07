<?php

namespace Tests\Feature;

use App\Exceptions\BaseTelegramBotException;
use App\Enums\ResTime;
use App\Services\CONSTANTS;
use App\Models\TelegramRequestModelBuilder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Services\TelegramBotService;
use Illuminate\Support\Facades\DB;

class RestrictChatMemberTest extends TestCase
{
    use RefreshDatabase;

    protected $requestData;

    protected $requestModel;

    protected TelegramBotService $botService;

    protected int $updateId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->requestData = $this->getMessageModelData();
        $this->updateId = $this->requestData["update_id"];

    }
    /**
     * Test that restricting a user by ID returns true.
     * @return void
     */
    public function testRestrictUserByIdReturnsTrue()
    {
        $this->clearTestLogFile();
        $this->prepareDependencies();
        $this->assertTrue($this->botService->restrictChatMember());
    }


    /**
     * Request to real Telegram API should be dispatched to queue when restricting user fails.
     * and exception should be thrown.
     * @return void
     */
    public function testExceptionIsThrownAndRequestDispatchedToQueueWhenRestrictingUserFails(): void
    {
        $this->requestData['message']['from']['id'] = $this->getInvalidUserId();
        $this->prepareDependencies();

        $this->expectException(BaseTelegramBotException::class);
        $this->expectExceptionMessage(CONSTANTS::RESTRICT_MEMBER_FAILED);

        $this->botService->restrictChatMember();

        $jobInQueue = DB::table('jobs')
            ->where('payload', 'like', "%{$this->updateId}%")
            ->first();

        $this->assertNotNull($jobInQueue);
    }

    public function testTimeArgumentPassedRestrictAccordingToTime()
    {
        $this->prepareDependencies();
        $this->botService->restrictChatMember(ResTime::WEEK);
        $lol = $this->getTestLogFile();
        $this->assertStringContainsString(ResTime::WEEK->getHumanRedable(), $this->getTestLogFile());
        $this->clearTestLogFile();
    }

    /**
     * Test that the default time is taken from that was set in a TelegramBotService if an argument is not passed
     * @return void
     */
    public function testTimeArgumentNotPassedRestrictTimeIsTakenFromDatabase()
    {
        $this->prepareDependencies();
        $this->botService->restrictChatMember();
        $this->assertStringContainsString(ResTime::DAY->getHumanRedable(), $this->getTestLogFile());
        $this->clearTestLogFile();
    }

    public function prepareDependencies()
    {
        $this->requestModel = (new TelegramRequestModelBuilder($this->requestData))->create();
        $this->botService = new TelegramBotService($this->requestModel);
        $this->clearTestLogFile();
    }
}
