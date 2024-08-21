<?php

namespace Feature\Buttons;

use App\Classes\Buttons;
use Nette\ArgumentOutOfRangeException;
use Illuminate\Support\Facades\Http;
use App\Enums\ModerationSettingsEnum;
use PHPUnit\Framework\TestCase;

class ButtonsTest extends TestCase
{
    /**
     * @test
     */
    public function testCreateButtonsWithDefaultSettings()
    {
        $buttons = new Buttons();
        $titles = ['Button 1', 'Button 2', 'Button 3'];
        $result = $buttons->create($titles);

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertArrayHasKey('keyboard', $result);
        $this->assertCount(2, $result['keyboard']);
        $this->assertCount(2, $result['keyboard'][0]);
        $this->assertCount(1, $result['keyboard'][1]);
    }

    /**
     * @test
     */
    public function testCreateButtonsWithCustomSettings()
    {
        $buttons = new Buttons();
        $titles = ['Button 1', 'Button 2', 'Button 3'];
        $result = $buttons->create($titles, 3, true);

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertArrayHasKey('keyboard', $result);
        $this->assertCount(2, $result['keyboard']);
        $this->assertCount(3, $result['keyboard'][0]);
        $this->assertCount(1, $result['keyboard'][1]);
        $this->assertEquals(ModerationSettingsEnum::BACK->value, $result['keyboard'][1][0]['text']);
    }

    /**
     * @test
     */
    public function testCreateButtonsWithEmptyTitles()
    {
        $buttons = new Buttons();
        $titles = [];
        $this->expectException(ArgumentOutOfRangeException::class);
        $buttons->create($titles);
    }

    /**
     * @test
     */
    public function testCreateButtonsWithNonArrayTitles()
    {
        $buttons = new Buttons();
        $titles = 'Button 1';
        $this->expectException(\TypeError::class);
        $buttons->create($titles);
    }
}