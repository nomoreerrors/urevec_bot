<?php

namespace App\Services;

use App\Classes\ModerationSettings;
use App\Classes\ReplyKeyboardMarkup;
use App\Exceptions\UnknownChatException;
use App\Models\Admin;
use App\Models\MessageModels\TextMessageModel;
use App\Models\TelegramRequestModelBuilder;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Enums\COMMAND;

class PrivateChatCommandService extends BotCommandService
{
    private $admin;

    private $moderationSettings;

    protected TextMessageModel $requestModel;

    public function __construct()
    {
        $this->requestModel = app("requestModel");
        $this->admin = Admin::where('admin_id', $this->requestModel->getChatId())->first();
        $this->moderationSettings = new ModerationSettings();
        parent::__construct();
        $this->selectChat();
    }


    protected function determineBotCommand(): void
    {
        switch ($this->command) {
            case "/start":
                $this->startHandler();
            case CONSTANTS::MODERATION_SETTINGS_CMD:
                $this->moderationSettings->send();
                break;
            case CONSTANTS::NEW_USERS_RESTRICT_SETTINGS_CMD:
                $this->moderationSettings->sendNewUsersRestrictionsSettings();
                break;
            // case CONSTANTS::RESTRICT_NEW_USERS_FOR_2H_CMD:
            //     $this->cacheRestriction(CONSTANTS::HOUR * 2);
            //     break;
            // case CONSTANTS::RESTRICT_NEW_USERS_FOR_24H_CMD:
            //     $this->cacheRestriction(CONSTANTS::DAY);
            //     break;
            // case CONSTANTS::RESTRICT_NEW_USERS_FOR_1W_CMD:
            //     $this->cacheRestriction(CONSTANTS::WEEK);
            //     break;
            // case CONSTANTS::RESTRICT_NEW_USERS_FOR_MONTH_CMD:
            //     $this->cacheRestriction(CONSTANTS::MONTH);
            //     break;
            // case CONSTANTS::STOP_RESTRICT_NEW_MEMBERS_CMD:
            //     $this->cacheRestriction(0);
            //     break;
            // case CONSTANTS::FILTER_SETTINGS_CMD:
            //     // Handle filter settings command
            //     break;
            // case CONSTANTS::BAN_SETTINGS_CMD:
            //     // Handle ban settings command
            //     break;
            default:
                app("botService")->sendMessage("Неизвестная команда");
                log::info("Неизвестная команда в приватном чате" . $this->command);
                response(CONSTANTS::UNKNOWN_CMD, Response::HTTP_OK);
        }
    }


    /**
     * Send the list of available chats to user as buttons
     * @return void
     */
    private function selectChat(): void
    {
        $chatTitles = $this->admin->chats->pluck('chat_title')->toArray();
        $replyMarkup = new ReplyKeyboardMarkup();

        $i = 0;
        foreach ($chatTitles as $title) {
            $replyMarkup->addRow()
                ->addButton($title);
            if (($i + 1) % 2 == 0) {
                $replyMarkup->addRow();
            }
            $i++;
        }

        $keyBoard = $replyMarkup->get();
        app("botService")->sendMessage("Select chat", $keyBoard);
    }


    private function startHandler(): Response
    {
        if ($this->admin->chats->count() > 1) {
            $this->selectChat();
        }
        $this->moderationSettings->send();
        return response("OK", Response::HTTP_OK);
    }


    protected function checkUserAccess(): void
    {
        if (empty($this->admin)) {
            $error = "403: Request to a bot private chat from unknown user." . $this->requestModel->getChatId();
            log::info($error);

            app("botService")->sendMessage("Добавьте бота в свою группу и назначьте администратором,
             чтобы активировать работу.");
            throw new UnknownChatException($error, __METHOD__);
        }
        return;
    }

}

