<?php

namespace Tests\Feature\Middleware;

use App\Exceptions\UnknownChatException;
use App\Services\CONSTANTS;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ChatNotAllowedBlockTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_if_chat_id_not_allowed_request_stopped_at_middleware(): void
    {
        $data= $this->testObjects[0]; 
        $response = $this->post('api/webhook', $data);

        $response->assertStatus(200);
        $response->assertContent(CONSTANTS::REQUEST_CHAT_ID_NOT_ALLOWED);

    }
}
