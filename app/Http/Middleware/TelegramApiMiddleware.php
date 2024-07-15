<?php

namespace App\Http\Middleware;

use App\Exceptions\EnvironmentVariablesException;
use App\Exceptions\TelegramModelException;
use App\Exceptions\UnexpectedRequestException;
use App\Jobs\FailedRequestJob;
use App\Services\BotErrorNotificationService;
use App\Services\TelegramMiddlewareService;
use Closure;
use Exception;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;
use App\Models\BaseTelegramRequestModel;
use App\Exceptions\UnknownChatException;
use App\Exceptions\UnknownIpAddressException;
use App\Models\UnknownObjectModel;
use Illuminate\Support\Facades\Storage;
use App\Services\CONSTANTS;

class TelegramApiMiddleware
{
    private bool $chatIdAllowed = false;

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $data = $request->all();

        try {

            $this->validateEnvironmentVariables($data);
            $this->saveRawRequestData($data);
            $this->validateRequest($data);

        } catch (UnexpectedRequestException | EnvironmentVariablesException $e) {
            return $this->handleException($data, $e);
        } catch (UnknownChatException | UnknownIpAddressException $e) {
            return $this->handleException($data, $e);
        } catch (TelegramModelException $e) {
            return $this->handleException($data, $e);
        } catch (Exception $e) {
            BotErrorNotificationService::send($e->getMessage() . "Line: " . $e->getLine());
            return $this->handleException($data, $e);
        }


        return $next($request);
    }


    private function handleException(array $requestData, Exception $e): Response
    {
        Log::error($e->getmessage() . " Line: " . $e->getLine());

        if (!($e instanceof UnknownChatException)) {
            FailedRequestJob::dispatch($requestData);
        }
        return response(env("APP_DEBUG") ? $e->getMessage() : Response::$statusTexts[500], Response::HTTP_OK);
    }



    private function validateEnvironmentVariables(array $requestData): void
    {
        if (empty(env("TELEGRAM_CHAT_ADMINS_ID"))) {
            throw new EnvironmentVariablesException(CONSTANTS::EMPTY_ENVIRONMENT_VARIABLES, __METHOD__);
        }
    }

    private function saveRawRequestData(array $requestData): void
    {
        date_default_timezone_set('Europe/Moscow');
        $requestLogData = Storage::json("rawrequest.json") ?? [];
        $requestLogData[] = array_merge($requestData, ['Moscow_time' => date("F j, Y, g:i a")]);
        Storage::put("rawrequest.json", json_encode($requestLogData, JSON_UNESCAPED_UNICODE));
    }

    private function validateRequest(array $requestData): void
    {
        $middlewareService = new TelegramMiddlewareService($requestData);
        $middlewareService->checkIfObjectTypeExpected();
        $requestModel = (new BaseTelegramRequestModel($requestData))->getModel();
        $this->chatIdAllowed = $middlewareService->checkIfChatIdAllowed($requestModel->getChatId());
        $middlewareService->checkIfIpAllowed(request()->ip());
    }
}
