<?php

namespace App\Jobs;

use App\Models\TelegramRequestModelBuilder;
use App\Models\FailedRequestModel;
use App\Models\PhotoMediaModel;
use App\Models\VideoMediaModel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Http\Request;
use Illuminate\Queue\SerializesModels;

class FailedRequestJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(protected array $data)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Http::post(env("TELEGRAM_API_WEBHOOK_URL"), $this->data);
    }
}
