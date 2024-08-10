<?php

namespace Tests;

use App\Enums\ResNewUsersCmd;
use App\Models\ForwardMessageModel;
use App\Enums\UnusualCharsFilterCmd;
use App\Enums\ResTime;
use App\Models\Chat;
use App\Models\UnusualCharsFilter;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use App\Enums\BadWordsFilterCmd;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;


class ResTimeEnumTest extends BaseTestCase
{
    public function testGetTime()
    {
        $time = ResTime::getTime(ResNewUsersCmd::SET_TIME_TWO_HOURS);
        $this->assertEquals(1, $time);

        $time = ResTime::getTime(BadWordsFilterCmd::SET_TIME_MONTH);
        $this->assertEquals(4, $time);

        $time = ResTime::getTime(UnusualCharsFilterCmd::SET_TIME_WEEK);
        $this->assertEquals(3, $time);
    }
}



