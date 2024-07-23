<?php

namespace Tests\Feature\Middleware;

use App\Services\CONSTANTS;
use App\Exceptions\UnknownIpAddressException;
use Symfony\Component\HttpFoundation\Response;
use App\Exceptions\UnexpectedRequestException;
use App\Services\TelegramMiddlewareService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

/**
 * @method checkIfChatIdAllowed()
 *  @package Tests\Feature\ServicesTests\TelegramMiddlewareService
 */
class CheckIfChatIdAllowedTest extends TestCase
{
    public function testCheckIfChatIdAllowedThrowsExceptionIfChatIdNotAllowed()
    {
        $data = $this->getMessageModelData();
        $service = new TelegramMiddlewareService($data);

        $this->expectException(\App\Exceptions\UnknownChatException::class);
        $this->expectExceptionMessage(CONSTANTS::REQUEST_CHAT_ID_NOT_ALLOWED);

        $service->checkIfChatIdAllowed(12345);
    }

    public function testCheckIfChatIdAllowedReturnsNullIfChatIdAllowed()
    {
        $data = $this->getMessageModelData();
        $service = new TelegramMiddlewareService($data);


        $this->assertNull($service->checkIfChatIdAllowed(-1002222230714));
    }
}
