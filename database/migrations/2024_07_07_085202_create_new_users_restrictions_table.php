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
        Schema::create('new_users_restrictions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("chat_id");
            $table->boolean("restrict_new_users")->default(0);
            $table->boolean("can_send_messages")->default(0);
            $table->boolean("can_send_media")->default(0);
            $table->tinyInteger("restriction_time")->default(0);


            $table->timestamps();
            // Foreign key is an unsigned big integer so it can't to be directly referenced to chat_id in chats
            // that is bigInteger type and the value may be signed.
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
