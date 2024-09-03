<?php

namespace Feature\ButtonsTitles;

use Tests\TestCase;
use App\Classes\ButtonsTitles;
use App\Enums\BadWordsFilterEnum;


class GetEditRestrictionsTitlesTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->admin = $this->setAdminWithMultipleChats(2);
    }


    public function testGetEditRestrictionsTitles()
    {
        $filter = $this->admin->chats()->first()->badWordsFilter()->first();

        $titles = (new ButtonsTitles($filter, BadWordsFilterEnum::class))->getEditRestrictionsTitles();
        $this->assertIsArray($titles);
    }
}
