<?php

namespace Tests\Feature\Middleware;

use App\Exceptions\UnknownChatException;
use App\Http\Middleware\TelegramApiMiddleware;
use Illuminate\Support\Facades\Cache;
use App\Services\CONSTANTS;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;
use App\Services\TelegramMiddlewareService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Closure;
use Illuminate\Http\Request;

class RequestChatNotAllowedBlockTest extends TestCase
{
    /**
     * Test that a chat ID not allowed request stops at the middleware and returns the correct response.
     */
    public function test_chat_id_not_allowed_request_stops_at_middleware(): void
    {
        $messageData = $this->getMessageModelData();
        $messageData['message']['chat']['id'] = 111115555;

        $response = $this->post('api/webhook', $messageData);

        $response->assertStatus(200);
        $response->assertSee(CONSTANTS::REQUEST_CHAT_ID_NOT_ALLOWED);
    }
}
