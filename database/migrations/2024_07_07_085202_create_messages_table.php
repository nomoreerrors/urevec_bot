<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->integer("message_id");
            $table->integer("chat_id");
            $table->integer("from_user_id");
            $table->string("user_name", 100);
            $table->integer("public_date");
            $table->string("text");
            $table->smallInteger("likes_count");
            $table->tinyInteger("dislikes_count");
            $table->boolean("is_banned");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
