<?php

use Illuminate\Database\Migrations\Migration;
use App\Enums\ResTime;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('unusual_chars_filter', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('chat_id');
            $table->boolean("filter_enabled")->default(1);
            $table->boolean("delete_user")->default(0);
            $table->boolean("restrict_user")->default(1);
            $table->boolean("delete_message")->default(1);
            $table->boolean("dasable_send_messages")->default(1);
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
        Schema::dropIfExists('new_users_restrictions');
    }
};
