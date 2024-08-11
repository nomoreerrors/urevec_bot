<?php

namespace Tests;

use App\Models\ForwardMessageModel;
use App\Models\Admin;
use App\Models\Chat;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use App\Models\InvitedUserUpdateModel;
use App\Models\MessageModel;
use App\Models\MultiMediaModel;
use App\Models\TextMessageModel;
use App\Services\FilterService;
use App\Services\ChatSettingsService;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use App\Services\TelegramBotService;
use App\Models\NewMemberJoinUpdateModel;
use App\Models\TelegramRequestModelBuilder;
use App\Services\CONSTANTS;


abstract class TestCase extends BaseTestCase
{
    protected array $testObjects;

    protected $service;

    protected Chat $chat;

    protected $requestModel;

    protected $model;

    protected array $adminsIdArray;

    protected $chatPermissions;

    protected $filter;

    private int $adminId = 7400599756;

    private int $testUserId = 9999999;

    protected int $invalidUserId = 9999999;

    protected TelegramBotService $botService;
    protected array $data;

    protected Admin $admin;

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

    public function fakeSendMessageSucceedResponse()
    {
        Http::fake([
            env('TELEGRAM_API_URL') . env('TELEGRAM_API_TOKEN') . "/sendMessage" =>
                Http::response([
                    "ok" => true,
                    "description" => "success",
                ], 200)
        ]);
    }

    public function fakeDeleteMessageSucceedResponse()
    {
        Http::fake([
            env('TELEGRAM_API_URL') . env('TELEGRAM_API_TOKEN') . "/deleteMessage" =>
                Http::response([
                    "ok" => true,
                    "description" => "success",
                ], 200)
        ]);
    }

    public function fakeRestrictMemberSucceedResponse()
    {
        Http::fake([
            env('TELEGRAM_API_URL') . env('TELEGRAM_API_TOKEN') . "/restrictChatMember" =>
                Http::response([
                    "ok" => true,
                    "description" => "success",
                ], 200)
        ]);
    }

    public function fakeResponseWithAdminsIds(int $id, int $secondId, bool $status = true)
    {
        return Http::fake([
            env('TELEGRAM_API_URL') . env('TELEGRAM_API_TOKEN') . "/getChatAdministrators" =>
                Http::response([

                    'ok' => $status,
                    'description' => 'ok',
                    'result' => [
                        [
                            'user' => [
                                'id' => $id, //Id converted to admin_id when it's assigning in TelegramRequestModelBuilder
                                'is_bot' => false,
                                'first_name' => 'Dolph Lundgren',
                                'username' => 'DolphLundgren'
                            ]
                        ], // Admin 1
                        [
                            'user' => [
                                'id' => $secondId,
                                'is_bot' => false,
                                'first_name' => 'Antonio Banderos',
                                'username' => 'AntonioBanderos'
                            ]
                        ], // Admin 2
                    ]
                ], 200)
        ]);
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
                    "id" => -1002222230714,
                    "title" => "Jared Leto",
                    "type" => "supergroup"

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

    public function addFakeChatToDatabase(): void
    {
        Chat::create([
            "chat_id" => -1002222230714,
            "chat_title" => "Jared Leto",
            "chat_admins" => [23456, 34567]
        ]);
    }

    public function getTextMessageModelData(): array
    {
        return [
            "update_id" => 24024902,
            "message" => [
                "message_id" => 17770,
                "chat" => [
                    "id" => -1002222230714,
                    "title" => "Jared Leto",
                    "type" => "supergroup"

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
                    [
                        "ofsset" => 0,
                        "type" => "unknown",
                    ],
                    [
                        "ofsset" => 3,
                        "type" => "unknown2",
                    ],
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
                    "id" => -1002222230714,
                    "title" => "Jared Leto",
                    "type" => "supergroup"
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
                    "id" => -1002222230714,
                    "title" => "Jared Leto",
                    "type" => "supergroup"
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


    public function getTestLogFile()
    {
        return file_get_contents(storage_path("logs/testing.log")) ?? "";
    }


    public function clearTestLogFile()
    {
        if (file_exists(storage_path("logs/testing.log"))) {
            unlink(storage_path("logs/testing.log"));
            file_put_contents(storage_path("logs/testing.log"), "");
            return;
        }
        return;
    }

    public function setAllRestrictionsToFalse(Chat $chat)
    {
        $chat->newUserRestrictions()->update([
            'restrict_new_users' => 0,
            'restriction_time' => 0,
            'can_send_messages' => 0,
            'can_send_media' => 0
        ]);
    }

    public function setAllRestrictionsDisabled(Chat $chat)
    {
        $chat->newUserRestrictions()->update([
            'restrict_new_users' => 0,
        ]);
    }

    public function setAllRestrictionsEnabled(Chat $chat)
    {
        $chat->newUserRestrictions()->update([
            'restrict_new_users' => 1,
            'restriction_time' => 2, // Last restriction time is always stored in DB even if restrictions are disabled
            'can_send_messages' => 0,
            'can_send_media' => 0
        ]);
    }

    protected function getPrivateChatMessage(int $fromId, string $command = null): array
    {
        $data = $this->getTextMessageModelData();
        $data["message"]["from"]["id"] = $fromId;
        $data["message"]["chat"]["id"] = $fromId;
        $data["message"]["chat"]["type"] = 'private';
        $data["message"]["text"] = $command ?? "some text";
        return $data;
    }

    /**
     * Summary of putLastChatIdToCache
     * @param int $adminId Id of user that is typing to private chat
     * @param int $chatId Id of selected chat - one of his multiple groups
     * @return bool
     */
    protected function fakeChatSelected(int $adminId, int $chatId)
    {
        return Cache::put(
            "last_selected_chat_" . $adminId,
            $chatId,
        );
    }
}



