<?php

namespace App\Traits;
use App\Exceptions\RestrictChatMemberFailedException;
use App\Enums\RestrictChatMemberData;
use App\Services\CONSTANTS;
use App\Enums\ResTime;

/**
 * Trait RestrictChatMemberTrait
 *
 * Restrict chat member permission
 *
 * Trait has a method restrictChatMember which restricts member in the chat
 * You can set time for restriction in the property $chatRestrictionTime
 * Values for this property are in the enum ResTime
 */
trait RestrictChatMemberTrait
{
    private ResTime $chatRestrictionTime = ResTime::DAY;


    public function restrictChatMember(ResTime $resTime = null, int $id = null): void
    {
        $until_date = $this->getUntilDate($resTime);
        $status = $this->getRestrictStatusFromModel();
        $data = $this->getRestrictionData($status, $until_date, $id);

        $response = $this->sendPost(
            'restrictChatMember',
            $data
        );

        if (!$response->Ok()) {
            throw new RestrictChatMemberFailedException(CONSTANTS::RESTRICT_MEMBER_FAILED, __METHOD__);
        }
        return;
    }

    /**
     * Get Telegram restrictions data according to model db columns status
     * @param array $status
     * @param int $until_date
     * @param int $id
     * @return array
     */
    protected function getRestrictionData(array $status, int $until_date, int $id = null): array
    {
        $data = [
            RestrictChatMemberData::CHAT_ID->value => $this->getRequestModel()->getChatId(),
            RestrictChatMemberData::USER_ID->value => $id ?? $this->getRequestModel()->getFromId(),
            RestrictChatMemberData::CAN_SEND_MESSAGES->value => $status['canSendMessages'],
            RestrictChatMemberData::CAN_SEND_DOCUMENTS->value => $status['canSendMedia'],
            RestrictChatMemberData::CAN_SEND_PHOTOS->value => $status['canSendMedia'],
            RestrictChatMemberData::CAN_SEND_VIDEOS->value => $status['canSendMedia'],
            RestrictChatMemberData::CAN_SEND_VIDEO_NOTES->value => $status['canSendMedia'],
            RestrictChatMemberData::CAN_SEND_OTHER_MESSAGES->value => $status['canSendMedia'],
            RestrictChatMemberData::UNTIL_DATE->value => $until_date
        ];
        return $data;
    }


    protected function getUntilDate(ResTime $resTime = null): int
    {
        $time = $resTime ?
            $resTime->getSeconds() :
            $this->getChatRestrictionTime()->getSeconds();

        $until_date = time() + $time;
        return $until_date;
    }


    protected function getRestrictStatusFromModel(): array
    {
        if (empty($this->getBanReasonModelName())) {
            throw new RestrictChatMemberFailedException(CONSTANTS::RESTRICT_MEMBER_FAILED, __METHOD__);
        }

        if (empty($this->getChat()->{$this->getBanReasonModelName()})) {
            throw new RestrictChatMemberFailedException(CONSTANTS::UNKNOWN_MODEL, __METHOD__);
        }

        $reasonModel = $this->getChat()->{$this->getBanReasonModelName()};
        $status['canSendMedia'] = $reasonModel->can_send_media;
        $status['canSendMessages'] = $reasonModel->can_send_messages;
        return $status;
    }

}
