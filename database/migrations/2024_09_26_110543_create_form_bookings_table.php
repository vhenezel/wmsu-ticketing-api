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
        Schema::create('form_bookings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bookingId');
            $table->foreign(columns: 'bookingId')->references('id')->on('bookings')->onDelete('cascade')->onUpdate('cascade');
            $table->unsignedBigInteger('formId');
            $table->foreign(columns: 'formId')->references('id')->on('forms')->onDelete('cascade')->onUpdate('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_bookings');
    }
};
