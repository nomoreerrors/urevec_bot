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
     * Test that the middleware stops the request if the object type is not expected.
     */
    public function test_middleware_stops_request_for_unknown_object_type(): void
    {
        $unknownObject = $this->getUnknownObject();
        $response = $this->postJson('/api/webhook', $unknownObject);

        $response->assertOk();
        $response->assertSee(CONSTANTS::UNKNOWN_OBJECT_TYPE);
    }
}
