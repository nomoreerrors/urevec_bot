<?php

namespace Tests\Feature\Middleware;

use App\Services\CONSTANTS;
use Symfony\Component\HttpFoundation\Response;
use App\Exceptions\UnexpectedRequestException;
use App\Services\TelegramMiddlewareService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

/**
 * @method checkIfObjectTypeExpected()
 *  @package Tests\Feature\ServicesTests\TelegramMiddlewareService
 */
class CheckIfObjectTypeExpectedTest extends TestCase
{
    public function test_object_type_is_not_expected_throws_exception(): void
    {
        $requestData = $this->getUnknownObject();
        $this->expectException(UnexpectedRequestException::class);
        (new TelegramMiddlewareService($requestData))->checkIfObjectTypeExpected();
    }


    /**
     * checkIfObjectTypeExpected method test 
     * @return void
     */
    public function test_object_type_is_expected_return_null(): void
    {
        $requestData = $this->getMessageModelData();
        $this->assertNull((new TelegramMiddlewareService($requestData))->checkIfObjectTypeExpected());
    }
}
