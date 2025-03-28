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
        Schema::create('user_contents', function (Blueprint $table) {
            $table->id();
            $table->uuid('users_id');
            $table->foreign('users_id')->on('users')->references('id')->onDelete('cascade');
            $table->foreignId('contents_id')->constrained('contents')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_contents');
    }
};
