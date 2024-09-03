<?php

namespace Tests;

use App\Models\ForwardMessageModel;
use App\Classes\PrivateChatCommandCore;
use App\Classes\Buttons;
use ReflectionMethod;
use ReflectionProperty;
use ReflectionClass;
use Database\Seeders\SimpleSeeder;
use App\Models\Admin;
use Illuminate\Database\Eloquent\Model;
use App\Enums\ModerationSettingsEnum;
use App\Models\Chat;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use App\Services\ChatSettingsService;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use App\Services\TelegramBotService;
use App\Models\NewMemberJoinUpdateModel;
use App\Models\TelegramRequestModelBuilder;
use App\Services\CONSTANTS;


abstract class TestCase extends BaseTestCase
{
    protected array $testObjects;

    protected $restrictions;

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
        $this->clearTestLogFile();
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
        $restrictions = $chat->newUserRestrictions;
        $result = $restrictions->update([
            'enabled' => 0,
            'restriction_time' => 0,
            'can_send_messages' => 0,
            'can_send_media' => 0
        ]);
        if (!$result) {
            throw new \Exception("Failed to update restrictions");
        }
    }


    public function setAllRestrictionsDisabled(Chat $chat)
    {
        $chat->newUserRestrictions()->update([
            'enabled' => 0,
        ]);

        if (
            $chat->newUserRestrictions->first()->enabled
        ) {
            throw new \Exception("Restrictions are not disabled");
        }
    }

    public function setAllRestrictionsEnabled(Chat $chat)
    {
        $chat->newUserRestrictions()->update([
            'enabled' => 1,
            'restriction_time' => 2, // Last restriction time is always stored in DB even if restrictions are disabled
            'can_send_messages' => 0,
            'can_send_media' => 0
        ]);

        if (
            !$chat->newUserRestrictions->first()->enabled ||
            $chat->newUserRestrictions->first()->restriction_time !== 2 ||
            $chat->newUserRestrictions->first()->can_send_messages ||
            $chat->newUserRestrictions->first()->can_send_media
        ) {
            throw new \Exception("Restrictions are not enabled");
        }
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
    protected function putSelectedChatIdToCache(int $adminId, int $chatId)
    {
        return Cache::put(
            "last_selected_chat_" . $adminId,
            $chatId,
        );
    }

    protected function getLastSelectedChatIdFromCache(int $adminId)
    {
        return Cache::get(
            "last_selected_chat_" . $adminId
        );
    }

    protected function deleteSelectedChatFromCache(int $adminId)
    {
        return Cache::forget("last_selected_chat_" . $adminId);
    }

    public function getBackMenuJsonArrayFromCache(int $adminId): string
    {
        return Cache::get("back_menu_" . $adminId);
    }

    public function assertBackToPreviousMenuButtonWasSent()
    {
        $this->assertStringContainsString(ModerationSettingsEnum::BACK->value, $this->getTestLogFile());
    }

    /**
     * Expecting array of button names
     * @param array $buttons
     * @return void
     */
    public function assertButtonsWereSent(array $buttons)
    {
        foreach ($buttons as $key => $value) {
            $this->assertStringContainsString($value, $this->getTestLogFile());
        }
    }

    public function assertJsonBackMenuArrayContains(string $command, int $adminId)
    {
        $this->assertStringContainsString($command, $this->getBackMenuJsonArrayFromCache($adminId));
    }

    public function setCommand($command)
    {
        $this->data["message"]["text"] = $command;
    }

    public function assertReplyMessageSent($message)
    {
        $this->assertStringContainsString($message, $this->getTestLogFile());
    }

    public function putLastCommandToCache(int $adminId, string $lastCommand)
    {
        Cache::put(CONSTANTS::CACHE_LAST_COMMAND . $adminId, $lastCommand);
    }

    public function getLastCommandFromCache(int $adminId)
    {
        return Cache::get(CONSTANTS::CACHE_LAST_COMMAND . $adminId);
    }

    public function deleteLastCommandFromCache(int $adminId)
    {
        Cache::forget(CONSTANTS::CACHE_LAST_COMMAND . $adminId);
    }

    public function getEditRestrictionsButtons(Model $model, string $enum): array
    {
        return [
            $model->can_send_media ?
                $enum::CAN_SEND_MEDIA_DISABLE->value :
                $enum::CAN_SEND_MEDIA_ENABLE->value,

            $model->can_send_messages ?
                $enum::CAN_SEND_MESSAGES_DISABLE->value :
                $enum::CAN_SEND_MESSAGES_ENABLE->value,

            $model->enabled ?
                $enum::ENABLED_DISABLE->value :
                $enum::ENABLED_ENABLE->value,

                $enum::SELECT_RESTRICTION_TIME->value,
        ];
    }

    public function getFilterSettingsButtons(Model $model, string $enum): array
    {
        return [
            $model->enabled ?
                $enum::ENABLED_DISABLE->value :
                $enum::ENABLED_ENABLE->value,

            $model->delete_message ?
                $enum::DELETE_MESSAGE_DISABLE->value :
                $enum::DELETE_MESSAGE_ENABLE->value,

                $enum::EDIT_RESTRICTIONS->value
        ];
    }

    public function getRestrictionsTimeButtons(Model $model, string $enum): array
    {
        return [
                $enum::SET_TIME_MONTH->value,
                $enum::SET_TIME_WEEK->value,
                $enum::SET_TIME_DAY->value,
                $enum::SET_TIME_TWO_HOURS->value,
        ];
    }

    public function getAccessToProtectedMethodAndInvoke(string $method, object $mockClass)
    {
        $reflection = new ReflectionClass($mockClass);
        $reflectionMethod = $reflection->getMethod($method);
        $reflectionMethod->setAccessible(true);
        $result = $reflectionMethod->invoke($mockClass);
        return $result;
    }

    public function getValueOfProtectedProperty(string $getProperty, object $class)
    {
        $reflection = new ReflectionClass($class);
        $property = $reflection->getProperty($getProperty);
        $property->setAccessible(true);
        return $property->getValue($class);
    }

    public function setValueToProtectedProperty(string $getProperty, object $class, $value = null)
    {
        $reflection = new ReflectionClass($class);
        $property = $reflection->getProperty($getProperty);
        $property->setAccessible(true);

        if ($value !== null) {
            $property->setValue($class, $value);
            if (gettype($value) !== gettype($property->getValue($class))) {
                throw new \BadMethodCallException("Property " . $property . "тип свойства не соответствует типу аргумента");
            }
        }

        return $property->getValue($class);
    }

    /**
     * Get array from cache
     * @return array
     */
    protected function getBackMenuArray(int $adminId): ?array
    {
        if (empty($this->admin)) {
            throw new \Exception("Admin not found");
        }
        $cacheKey = "back_menu_" . $adminId;
        return json_decode(Cache::get($cacheKey), true);
    }


    public function getBackMenuCacheKey(int $adminId): string
    {
        if (empty($this->admin)) {
            throw new \Exception("Admin not found");
        }
        return "back_menu_" . $adminId;
    }

    public function forgetBackMenuArray(int $adminId)
    {
        $cacheKey = $this->getBackMenuCacheKey($adminId);
        Cache::forget($cacheKey);
    }

    protected function setBackMenuArrayToCache(array $backMenuArray, int $adminId)
    {
        $cacheKey = $this->getBackMenuCacheKey($adminId);
        Cache::put($cacheKey, json_encode($backMenuArray));
    }

    /**
     * Get private chat bot service that build based on a request data with admin id from database
     * and pass it to the container
     * @param int $adminsCount
     * @param int $chatsCount
     * @return void
     */
    protected function setPrivateChatBotService(int $adminsCount = 1, int $chatsCount = 1, int $admin = null): void
    {
        $this->fakeSendMessageSucceedResponse(); // for fake request for admins 
        (new SimpleSeeder())->run($adminsCount, $chatsCount);
        $this->admin = Admin::first();
        $this->chat = Chat::first();


        $adminId = $admin ?? $this->admin->admin_id;
        $this->data = $this->getPrivateChatMessage($adminId);
        $this->requestModel = (new TelegramRequestModelBuilder($this->data))->create();
        $this->botService = new TelegramBotService(($this->requestModel)->create());

        app()->singleton("botService", fn() => $this->botService);
        app()->singleton("requestModel", fn() => $this->requestModel);
    }

    protected function assertLastChatIdWasCached(int $adminId, int $chatId): void
    {
        $this->assertSame($chatId, $this->getLastSelectedChatIdFromCache($adminId));
    }


    /**
     * Seed admin to database with attached chats
     * @param mixed $adminsCount
     * @param mixed $chatsCount
     * @return \App\Models\Admin
     */
    protected function setAdminWithMultipleChats($chatsCount = 1): Admin
    {
        (new SimpleSeeder())->run(1, $chatsCount);
        return Admin::first();
    }



    protected function mockBotServiceGetPrivateChatCommandMethod(string $command, $mockBotService): void
    {
        $mockBotService->expects($this->any())
            ->method('getPrivateChatCommand')
            ->willReturn($command);
    }


    protected function getAdminChatsButtons(Admin $admin): array
    {
        return (new Buttons())->getSelectChatButtons(
            $admin->chats()->pluck("chat_title")->toArray(),
        );
    }

    protected function getFiltersSettingsButtons(): array
    {
        return (new Buttons())->getFiltersSettingsButtons();
    }

    protected function assertBackMenuArrayContains(int $adminId, array $array)
    {
        $backMenuArray = $this->getBackMenuArray($adminId);
        foreach ($array as $value) {
            $this->assertTrue(in_array($value, $backMenuArray));
        }
    }

    protected function assertBackMenuArrayNotContains(int $adminId, string $value)
    {
        $backMenuArray = $this->getBackMenuArray($adminId);
        $this->assertFalse(in_array($value, $backMenuArray));
    }
}






