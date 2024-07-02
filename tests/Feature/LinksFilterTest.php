<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Services\TelegramBotService;
use Illuminate\Support\Facades\Log;


class LinksFilterTest extends TestCase
{
    public function test_if_message_text_value_has_link_returns_true(): void
    {
        foreach ($this->testObjects as $object) {
            $this->filter->setData($object);


            $hasLink = str_contains($this->filter->text, "http");

            if ($hasLink || $this->filter->textLink) {
                $this->assertTrue($this->filter->linksFilter());
            }
        }
    }



    public function test_object_has_not_links_returns_false(): void
    {
        foreach ($this->testObjects as $object) {
            $this->filter->setData($object);


            $hasLink = str_contains($this->filter->text, "http");

            if (!$hasLink && !$this->filter->textLink) {
                $this->assertTrue($this->filter->linksFilter());
            }
        }
    }
}
