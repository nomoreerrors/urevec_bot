<?php

namespace App\Services;

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

abstract class BotCommandService
{
    protected $command;

    protected TextMessageModel $requestModel;

    public function __construct()
    {
        $this->requestModel = app("requestModel");
        $this->command = $this->requestModel->getText();
        $this->checkUserAccess();
        $this->determineBotCommand();
    }

    abstract protected function checkUserAccess(): void;

    abstract protected function determineBotCommand(): void;
}
//TODO: Все готово. Осталось решить, как отправить успешное сообщение себе в чат.
// То, что выбор чата и moderation settings наслаиваются друг на друга — нормально —
// ведь я пока не убрал выбор чата, если у админа он один.
//TODO Выбираем нужный чат и чат устанавливается в botservice, присылаем модератион сеттингс.
