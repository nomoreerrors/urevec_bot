<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\ResTime;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('links_filter', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("chat_id");
            $table->boolean("enabled")->default(1);
            $table->boolean("delete_user")->default(0);
            $table->boolean("restrict_user")->default(1);
            $table->boolean("delete_message")->default(1);
            $table->boolean("can_send_messages")->default(0);
            $table->boolean("can_send_media")->default(0);
            $table->tinyInteger("restriction_time")->default(ResTime::TWO_HOURS->value);
            $table->timestamps();


            $table->foreign('chat_id')->references('id')->on('chats')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bad_words_filter');
    }
};
