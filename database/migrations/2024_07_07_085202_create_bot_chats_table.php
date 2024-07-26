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
        Schema::create('bot_chats', function (Blueprint $table) {
            $table->id();
            $table->bigInteger("chat_id");
            $table->string("chat_title", 100)->default("unset");
            $table->string("chat_admins", 200)->default("[]");
            $table->string("private_commands_access", 200)->default("unset");
            $table->string("group_commands_access", 200)->default("unset");
            $table->boolean("my_commands_set")->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bot_chats');
    }
};
