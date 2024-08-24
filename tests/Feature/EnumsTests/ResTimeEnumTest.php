<?php

namespace Tests;

use App\Enums\NewUserRestrictionsEnum;
use App\Models\ForwardMessageModel;
use App\Enums\UnusualCharsFilterEnum;
use App\Enums\ResTime;
use App\Models\Chat;
use App\Models\UnusualCharsFilter;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use App\Enums\BadWordsFilterEnum;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;


class ResTimeEnumTest extends BaseTestCase
{
    public function testGetTime()
    {
        $time = ResTime::getTime(NewUserRestrictionsEnum::SET_TIME_TWO_HOURS);
        $this->assertEquals(1, $time);

        $time = ResTime::getTime(BadWordsFilterEnum::SET_TIME_MONTH);
        $this->assertEquals(4, $time);

        $time = ResTime::getTime(UnusualCharsFilterEnum::SET_TIME_WEEK);
        $this->assertEquals(3, $time);
    }
}



