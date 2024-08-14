<?php

namespace App\Traits;

trait RestrictionsCases
{
    public function getRestrictionsCases()
    {
        switch ($this->command) {
            case $this->enum::EDIT_RESTRICTIONS->value:
                $this->sendEditRestrictionsButtons();
                break;
            case $this->enum::RESTRICTIONS_ENABLE_ALL->value:
            case $this->enum::RESTRICTIONS_DISABLE_ALL->value:
                $this->toggleAllRestrictions();
                break;
            case $this->enum::SEND_MEDIA_ENABLE->value:
            case $this->enum::SEND_MEDIA_DISABLE->value:
                $this->toggleSendMedia();
                break;
            case $this->enum::SEND_MESSAGES_ENABLE->value:
            case $this->enum::SEND_MESSAGES_DISABLE->value:
                $this->toggleSendMessages();
                break;
        }
    }
}
