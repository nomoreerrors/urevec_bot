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
        Schema::create('bad_words_filter', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("chat_id");
            $table->boolean("filter_enabled")->default(1);
            $table->boolean("delete_user")->default(0);
            $table->boolean("restrict_user")->default(1);
            $table->boolean("delete_message")->default(1);
            $table->boolean("dasable_send_messages")->default(1);
            $table->tinyInteger("restriction_time")->default(ResTime::TWO_HOURS->value);
            $table->text("bad_words")->nullable();
            $table->text("bad_phrases")->nullable();
            $table->text("critical_words")->nullable();
            $table->text("critical_phrases")->nullable();
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
