<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('devil_words_filter_restrictions', function (Blueprint $table) {
            $table->id("chat_id");
            $table->boolean("filter_enabled")->default(1);
            $table->boolean("delete_messages")->default(1);
            $table->boolean("delete_users")->default(1);
            $table->boolean("cant_send_messages")->default(1);
            $table->tinyInteger("restriction_time")->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('devil_words_filter_restrictions');
    }
};
