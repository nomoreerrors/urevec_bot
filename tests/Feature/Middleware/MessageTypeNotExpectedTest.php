<?php

namespace Tests\Feature\Middleware;

use App\Services\CONSTANTS;
use Symfony\Component\HttpFoundation\Response;
use App\Exceptions\UnexpectedRequestException;
use App\Services\TelegramMiddlewareService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class MessageTypeNotExpectedTest extends TestCase
{

    /**
     * A basic feature test example.
     */
    public function test_not_expected_request_object_type_stopped_at_middleware_and_response_ok(): void
    {
        $data = $this->unknownObject;

        $response = $this->post('api/webhook', $data);
        
        $response->assertStatus(200);
        $response->assertContent(CONSTANTS::UNKNOWN_OBJECT_TYPE);
    }
}
