<?php

namespace Tests;

use App\Models\ForwardMessageModel;
use App\Models\InvitedUserUpdateModel;
use App\Models\MessageModel;
use App\Models\MultiMediaModel;
use App\Models\TextMessageModel;
use App\Services\FilterService;
use App\Services\ChatSettingsService;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use App\Services\TelegramBotService;
use App\Models\NewMemberJoinUpdateModel;
use App\Models\BaseTelegramRequestModel;
use App\Services\CONSTANTS;


abstract class TestCase extends BaseTestCase
{
    protected array $testObjects;

    protected $service;

    protected array $adminsIdArray;

    protected $chatPermissions;

    protected $filter;

    private int $adminId;

    private int $testUserId;

    private int $secondTestUserId;

    private int $unknownChatId = 1234567890;


    private array $unknownObject = [
        "update_id" => 11122233,
        "unknown_type" =>
            [
                "chat" =>
                    [
                        "id" => -1002222230714,
                        "title" => "Testylvania",
                        "type" => "supergroup"
                    ]
            ]
    ];




    protected function setUp(): void
    {
        parent::setUp();
        $this->testObjects = json_decode(file_get_contents(__DIR__ . "/TestObjects.json"), true);
        $this->adminsIdArray = explode(",", env("TELEGRAM_CHAT_ADMINS_ID"));
        $this->chatPermissions = new ChatSettingsService();
        $this->adminId = 754429643;
        $this->testUserId = 850434834; //bot id
        $this->secondTestUserId = 1087968824; //bot id
    }



    public function getAdminId()
    {
        return $this->adminId;
    }


    public function getTestUserId()
    {
        return $this->testUserId;
    }


    public function getSecondTestUserId()
    {
        return $this->secondTestUserId;
    }


    public function getUnknownChatId(): int
    {
        return $this->unknownChatId;
    }


    public function getNewMemberUpdateModel(): NewMemberJoinUpdateModel
    {
        return new NewMemberJoinUpdateModel([
            "chat_member" => [
                "chat" => [
                    "id" => -1002222230714
                ],
                "from" => [
                    "id" => $this->getTestUserId(),
                    "first_name" => "Mai Abrikosov"
                ],
                "old_chat_member" => [
                    "status" => "left"
                ],
                "new_chat_member" => [
                    "user" => [
                        "id" => $this->getTestUserId()
                    ],
                    "status" => "member"
                ]
            ]
        ]);
    }


    public function getMessageModel(): MessageModel
    {
        return new MessageModel([
            "update_id" => 24024902,
            "message" => [
                "message_id" => 17770,
                "chat" => [
                    "id" => -1002222230714
                ],
                "from" => [
                    "id" => $this->getTestUserId(),
                    "first_name" => "Mai Abrikosov"
                ],
            ]
        ]);
    }


    public function getTextMessageModel(): MessageModel
    {
        return new TextMessageModel([
            "update_id" => 24024902,
            "message" => [
                "message_id" => 17770,
                "chat" => [
                    "id" => -1002222230714
                ],
                "from" => [
                    "id" => $this->getTestUserId(),
                    "first_name" => "Mai Abrikosov"
                ],
                "text" => "This is your life, and it's ending one minute at a time."
            ]
        ]);
    }



    public function getMultiMediaModel(): MultiMediaModel
    {
        return new MultiMediaModel([
            "update_id" => 24024902,
            "message" => [
                "message_id" => 17770,
                "chat" => [
                    "id" => -1002222230714
                ],
                "from" => [
                    "id" => $this->getTestUserId(),
                    "first_name" => "Mai Abrikosov"
                ],
                "video" => [],
                "photo" => [],
                "caption" => "some text bla-bla-bla",
                "caption_entities" => [
                    "type" => "unknown",
                ]
            ]
        ]);


    }

    public function getForwardMessageModel(): ForwardMessageModel
    {
        return new ForwardMessageModel([
            "update_id" => 24024902,
            "message" => [
                "message_id" => 17770,
                "chat" => [
                    "id" => -1002222230714
                ],
                "from" => [
                    "id" => $this->getTestUserId(),
                    "first_name" => "Mai Abrikosov"
                ],
                "forward_origin" => [],
                "forward_from_chat" => [],
            ]
        ]);
    }



    /**
     * User Id and invited user id are not equal
     * @return \App\Models\InvitedUserUpdateModel
     */
    public function getInvitedUserUpdateModel(): InvitedUserUpdateModel
    {
        return new InvitedUserUpdateModel([
            "chat_member" => [
                "chat" => [
                    "id" => -1002222230714
                ],
                "from" => [
                    "id" => $this->getTestUserId(),
                    "first_name" => "Mai Abrikosov"
                ],
                "old_chat_member" => [
                    "status" => "left"
                ],
                "new_chat_member" => [
                    "user" => [
                        "id" => $this->getSecondTestUserId()
                    ],
                    "status" => "member"
                ]
            ]
        ]);
    }


    public function getUnknownObject(): array
    {
        return $this->unknownObject;
    }

}
