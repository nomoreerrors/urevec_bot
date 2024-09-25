<?php

namespace App\Classes;

use App\Services\BotErrorNotificationService;
use App\Services\CONSTANTS;
use App\Models\Admin;
use App\Exceptions\SetCommandsFailedException;
use App\Exceptions\BaseTelegramBotException;
use App\Services\TelegramBotService;
use App\Models\Chat;

/**
 * Register bot menu commands for users
 */
class PrivateChatCommandRegister
{
    private Chat $chat;

    public function __construct(private TelegramBotService $botService)
    {
        $this->chat = $this->botService->getChat();
    }
    /**
     * Setting menu commands for the bot in private and group chats.
     *
     * @return void
     * @throws BaseTelegramBotException
     */
    public function setMyCommands(int $adminId, array $commands): void
    {
        // $this->chat = $this->botService->getChat();
        $this->validateCommands($commands);
        $this->setPrivateChatCommands($adminId, $commands);

        $updatedCommands = $this->getMyCommands("chat", $adminId);

        $this->checkifCommandsAreSet($commands, $updatedCommands);
        $this->updatePrivateChatCommandsAccessColumn($adminId);
        $this->updateMyCommandsColumn($adminId);
    }

    /**
     * Validate the commands array
     *
     * @param array $commands
     * @return void
     * @throws BaseTelegramBotException
     */
    protected function validateCommands(array $commands): void
    {
        if (
            count(array_column($commands, 'command')) !== count($commands) ||
            count(array_column($commands, 'description')) !== count($commands)
        ) {
            throw new BaseTelegramBotException(CONSTANTS::SET_MY_COMMANDS_FAILED, __METHOD__);
        }
    }

    /**
     * Summary of updateMyCommandsColumnForAdmins
     * @param array|\App\Models\Admin $admins admin or array of admins
     * @return void
     */
    public function updateMyCommandsColumn(int $adminId): void
    {
        $result = $this->chat->admins()->where('admins.admin_id', $adminId)
            ->first()->pivot->update([
                "my_commands_set" => 1
            ]);

        if (!$result) {
            throw new BaseTelegramBotException(CONSTANTS::SET_MY_COMMANDS_FAILED, __METHOD__);
        }
    }


    public function getMyCommands(string $type, int $chatId): array
    {
        $scope = [
            "scope" => [
                "type" => $type,
                "chat_id" => $chatId
            ]
        ];

        $response = $this->botService->sendPost("getMyCommands", $scope);
        // dd($response->json());
        $result = $response->json();

        return $result["result"];
    }

    /**
     * Set bot commands visibility in a private chat for admins
     *
     * @param int $adminId
     * @return void
     * @throws BaseTelegramBotException
     */
    public function setPrivateChatCommands(int $adminId, array $commandsArray): void
    {
        if (empty($commandsArray)) {
            throw new BaseTelegramBotException(
                CONSTANTS::SET_MY_COMMANDS_FAILED,
                __METHOD__
            );
        }

        $commands = $this->buildCommands($adminId, $commandsArray);
        $response = $this->botService->sendPost("setMyCommands", $commands);

        if (!$response->ok()) {
            // BotErrorNotificationService::send(json_encode($response->json()));
            throw new BaseTelegramBotException(CONSTANTS::SET_PRIVATE_CHAT_COMMANDS_FAILED, __METHOD__);
        }
    }


    /**
     * update my commands column for all chat admins
     * @return void
     */
    protected function updatePrivateChatCommandsAccessColumn($adminId): void
    {
        $result = $this->chat->admins()->where('admins.admin_id', $adminId)
            ->first()->pivot->update([
                "private_commands_access" => 1
            ]);


        if (!$result) {
            throw new BaseTelegramBotException(CONSTANTS::SET_MY_COMMANDS_FAILED, __METHOD__);
        }
    }



    protected function buildCommands(int $adminId, array $commandsArray): array
    {
        $commandBuilder = new CommandBuilder($adminId);

        foreach ($commandsArray as $command) {
            $commandBuilder->command($command['command'], $command['description']);
        }

        $result = $commandBuilder->withChatScope()->get();

        return $result;
    }

    /**
     * Make sure that received commands list is the same as expected
     *
     * @param array $expectedCommands
     * @param array $actualCommands
     * @throws \App\Exceptions\BaseTelegramBotException
     * @return array
     */
    public function checkIfCommandsAreSet(array $expectedCommands, array $actualCommands): array
    {
        $result = [];
        if (count($expectedCommands) !== count($actualCommands)) {
            throw new SetCommandsFailedException($expectedCommands, $actualCommands);
        }

        foreach ($expectedCommands as $index => $expectedCommand) {
            $result = array_diff($actualCommands[$index], $expectedCommand);

            if (!empty($result)) {
                throw new SetCommandsFailedException($expectedCommands, $actualCommands);
            }
        }

        return $result;
    }


}
