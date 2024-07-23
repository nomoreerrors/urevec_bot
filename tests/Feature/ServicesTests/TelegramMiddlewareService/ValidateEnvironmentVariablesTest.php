<?php

namespace Tests\Feature\Middleware;

use App\Services\CONSTANTS;
use Symfony\Component\HttpFoundation\Response;
use App\Exceptions\UnexpectedRequestException;
use App\Exceptions\EnvironmentVariablesException;
use App\Services\TelegramMiddlewareService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

/**
 * @method checkIfObjectTypeExpected()
 *  @package Tests\Feature\ServicesTests\TelegramMiddlewareService
 */
class ValidateEnvironmentVariablesTest extends TestCase
{
    public function testValidateEnvironmentVariablesThrowsExceptionWhenVariablesEmpty()
    {
        $data = $this->getMessageModelData();
        putenv("test1=");
        putenv("test2=");

        $this->expectException(EnvironmentVariablesException::class);
        $this->expectExceptionMessage(CONSTANTS::EMPTY_ENVIRONMENT_VARIABLES);

        $telegramMiddlewareService = new TelegramMiddlewareService($data);
        $telegramMiddlewareService->validateEnvironmentVariables(env("test1"), env("test2"));
    }


    public function testValidateEnvironmentVariablesPassesWhenVariablesNotEmpty()
    {
        $data = $this->getMessageModelData();
        // Mock the environment variables
        putenv('test1=1');
        putenv('test2=2');

        $telegramMiddlewareService = new TelegramMiddlewareService($data);
        $this->assertNull($telegramMiddlewareService->validateEnvironmentVariables(env('test1'), env('test2')));
    }
}