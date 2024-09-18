<?php

namespace App\Classes;

use App\Exceptions\BaseTelegramBotException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Exception;

class CommandBuilder
{
    private array $commandsArray = ["commands" => []];

    private bool $scope = false;

    /**
     *  Methods calling order
     *   $command = (new CommandBuilder($chatId))
     *      ->command("command", "description")
     *      ->withDefaultScope()
     *     ->get();
     * 
     * https://core.telegram.org/bots/api#botcommandscope
     */
    /**
     * @param int $chat_id can be Chat id or User id
     */
    public function __construct(private int $chatId)
    {
    }

    /**
     * Add a new command to commandsArray
     * @return CommandBuilder
     */
    public function command(string $command, string $description): static
    {
        $this->commandsArray["commands"][] = ["command" => $command, "description" => $description];
        return $this;
    }

    /**
     * Only for private chat with bot for specific user or admin
     * Use it if you want to manage commands visibility for different types of users
     * @return ReplyKeyboardMarkup
     */
    public function withChatScope(): static
    {
        if ($this->scope === true) {
            throw new BaseTelegramBotException("МЕТОД НЕ МОЖЕТ БЫТЬ ВЫЗВАН ПОВТОРНО", __METHOD__);
        }
        if (empty($this->commandsArray["commands"])) {
            throw new BaseTelegramBotException("No commands added before setting scope", __METHOD__);
        }
        $this->scope = true;
        $this->commandsArray["scope"] = [
            "type" => "chat",
            "chat_id" => $this->chatId
        ];
        return $this;
    }

    /**Add scope type */
    public function withDefaultScope(): static
    {
        if ($this->scope === true) {
            throw new BaseTelegramBotException("МЕТОД НЕ МОЖЕТ БЫТЬ ВЫЗВАН ПОВТОРНО", __METHOD__);
        }
        $this->scope = true;
        $this->commandsArray["scope"] = [
            "type" => "default",
            "chat_id" => $this->chatId
        ];
        return $this;
    }

    /**Add scope type */
    public function withAllPrivateChatsScope(): static
    {
        if ($this->scope === true) {
            throw new BaseTelegramBotException("МЕТОД НЕ МОЖЕТ БЫТЬ ВЫЗВАН ПОВТОРНО", __METHOD__);
        }
        $this->scope = true;
        $this->commandsArray["scope"] = [
            "type" => "all_private_chats",
            "chat_id" => $this->chatId
        ];
        return $this;
    }

    /**Add scope type */
    public function withAllGroupChatsScope(): static
    {
        if ($this->scope === true) {
            throw new BaseTelegramBotException("МЕТОД НЕ МОЖЕТ БЫТЬ ВЫЗВАН ПОВТОРНО", __METHOD__);
        }
        $this->scope = true;
        $this->commandsArray["scope"] = [
            "type" => "all_group_chats",
            "chat_id" => $this->chatId
        ];
        return $this;
    }

    /**Add scope type */
    public function withAllChatAdministratorsScope(): static
    {
        if ($this->scope === true) {
            throw new BaseTelegramBotException("МЕТОД НЕ МОЖЕТ БЫТЬ ВЫЗВАН ПОВТОРНО", __METHOD__);
        }
        $this->scope = true;
        $this->commandsArray["scope"] = [
            "type" => "all_chat_adminiistrators",
            "chat_id" => $this->chatId
        ];
        return $this;
    }


    /**Add scope type */
    public function withChatAdministratorsScope(): static
    {
        if ($this->scope === true) {
            throw new BaseTelegramBotException("МЕТОД НЕ МОЖЕТ БЫТЬ ВЫЗВАН ПОВТОРНО", __METHOD__);
        }

        $this->scope = true;
        $this->commandsArray["scope"] = [
            "type" => "chat_administrators",
            "chat_id" => $this->chatId
        ];
        return $this;
    }

    /**Add scope type */
    public function withChatMemberScope(): static
    {
        if ($this->scope === true) {
            throw new BaseTelegramBotException("МЕТОД НЕ МОЖЕТ БЫТЬ ВЫЗВАН ПОВТОРНО", __METHOD__);
        }

        $this->scope = true;
        $this->commandsArray["scope"] = [
            "type" => "chat_member",
            "chat_id" => $this->chatId
        ];
        return $this;
    }



    public function get(): array
    {
        $this->exceptionHandler();
        return $this->commandsArray;
    }


    private function exceptionHandler(): void
    {
        if (empty($this->commandsArray["commands"])) {
            throw new BaseTelegramBotException("НЕВЕРНЫЙ ПОРЯДОК МЕТОДОВ ИЛИ МЕТОД SCOPE ВЫЗВАН ПОВТОРНО.  ПРАВИЛЬНЫЙ ВЫЗОВ:  .
             (new CommandBuilder())->command(command, description)->withChatScope()->get()", __METHOD__);
        }
    }

}
