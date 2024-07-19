<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Services\CONSTANTS;
use Tests\TestCase;
use Illuminate\Http\Request;

class BlockUnknownObjectTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function testUnknownObjectResponseStatusCodeIsOk(): void
    {
        $unknownObject = $this->getUnknownObject();
        $response = $this->postJson('/api/webhook', $unknownObject);

        $response->assertOk();
        $response->assertSee(CONSTANTS::UNKNOWN_OBJECT_TYPE);
    }

}
