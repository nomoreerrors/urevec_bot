<?php

namespace App\Classes;

use App\Exceptions\BaseTelegramBotException;
use App\Services\BotErrorNotificationService;
use App\Models\Admin;
use App\Services\TelegramBotService;
use App\Models\Chat;

class ChatBuilder
{
    private ?Chat $chat = null;
    /**
     * All existed relationships models names
     * @var array
     */
    private array $chatRelationsNames = [];

    public function __construct(private TelegramBotService $botService)
    {
        $this->setChatRelationsNames();
    }

    public function createChat(): void
    {
        $this->chat = $this->findChat();

        // BotErrorNotificationService::send("tgcontroller");
        if (empty($this->chat)) {
            $this->chat = Chat::create([
                "chat_id" => $this->botService->getRequestModel()->getChatId(),
                "chat_title" => $this->botService->getRequestModel()->getChatTitle(),
            ]);
            $this->createChatAdmins();
            $this->setMyCommands();

        }

        $this->updateChatRelations();
    }

    protected function setMyCommands()
    {
        $commands = [
            [
                "command" => "moderation_settings",
                "description" => "TEST 234!!! Configure bot moderation settiings"
            ],
            [
                "command" => "David Gale",
                "description" => "K-Pax"
            ],
        ];

        BotErrorNotificationService::send($this->chat->admins()->first()->admin_id);
        foreach ($this->chat->admins()->get() as $admin) {
            $this->botService->privateChatCommandRegister()->setMyCommands($admin->admin_id, $commands);
        }
    }


    /**
     * Create or update admins of a new created chat and attach them to the chat 
     * @return void
     */
    protected function createChatAdmins(): void
    {
        if (!empty($this->chat->admins()->first())) {
            return;
        }

        foreach ($this->botService->getRequestModel()->getAdmins() as $admin) {
            $adminModel = Admin::where('admin_id', $admin['admin_id'])->exists()
                ? Admin::where('admin_id', $admin['admin_id'])->first()
                : Admin::create($admin);
            $adminModel->chats()->attach($this->chat->id);
        }
    }

    /**
     *Create or update new added relations models in DB if they don't exist yet
     * @param int $chatId
     * @return void
     */
    public function updateChatRelations(): void
    {
        $relations = $this->getChatRelationsNames();

        foreach ($relations as $relation) {
            $this->createChatRelation($relation);
        }
    }

    protected function createChatRelation(string $relation): void
    {
        if (empty($this->chat->{$relation})) {
            $this->chat->{$relation}()->create();
        }
    }

    /**
     * Summary of findChat
     * @return Chat|object|\Illuminate\Database\Eloquent\Model|null
     */
    protected function findChat(): ?Chat
    {
        $result = Chat::where("chat_id", $this->botService->getRequestModel()->getChatId())->first();
        return $result;
    }


    public function getChatRelationsNames(): array
    {
        return $this->chatRelationsNames;
    }

    public function setChatRelationsNames(): void
    {
        $this->chatModels = Chat::getDefinedRelationsNames();
    }

}
