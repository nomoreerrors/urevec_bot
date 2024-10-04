<?php

namespace Tests\Feature;

use App\Models\TelegramRequestModelBuilder;
use App\Models\Chat;
use App\Exceptions\BaseTelegramBotException;
use App\Services\CONSTANTS;
use App\Services\TelegramBotService;
use App\Models\StatusUpdates\InvitedUserUpdateModel;
use App\Models\StatusUpdates\NewMemberJoinUpdateModel;
use App\Models\StatusUpdates\StatusUpdateModel;
use App\Services\ChatRulesService;
use Database\Seeders\SimpleSeeder;
use Tests\TestCase;

/**
 * Test BlockNewVisitorTest method of ChatRulesService
 */
class BlockNewVisitorTest extends TestCase
{

    public function setUp(): void
    {
        parent::setUp();
    }

}
