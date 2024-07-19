<?php

namespace Tests\Feature;

use App\Exceptions\TelegramModelException;
use App\Services\ChatSettingsService;
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
        $this->assertTrue(ChatSettingsService::setPermissionsToNightMode());
    }


    public function test_set_light_mode_permissions_returns_true(): void
    {
        $this->assertTrue(ChatSettingsService::setPermissionsToLightMode());
    }


    public function testSwitchingPermissionsBetweenNightAndLightModeWorks(): void
    {
        $nightModeResponse = $this->post('/setChatPermissions', [
            'token' => env('CRON_TOKEN'),
            'mode' => 'night_mode',
        ]);
        $nightModeResponse->assertOk();


        $lightModeResponse = $this->post('/setChatPermissions', [
            'token' => env('CRON_TOKEN'),
            'mode' => 'light_mode',
        ]);
        $lightModeResponse->assertOk();

        $this->assertThrows(fn() => ChatSettingsService::setNightLightMode([]), TelegramModelException::class);
    }
}
