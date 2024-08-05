<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    /**
     * Table that contains chats and administrators relationships
     */
    public function up(): void
    {
        Schema::create('chat_admins', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger("admin_id");
            $table->bigInteger("chat_id");
            $table->boolean('private_commands_access')->default(0);
            $table->boolean('group_commands_access')->default(0);
            $table->boolean('my_commands_set')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_admins');
    }
};
