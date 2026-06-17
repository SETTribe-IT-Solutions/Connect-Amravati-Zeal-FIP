<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('mobile', 15)->nullable();
            $table->string('designation', 100)->nullable();
            $table->foreignId('district_id')->nullable()->constrained()->onDelete('restrict');
            $table->foreignId('taluka_id')->nullable()->constrained()->onDelete('restrict');
            $table->foreignId('village_id')->nullable()->constrained()->onDelete('restrict');
            $table->string('status', 20)->default('Active'); // Active, Inactive
            $table->rememberToken();
            $table->timestamps();

            $table->index(['email', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
