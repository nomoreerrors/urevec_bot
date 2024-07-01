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

    public function test_set_night_mode_permissions_return_true(): void
    {
        $result = $this->chatPermissions->setPermissionsToNightMode();
        $this->assertTrue($result);
    }


    public function test_set_light_mode_permissions_return_true(): void
    {
        $result = $this->chatPermissions->setPermissionsToLightMode();
        $this->assertTrue($result);
    }
}
