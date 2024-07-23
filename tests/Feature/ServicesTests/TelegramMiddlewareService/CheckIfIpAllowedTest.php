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
 * @method checkiifIpAllowed()
 *  @package Tests\Feature\ServicesTests\TelegramMiddlewareService
 */
class CheckIfIpAllowedTest extends TestCase
{
    public function test_allowed_ip_is_accepted(): void
    {
        $data = $this->getMessageModelData();
        $ip = '37.19.77.114'; // Assuming this IP is in the allowed list
        $this->assertNull((new TelegramMiddlewareService($data))->checkIfIpAllowed($ip));
    }

    public function test_disallowed_ip_is_rejected(): void
    {
        $data = $this->getMessageModelData();
        $ip = '10.0.0.1'; // Assuming this IP is not in the allowed list
        $this->expectException(UnknownIpAddressException::class);
        (new TelegramMiddlewareService($data))->checkIfIpAllowed($ip);
    }

}
