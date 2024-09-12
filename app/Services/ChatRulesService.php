<?php

namespace App\Services;

use App\Enums\BanMessages;
use Illuminate\Support\Facades\Cache;
use App\Enums\Time;
use Illuminate\Support\Facades\Log;
use App\Enums\ResTime;
use App\Exceptions\RestrictMemberFailedException;
use App\Models\MessageModels\MediaModels\BaseMediaModel;
use App\Models\TelegramRequestModelBuilder;
use App\Models\MessageModels\MessageModel;
use App\Models\StatusUpdates\NewMemberJoinUpdateModel;
use App\Models\StatusUpdates\InvitedUserUpdateModel;
use App\Models\MessageModels\TextMessageModel;

class ChatRulesService
{
    private ?ResTime $restrictionTime;

    private TelegramRequestModelBuilder $model;

    private string $humanReadableRerestritionTime = "";

    public function __construct(private TelegramBotService $botService)
    {
        $this->model = $this->botService->getRequestModel();
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

        if ($this->botService->getChat()->newUserRestrictions->enabled == 0) {
            return false;
        }

        if ($this->model instanceof NewMemberJoinUpdateModel) {

            $result = $this->botService->restrictChatMember();

            if ($result) {
                log::info(BanMessages::NEW_MEMBER_RESTRICTED->withId($this->model->getFromId()));
                return true;
            }
        }

        if ($this->model instanceof InvitedUserUpdateModel) {

            $invitedUsers = $this->model->getInvitedUsersIdArray();

            if ($invitedUsers !== []) {

                foreach ($invitedUsers as $user_id) {

                    $result = $this->botService->restrictChatMember(id: $user_id);
                    if ($result) {
                        log::info(BanMessages::INVITED_USER_BLOCKED->withId($user_id));
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
            $this->botService->banUser();
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


        if ($this->botService->banUser()) {
            return true;
        }
        return false;
    }

    public function ifMessageHasLinkBlockUser(): bool
    {
        if (!$this->model instanceof MessageModel || $this->model->getFromAdmin()) {
            return false;
        }

        if (!$this->shouldRestrictUser('linksFilter')) {
            return false;
        }

        $resTime = $this->getRestrictionTime('linksFilter');

        if ($this->model->getHasTextLink() || ($this->model->getHasLink())) {
            $this->botService->setBanReasonModelName('linksFilter');
            $this->botService->banUser($resTime);

            if ($this->shouldDeleteMessage('linksFilter')) {
                $this->botService->deleteMessage();
            }
            return true;
        }
        return false;
    }

    /**
     * Check if the user should be restricted based on the given type.
     *
     * The method checks if the restrict_user column is set to true in the
     * corresponding relation with the given type.
     *
     * @param string $type the type of the relation, e.g. 'linksFilter', 'newUserRestrictions'
     * @return bool true if the user should be restricted, false otherwise
     */
    protected function shouldRestrictUser(string $type): bool
    {
        $relation = $this->botService->getChat()->{$type};
        return $relation->first()->restrict_user;
    }

    /**
     * Check if the chat settings allow deleting messages of the specified type
     *
     * @param string $type the type of messages to check, e.g. 'linksFilter', 'newUserRestrictions'
     * @return bool true if the bot should delete messages of the specified type, false otherwise
     */
    protected function shouldDeleteMessage(string $type): bool
    {
        $relation = $this->botService->getChat()->{$type};
        return $relation->first()->delete_message;
    }



    /**
     * Extract restriction time from the relation with the given type
     *
     * @param string $type the type of the relation, e.g. 'linksFilter', 'newUserRestrictions'
     * @return \App\Enums\ResTime the restriction time represented as an enum value
     */
    protected function getRestrictionTime(string $type): ResTime
    {
        $relation = $this->botService->getChat()->{$type};
        $resTime = ResTime::from($relation->first()->restriction_time);
        return $resTime;
    }


}


