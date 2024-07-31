<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Exceptions\RestrictMemberFailedException;
use App\Models\MessageModels\MediaModels\BaseMediaModel;
use App\Models\TelegramRequestModelBuilder;
use App\Models\MessageModels\MessageModel;
use App\Models\StatusUpdates\NewMemberJoinUpdateModel;
use App\Models\StatusUpdates\InvitedUserUpdateModel;
use App\Models\MessageModels\TextMessageModel;

class ChatRulesService
{
    private TelegramBotService $telegramBotService;

    public function __construct(private TelegramRequestModelBuilder $model)
    {
        $this->telegramBotService = new TelegramBotService($this->model);
    }

    /**
     * Summary of blockNewVisitor
     * Restrict new member for 24 hours
     * @throws \App\Exceptions\RestrictMemberFailedException
     * @return bool
     */
    public function blockNewVisitor(): bool
    {
        if (
            !($this->model instanceof InvitedUserUpdateModel) &&
            !($this->model instanceof NewMemberJoinUpdateModel)
        ) {
            return false;
        }

        if ($this->model instanceof NewMemberJoinUpdateModel) {

            $result = $this->telegramBotService->restrictChatMember();

            if ($result) {
                log::info(CONSTANTS::NEW_MEMBER_RESTRICTED . "user_id: " . $this->model->getFromId());
                return true;
            }
        }

        if ($this->model instanceof InvitedUserUpdateModel) {

            $invitedUsers = $this->model->getInvitedUsersIdArray();

            if ($invitedUsers !== []) {

                foreach ($invitedUsers as $user_id) {

                    $result = $this->telegramBotService->restrictChatMember(id: $user_id);
                    if ($result) {
                        log::info(CONSTANTS::INVITED_USER_BLOCKED . "USER_ID: " . $user_id);
                    }
                }
                return true;
            }
        }
        throw new RestrictMemberFailedException(CONSTANTS::RESTRICT_NEW_USER_FAILED, __METHOD__);
    }

    /**
     * Summary of deleteMessageIfContainsBlackListWords
     * Words are stored at Storage/app/badWord.json & badPhrases.json
     * @return bool
     */
    public function ifMessageContainsBlackListWordsBanUser(): bool
    {
        if ($this->model->getFromAdmin()) {
            return false;
        }

        if
        (
            !($this->model instanceof TextMessageModel) &&
            !($this->model instanceof BaseMediaModel)
        ) {
            return false;
        }

        $filter = new FilterService($this->model);

        if ($filter->wordsFilter()) {
            $this->telegramBotService->banUser();
            return true;
        }

        return false;
    }

    /**
     * Summary of blockUserIfMessageIsForward
     * Forward message from another group or chat
     * @return bool
     */
    public function blockUserIfMessageIsForward(): bool
    {
        if (!($this->model instanceof MessageModel)) {
            return false;
        }

        $isForward = $this->model->getIsForward();
        $isAdmin = $this->model->getFromAdmin();

        if (!$isForward || $isAdmin) {
            return false;
        }

        if (Cache::get(CONSTANTS::CACHE_BAN_FORWARD_MESSAGES . $this->model->getChatId())) {
            return false;
        }


        if ($this->telegramBotService->banUser()) {
            return true;
        }
        return false;
    }

    public function ifMessageHasLinkBlockUser(): bool
    {
        if ($this->model->getFromAdmin()) {
            return false;
        }

        if (!($this->model instanceof MessageModel)) {
            return false;
        }

        // Text_link key value
        if ($this->model->getHasTextLink()) {
            $this->telegramBotService->banUser();
            return true;
        }

        if (
            method_exists($this->model, 'getHasLink') &&
            $this->model->getHasLink()
        ) {
            $this->telegramBotService->banUser();
            return true;

        }

        return false;
    }
}

