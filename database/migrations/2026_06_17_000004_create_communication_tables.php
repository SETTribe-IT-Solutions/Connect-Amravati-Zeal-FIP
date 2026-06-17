<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('receiver_id')->constrained('users')->onDelete('cascade');
            $table->text('content');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['sender_id', 'receiver_id']);
        });

        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('content');
            $table->string('target_role')->nullable();
            $table->foreignId('target_district_id')->nullable()->constrained('districts');
            $table->foreignId('target_taluka_id')->nullable()->constrained('talukas');
            $table->foreignId('target_village_id')->nullable()->constrained('villages');
            $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');
            $table->string('attachment_path')->nullable();
            $table->timestamps();
        });

        Schema::create('appreciations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recipient_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');
            $table->string('category');
            $table->text('message');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appreciations');
        Schema::dropIfExists('announcements');
        Schema::dropIfExists('messages');
    }
};
