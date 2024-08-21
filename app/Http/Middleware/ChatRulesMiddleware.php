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
use App\Models\TelegramRequestModelBuilder;
use Symfony\Component\HttpFoundation\Response;
use App\Services\CONSTANTS;
use Illuminate\Support\Facades\Log;


class ChatRulesMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $requestModel = app("botService")->getRequestModel();

        if ($requestModel->getChatType() !== "private") {
            $data = $requestModel->getData();
            $ruleService = new ChatRulesService($requestModel);
        } else {
            return $next($request);
        }


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
