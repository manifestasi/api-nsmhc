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
        Schema::create('user_husbands', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('users_id');
            $table->foreign('users_id')->on('users')->references('id')->onDelete('cascade');
            $table->string('name', 100);
            $table->integer('age');
            $table->string('last_education');
            $table->string('last_job');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_husbands');
    }
};
