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
    Schema::table('bookings', function (Blueprint $table) {
      if (!Schema::hasColumn('bookings', 'remarks')) {
        $table->string('remarks')->nullable();
      }

      if (!Schema::hasColumn('bookings', 'returnDate')) {
        $table->date('returnDate')->nullable();
      }

      if (!Schema::hasColumn('bookings', 'nowServingAt')) {
        $table->timestamp('nowServingAt')->nullable();
      }
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    //
  }
};