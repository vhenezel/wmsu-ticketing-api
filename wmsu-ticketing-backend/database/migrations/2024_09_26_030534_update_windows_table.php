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
        Schema::table('windows', function (Blueprint $table) {
            $table->unsignedBigInteger('assigned_id')->nullable()->change();
            $table->string('method')->nullable()->default('Online')->change();
            $table->string('status')->nullable()->default('Online')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('windows', function (Blueprint $table) {
            // Reverse the changes
            $table->unsignedBigInteger('assigned_id')->nullable(false)->change();
            $table->string('method')->nullable(false)->default(null)->change();
            $table->string('status')->nullable(false)->default(null)->change();
        });
    }
};
