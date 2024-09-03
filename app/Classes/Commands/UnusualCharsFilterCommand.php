<?php

namespace App\Classes\Commands;

class UnusualCharsFilterCommand extends FilterCommand
{
    protected function handle(): void
    {
        parent::handle();
        switch ($this->command) {
            // Additional cases
        }
    }

}
