<?php

namespace Tests\Feature;

use App\Services\CONSTANTS;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Request;
use Tests\TestCase;

class IpNotAllowedBlockTest extends TestCase
{
    /**
     * admin id must be false and chat id allowed
     */
    public function test_ip_not_allowed_request_stopped_at_middleware_and_response_status_ok(): void
    {
        $data = $this->testObjects[4];

        $response = $this->call('POST', 'api/webhook', $data , [], [], ['REMOTE_ADDR' => '12.22.0.0']); 
        
        $response->assertStatus(200);
        $response->assertContent(CONSTANTS::REQUEST_IP_NOT_ALLOWED);

    }
}
