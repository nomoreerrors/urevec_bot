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
            $table->bigInteger("chat_id");
            $table->boolean("restrict_new_users")->default(0);
            $table->boolean("can_send_messages")->default(0);
            $table->boolean("can_send_media")->default(0);
            $table->tinyInteger("restriction_time")->default(0);


            $table->timestamps();
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
