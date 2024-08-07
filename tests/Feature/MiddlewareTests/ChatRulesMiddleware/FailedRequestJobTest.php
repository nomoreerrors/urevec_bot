<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class FailedRequestJobTest extends TestCase
{
    use RefreshDatabase;

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
