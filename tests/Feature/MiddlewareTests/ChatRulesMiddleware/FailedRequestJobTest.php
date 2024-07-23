<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class FailedRequestJobTest extends TestCase
{
    use RefreshDatabase;
    /**
     * Test that failed requests with an unknown chat ID are not dispatched to the queue.
     */
    public function testFailedRequestWithUnknownChatIdIsNotDispatchedToQueue(): void
    {
        $requestData = $this->getMessageModelData();
        $requestData['message']['chat']['id'] = $this->getUnknownChatId();
        $updateId = $requestData['update_id'];

        $this->withServerVariables(['REMOTE_ADDR' => '127.5.5.1']) //unknown ip address throws exception
            ->post('api/webhook', $requestData)
            ->assertStatus(200);

        //jobs table must be empty or object with current update_id must not exists before running test or test will fail
        $result = DB::table('jobs')
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

        $result = DB::table('jobs')
            ->where('payload', 'like', '%' . $updateId . '%')
            ->first();

        $this->assertNotNull($result);
    }
}
