<?php

namespace App\Enums;

enum RestrictChatMemberData: string
{
    case CHAT_ID = 'chat_id';
    case USER_ID = 'user_id';
    case CAN_SEND_MESSAGES = 'can_send_messages';
    case CAN_SEND_DOCUMENTS = 'can_send_documents';
    case CAN_SEND_PHOTOS = 'can_send_photos';
    case CAN_SEND_VIDEOS = 'can_send_videos';
    case CAN_SEND_VIDEO_NOTES = 'can_send_video_notes';
    case CAN_SEND_OTHER_MESSAGES = 'can_send_other_messages';
    case UNTIL_DATE = 'until_date';
}
