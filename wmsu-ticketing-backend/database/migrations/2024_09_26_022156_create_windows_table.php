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
        Schema::create('windows', function (Blueprint $table) {
            $table->id();
            $table->integer('window_number');
            $table->unsignedBigInteger('assigned_id');
            $table->foreign(columns: 'assigned_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->string('type');
            $table->string('method');
            $table->string('status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('windows');
    }
};