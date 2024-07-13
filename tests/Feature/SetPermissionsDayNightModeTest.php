<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;


/**
 * Включение и выключение прав днем и ночью
 */
class SetPermissionsDayNightModeTest extends TestCase
{

    public function testSettingNightModePermissionsReturnsTrue(): void
    {
        $this->assertTrue($this->chatPermissions->setPermissionsToNightMode());
    }


    public function test_set_light_mode_permissions_returns_true(): void
    {
        $this->assertTrue($this->chatPermissions->setPermissionsToLightMode());
    }


    public function testSwitchingPermissionsBetweenNightAndLightModeWorks(): void
    {
        $nightModeResponse = $this->post('/setChatPermissions', [
            'token' => env('CRON_TOKEN'),
            'mode' => 'night_mode',
        ]);

        $this->assertEquals('night_mode', $nightModeResponse->headers->get('mode'));

        $lightModeResponse = $this->post('/setChatPermissions', [
            'token' => env('CRON_TOKEN'),
            'mode' => 'light_mode',
        ]);

        $this->assertEquals('light_mode', $lightModeResponse->headers->get('mode'));
    }
}
