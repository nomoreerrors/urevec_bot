<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\Log;

class WebHookHandlerTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_if_message_contains_link_ban_user(): void
    {
        // foreach ($this->testObjects as $object) {
        //     $this->service->data = $object;
        //     $this->service->checkMessageType();

        //     $hasLink = $this->service->linksFilter();
        //     // log::info($this->service->linksFilter());
        //     if ($hasLink) {


        //         $response = $this->post("api/webhook", $object);
        //         // dd($response['status']);
        //         dd($response);
        // }
        // }
    }
}
