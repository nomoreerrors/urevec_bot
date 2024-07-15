<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class FailedRequestJobTest extends TestCase
{
    /**
     * Test that failed requests with an unknown chat ID are not dispatched to the queue.
     */
    public function testFailedRequestWithUnknownChatIdIsNotDispatchedToQueue(): void
    {
        $requestData = $this->getMessageModel()->getData();
        $requestData['message']['chat']['id'] = $this->getUnknownChatId();
        $updateId = $requestData['update_id'];

        $this->withServerVariables(['REMOTE_ADDR' => '127.5.5.1']) //unknown ip address throws exception
            ->post('api/webhook', $requestData)
            ->assertStatus(200);

        $result = DB::table('test_jobs')
            ->where('payload', 'like', '%' . $updateId . '%')
            ->first();

        $this->assertNull($result);
    }


    public function testFailedRequestIsDispatchedToQueue(): void
    {
        $requestData = $this->getUnknownObject();
        $updateId = $requestData['update_id'];

        $this->post('api/webhook', $requestData)
            ->assertStatus(200);

        $result = DB::table('test_jobs')
            ->where('payload', 'like', '%' . $updateId . '%')
            ->first();

        $this->assertNotNull($result);
    }
}
