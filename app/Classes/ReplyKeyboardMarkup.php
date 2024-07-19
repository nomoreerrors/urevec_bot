<?php

namespace App\Classes;

use App\Exceptions\TelegramModelException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Exception;

class ReplyKeyboardMarkup
{
    private array $keyBoard = [];

    /**
     * Class constructor.
     */
    public function __construct()
    {
        $this->setKeyBoard();
    }

    private function setKeyBoard(): static
    {
        $this->keyBoard = [
            "keyboard" => [],
            "resize_keyboard" => true
        ];
        return $this;
    }

    public function addRow(): static
    {
        $this->keyBoard["keyboard"][] = [];
        return $this;
    }

    public function addButton(string $text): static
    {
        $this->keyBoard["keyboard"][count($this->keyBoard["keyboard"]) - 1][] = ["text" => $text];
        return $this;
    }

    public function get(): array
    {
        return $this->keyBoard;
    }

}
