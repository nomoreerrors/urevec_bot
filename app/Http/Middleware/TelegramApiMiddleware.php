<?php

namespace App\Http\Middleware;

use App\Exceptions\EnvironmentVariablesException;
use App\Exceptions\TelegramModelException;
use App\Exceptions\UnexpectedRequestException;
use App\Services\TelegramMiddlewareService;
use Closure;
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


    public function saveRawRequestData(array $data)
    {
        date_default_timezone_set('Europe/Moscow');
        $requestLog = Storage::json("rawrequest.json");
        $info = $data;
        $info["Moscow_time"] = date("F j, Y, g:i a");
        if (!$requestLog) {
            Storage::put("rawrequest.json", json_encode($info, JSON_UNESCAPED_UNICODE));
        } else {
            $requestLog[] = $info;
            Storage::put("rawrequest.json", json_encode($requestLog, JSON_UNESCAPED_UNICODE));
        }
    }



// 
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
            $data = $request->all();


        try {
                if (empty(env("TELEGRAM_CHAT_ADMINS_ID"))) {
                    throw new EnvironmentVariablesException(CONSTANTS::EMPTY_ENVIRONMENT_VARIABLES, __METHOD__);
                }

                $this->saveRawRequestData($data);
                $middlewareService = new TelegramMiddlewareService($data);
                $middlewareService->checkIfObjectTypeExpected();


                $message = (new BaseTelegramRequestModel($data))->create();
                
                $middlewareService->checkIfChatIdAllowed($message->getChatId());
                $middlewareService->checIfIpAllowed($request->ip());
                
               

      
        } catch (UnknownChatException | UnknownIpAddressException | UnexpectedRequestException $e) {

            Log::error($e->getInfo() . $e->getData());
            return response($e->getMessage(), Response::HTTP_OK);
            
        } catch (TelegramModelException $e) {

            Log::error($e->getInfo() . $e->getData());
            return response(Response::$statusTexts[500], Response::HTTP_INTERNAL_SERVER_ERROR);
        }


        return $next($request);
    }
}
