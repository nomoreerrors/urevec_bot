<?php

namespace App\Classes;

use App\Services\ChatRulesService;
use App\Services\TelegramBotService;
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


class ChatRules
{
    public function __construct(
        private TelegramBotService $telegramBotService,
        private ChatRulesService $ruleService
    ) {
    }

    public function validate(): Response
    {
        if ($this->ruleService->blockNewVisitor()) {
            return response(CONSTANTS::NEW_MEMBER_RESTRICTED, Response::HTTP_OK);
        }

        if ($this->ruleService->blockUserIfMessageIsForward()) {
            return response(CONSTANTS::MEMBER_BLOCKED, Response::HTTP_OK);
        }

        if ($this->ruleService->ifMessageHasLinkBlockUser()) {
            return response(CONSTANTS::MEMBER_BLOCKED, Response::HTTP_OK);
        }

        if ($this->ruleService->ifMessageContainsBlackListWordsBanUser()) {
            return response(CONSTANTS::DELETED_BY_FILTER, Response::HTTP_OK);
        }
        return response(CONSTANTS::DEFAULT_RESPONSE, Response::HTTP_OK);
    }


}

