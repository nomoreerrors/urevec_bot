<?php

namespace Tests\Feature;

use App\Services\CONSTANTS;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Request;
use Tests\TestCase;

class IpNotAllowedBlockTest extends TestCase
{
    public function test_ip_not_allowed_request_is_blocked_by_middleware(): void
    {
        $request = $this->getMessageModel()->getData();

        $response = $this->withServerVariables(['REMOTE_ADDR' => '12.22.0.0'])
            ->post('api/webhook', $request);

        $response->assertOk();
        $response->assertSee(CONSTANTS::REQUEST_IP_NOT_ALLOWED);
    }
}
