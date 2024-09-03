<?php

namespace App\Classes\Commands;


class BadWordsFilterCommand extends FilterCommand
{
    protected function handle(): void
    {
        parent::handle();
        switch ($this->command) {
            case $this->enum::ADD_WORDS->value:
                $this->addBadWords();
                break;
            case $this->enum::DELETE_WORDS->value:
                $this->deleteBadWords();
                break;
            case $this->enum::GET_WORDS->value:
                $this->getBadWords();
                break;
        }
    }

    protected function addBadWords(): void
    {
        // TODO: Implement addBadWords() method.
    }

    protected function deleteBadWords(): void
    {
        // TODO: Implement deleteBadWords() method.
    }

    protected function getBadWords(): void
    {
        // TODO: Implement getBadWords() method.
    }
}

