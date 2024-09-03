<?php

namespace App\Http\Middleware;

use App\Exceptions\EnvironmentVariablesException;
use App\Models\Admin;
use App\Classes\Menu;
use App\Classes\PrivateChatCommandCore;
use App\Exceptions\BaseTelegramBotException;
use App\Exceptions\UnexpectedRequestException;
use App\Jobs\FailedRequestJob;
use App\Models\Chat;
use App\Services\BotErrorNotificationService;
use App\Services\TelegramBotService;
use Illuminate\Http\Client\HttpClientException;
use App\Classes\CommandsList;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Classes\ReplyKeyboardMarkup;
use App\Classes\Command;
use App\Services\TelegramMiddlewareService;
use Closure;
use Exception;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;
use App\Models\TelegramRequestModelBuilder;
use App\Exceptions\UnknownChatException;
use App\Exceptions\UnknownIpAddressException;
use App\Models\UnknownObjectModel;
use Illuminate\Support\Facades\Storage;
use App\Services\CONSTANTS;

class TelegramApiMiddleware
{
    private bool $chatIdAllowed = false;

    private $chatModel = null;

    private TelegramRequestModelBuilder $requestModel;

    private TelegramMiddlewareService $middlewareService;

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $data = $request->all();
        //TODO delete it
        return response("OK", Response::HTTP_OK);
        // throw new BaseTelegramBotException("test", __METHOD__);

        $this->saveRawRequestData($data);
        $this->middlewareService = new TelegramMiddlewareService($data);
        $this->middlewareService->validateEnvironmentVariables(env("DB_HOST"), env("ALLOWED_CHATS_ID"));
        $this->middlewareService->checkIfIpAllowed(request()->ip());
        $this->requestModel = (new TelegramRequestModelBuilder($data))->create();
        $this->setContainerDeps();

        if ($this->requestModel->getChatType() !== "private") {
            $this->setChat();
        }

        return $next($request);
    }

    /**
     * Set or create a new chat by groupchat Id
     */
    private function setChat(): void
    {
        $chatExists = Chat::where("chat_id", $this->requestModel->getChatId())->exists();

        if (!$chatExists) {
            app(TelegramBotService::class)->createChat();
            app(TelegramBotService::class)->setMyCommands();
        } else {
            app(TelegramBotService::class)->setChat($this->requestModel->getChatId());
        }
        return;
    }

    private function setContainerDeps()
    {
        app()->singleton(TelegramBotService::class, fn() => new TelegramBotService($this->requestModel));
        app()->singleton("commandsList", fn() => new CommandsList());
    }

    private function saveRawRequestData(array $requestData): void
    {
        date_default_timezone_set('Europe/Moscow');
        $requestLogData = Storage::json("rawrequest.json") ?? [];
        $requestLogData[] = array_merge($requestData, ['Moscow_time' => date("F j, Y, g:i a")]);
        Storage::put("rawrequest.json", json_encode($requestLogData, JSON_UNESCAPED_UNICODE));
    }

}


