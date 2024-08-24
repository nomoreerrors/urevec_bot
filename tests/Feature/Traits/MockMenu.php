<?php

namespace Tests\Feature\Traits;

use App\Classes\Menu;

/**
 * Mock private chat menu 
 * Menu::class 
 */
trait MockMenu
{
    use BaseMockTrait;

    protected $mockMenu;

    protected function mockMenuCreate()
    {
        $this->mockMenu = $this->createMock(Menu::class);
    }
}