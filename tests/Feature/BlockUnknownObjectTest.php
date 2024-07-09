<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Http\Request;

class BlockUnknownObjectTest extends TestCase
{
    /**
     * A basic feature test example.
     */

    private array $unknownObject = [
        "update_id" => 11122233,
        "unknown_type" =>
        ["chat" =>
        [
            "id" => -1002222230714,
            "title" => "Testylvania",
            "type" => "supergroup"
        ]]
    ];

    public function test_unknown_object_exit_code_response_status_500(): void
    {


        $response = $this->post('/api/webhook', $this->unknownObject);


        $response->assertStatus(500);
    }
}
