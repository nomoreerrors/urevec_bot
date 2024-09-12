<?php

namespace Tests\Feature\Middleware;

use App\Services\CONSTANTS;
use Symfony\Component\HttpFoundation\Response;
use App\Exceptions\UnexpectedRequestException;
use App\Services\TelegramMiddlewareService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RequestNotExpectedTest extends TestCase
{
    /**
     * Test that the middleware stops the request if the object type is not expected.
     */
    public function test_middleware_stops_the_request_if_object_type_is_unknown(): void
    {
        $requestData = $this->getUnknownObject();
        $response = $this->postJson('/api/webhook', $requestData);

        $response->assertOk();
        // $response->assertSee(CONSTANTS::UNKNOWN_OBJECT_TYPE);

        $this->expectException(UnexpectedRequestException::class);
        (new TelegramMiddlewareService($requestData))->checkIfObjectTypeExpected();
    }
}
