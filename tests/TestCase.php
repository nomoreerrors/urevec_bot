<?php

namespace Tests;

use App\Models\ForwardMessageModel;
use Illuminate\Support\Facades\Http;
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

    private int $adminId = 7400599756;

    private int $testUserId;

    protected int $invalidUserId = 9999999;

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
        $this->adminsIdArray = [5555, 6666, 7777]; //Just for tests
        $this->chatPermissions = new ChatSettingsService();
        $this->adminId = 754429643;
        $this->testUserId = 850434834; //bot id
        $this->secondTestUserId = 1087968824; //bot id
    }

    public function fakeSucceedResponse()
    {
        Http::fake(fn() => Http::response([
            "ok" => true,
            "description" => "success",
        ], 200));
    }

    public function fakeGetMyCommandsResponse($command, $description, $secondCommand, $secondDescription)
    {
        Http::fake([
            '*' => Http::response([
                "ok" => true,
                "result" => [
                    [
                        "command" => $command,
                        "description" => $description
                    ],
                    [
                        "command" => $secondCommand,
                        "description" => $secondDescription
                    ]
                ]
            ], 200, [])
        ]);
    }

    public function fakeFailedResponse()
    {
        Http::fake(fn() => Http::response([
            "ok" => false,
            "description" => "Bad Request: chat not found",
        ], 500));
    }

    public function fakeResponseWithAdminsIds(int $id, int $secondId, bool $status = true)
    {
        return Http::fake(fn() => Http::response([
            'ok' => $status,
            'description' => 'ok',
            'result' => [
                ['user' => ['id' => $id]], // Admin 1
                ['user' => ['id' => $secondId]] // Admin 2
            ]
        ], 200));
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


    public function getNewMemberJoinUpdateModelData(): array
    {
        return [
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
        ];
    }


    public function getMessageModelData(): array
    {
        return [
            "update_id" => 24024902,
            "message" => [
                "message_id" => 17770,
                "chat" => [
                    "id" => -1002222230714,
                    "type" => "supergroup",
                    "title" => "Jared Leto"
                ],
                "from" => [
                    "id" => $this->getTestUserId(),
                    "first_name" => "Mai Abrikosov"
                ],
            ]
        ];
    }


    public function getTextMessageModelData(): array
    {
        return [
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
        ];
    }



    public function getMultiMediaModelData(): array
    {
        return [
            "update_id" => 24024902,
            "message" => [
                "message_id" => 17770,
                "chat" => [
                    "id" => -1002222230714,
                    "type" => "supergroup",
                    "title" => "Jared Leto"
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
        ];


    }

    public function getForwardMessageModelData(): array
    {
        return [
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
        ];
    }



    /**
     * User Id and invited user id are not equal
     * @return array
     */
    public function getInvitedUserUpdateModelData(): array
    {
        return [
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
        ];
    }


    public function getUnknownObject(): array
    {
        return $this->unknownObject;
    }

    public function getInvalidUserId(): int
    {
        return $this->invalidUserId;
    }

}
