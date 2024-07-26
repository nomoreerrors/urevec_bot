<?php

namespace App\Http\Middleware;

use App\Services\ChatRulesService;
use Closure;
use Illuminate\Http\Request;
use App\Exceptions\BanUserFailedException;
use Illuminate\Support\Facades\Cache;
use App\Exceptions\RestrictMemberFailedException;
use App\Exceptions\BaseTelegramBotException;
use App\Jobs\FailedRequestJob;
use App\Models\BaseTelegramRequestModel;
use Symfony\Component\HttpFoundation\Response;
use App\Services\CONSTANTS;
use Illuminate\Support\Facades\Log;


class ChatRulesMiddleware
{
    /**
     * Handle an incoming request.
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $model = app("requestModel");
        $data = $model->getData();

        if (!Cache::has(CONSTANTS::CACHE_BAN_FORWARD_MESSAGES . $model->getChatId())) {
            Cache::put(CONSTANTS::CACHE_BAN_FORWARD_MESSAGES . $model->getChatId(), 0);
        }

        if (!Cache::has(CONSTANTS::CACHE_MY_COMMANDS_SET . $model->getChatId())) {
            Cache::put(CONSTANTS::CACHE_MY_COMMANDS_SET . $model->getChatId(), 0);
        }

        if ($model->getFromAdmin()) {
            return $next($request);
        }

        $ruleService = new ChatRulesService($model);

        try {
            if ($ruleService->blockNewVisitor()) {
                return response(CONSTANTS::NEW_MEMBER_RESTRICTED, Response::HTTP_OK);
            }

            if ($ruleService->blockUserIfMessageIsForward()) {
                return response(CONSTANTS::MEMBER_BLOCKED, Response::HTTP_OK);
            }

            if ($ruleService->ifMessageHasLinkBlockUser()) {
                return response(CONSTANTS::MEMBER_BLOCKED, Response::HTTP_OK);
            }

            if ($ruleService->ifMessageContainsBlackListWordsBanUser()) {
                return response(CONSTANTS::DELETED_BY_FILTER, Response::HTTP_OK);
            }

        } catch (BaseTelegramBotException | RestrictMemberFailedException | BanUserFailedException $e) {
            Log::error($e->getMessage() . $e->getData());
            FailedRequestJob::dispatch($data);
            return response($e->getMessage(), Response::HTTP_OK);
        } catch (\Throwable $e) {
            FailedRequestJob::dispatch($data);
            return response($e->getMessage(), Response::HTTP_OK);
        }
        return $next($request);
    }

}
