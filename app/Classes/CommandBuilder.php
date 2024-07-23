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
     *   $command = (new Command("command", "description"))
     *      ->withScope()
     *      ->default()
     *      ->addChatId($id) 
     *     ->get();
     * 
     * https://core.telegram.org/bots/api#botcommandscope
     */
    public function __construct()
    {
    }

    /**
     * Add a new command to commandsArray
     * @return CommandBuilder
     */
    public function command(string $command, string $description): static
    {
        // array_push($this->commandsArray["commands"], ["command" => $command, "description" => $description]);
        $this->commandsArray["commands"][] = ["command" => $command, "description" => $description];
        return $this;
    }

    /**
     * Add scope array. Required scope type and chatId
     * Use it if you want to manage commands visibility for different types of users
     * @return ReplyKeyboardMarkup
     */
    public function withScope(): static
    {
        $this->scope = true;
        $this->commandsArray["scope"] = [];
        return $this;
    }

    private function exceptionHandler(): void
    {
        if ($this->scope === false) {
            throw new BaseTelegramBotException("НЕВЕРНЫЙ ПОРЯДОК МЕТОДОВ. ПРАВИЛЬНЫЙ ВЫЗОВ:
             (new Command(command, description))->withScope->(type)->addChatId(id)->get()", __METHOD__);
        }
    }

    /**Add scope type */
    public function default(): static
    {

        $this->exceptionHandler();

        $this->commandsArray["scope"]["type"] = "default";
        return $this;
    }

    /**Add scope type */
    public function allPrivateChats(): static
    {
        $this->exceptionHandler();
        $this->commandsArray["scope"]["type"] = "all_private_chats";
        return $this;
    }

    /**Add scope type */
    public function allGroupChats(): static
    {
        $this->exceptionHandler();
        $this->commandsArray["scope"]["type"] = "all_group_chats";
        return $this;
    }

    /**Add scope type */
    public function allChatAdministrators(): static
    {
        $this->exceptionHandler();
        $this->commandsArray["scope"]["type"] = "all_chat_administrators";
        return $this;
    }

    /**Add scope type */
    public function chat(): static
    {
        $this->exceptionHandler();
        $this->commandsArray["scope"]["type"] = "chat";
        return $this;
    }

    /**Add scope type */
    public function chatAdministrators(): static
    {
        $this->exceptionHandler();
        $this->commandsArray["scope"]["type"] = "chat_administrators";
        return $this;
    }

    /**Add scope type */
    public function chatMember(): static
    {
        $this->exceptionHandler();
        $this->commandsArray["scope"]["type"] = "chat_member";
        return $this;
    }

    /**
     * Required option
     * Add chatId key to scope array
     * @param int $id
     * @return CommandBuilder
     */
    public function addChatId(int $id): static
    {
        $this->exceptionHandler();
        $this->commandsArray["scope"]["chat_id"] = $id;
        return $this;
    }

    public function get(): array
    {
        $this->exceptionHandler();
        return $this->commandsArray;
    }
}
