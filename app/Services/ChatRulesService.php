<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use App\Exceptions\RestrictMemberFailedException;
use App\Models\BaseMediaModel;
use App\Models\BaseTelegramRequestModel;
use App\Models\ForwardMessageModel;
use App\Models\InvitedUserUpdateModel;
use App\Models\MessageModel;
use App\Models\NewMemberJoinUpdateModel;
use App\Models\TextMessageModel;

class ChatRulesService
{
    private TelegramBotService $telegramBotService;

    public function __construct(private BaseTelegramRequestModel $message)
    {
        $this->telegramBotService = new TelegramBotService($this->message);
        $this->telegramBotService->prettyRequestLog();
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
            !($this->message instanceof InvitedUserUpdateModel) &&
            !($this->message instanceof NewMemberJoinUpdateModel)
        ) {

            return false;
        }

        if ($this->message instanceof NewMemberJoinUpdateModel) {

            $result = $this->telegramBotService->restrictChatMember();

            if ($result) {
                log::info(CONSTANTS::NEW_MEMBER_RESTRICTED . "user_id: " . $this->message->getFromId());
                return true;
            }
        }

        if ($this->message instanceof InvitedUserUpdateModel) {

            $invitedUsers = $this->message->getInvitedUsersIdArray();

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
    public function deleteMessageIfContainsBlackListWords(): bool
    {
        if (
            $this->message instanceof TextMessageModel &&
            !$this->message->getFromAdmin()
        ) {
            $filter = new FilterService($this->message);
            if ($filter->wordsFilter()) {
                $this->telegramBotService->deleteMessage();
            }
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
        if (
            $this->message instanceof ForwardMessageModel &&
            !$this->message->getFromAdmin()
        ) {
            if ($this->telegramBotService->banUser())
                ;

            return true;
        }

        return false;
    }

    public function ifMessageHasLinkBlockUser(): bool
    {
        if ($this->message->getFromAdmin()) {
            return false;
        }

        if ($this->message->getFromAdmin()) {
            return false;
        }

        if ($this->message instanceof MessageModel) {
            if ($this->message->getHasTextLink()) {

                if ($this->telegramBotService->banUser()) {
                    return true;
                }
            }
        }

        if (
            $this->message instanceof TextMessageModel ||
            $this->message instanceof BaseMediaModel
        ) {
            if ($this->message->getHasLink()) {

                if ($this->telegramBotService->banUser()) {
                    return true;
                }
            }
        }
        return false;
    }
}
