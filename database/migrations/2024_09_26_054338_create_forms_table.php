<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('forms', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        DB::table('forms')->insert([
            [
                'name' => 'Transcript of Record',
                'description' => 'Lorem ipsum dolor sit amet consectetu. Suspendisse suspendisse tempor ipsum sit egestas nunc.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Diploma',
                'description' => 'Lorem ipsum dolor sit amet consectetu. Suspendisse suspendisse tempor ipsum sit egestas nunc.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Certifications',
                'description' => 'Lorem ipsum dolor sit amet consectetu. Suspendisse suspendisse tempor ipsum sit egestas nunc.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'CEFT or HD',
                'description' => 'Certificate of Eligibility to Transfer (CEFT) or Honorable Dismissal (HD)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Reproduce Diploma',
                'description' => 'Lorem ipsum dolor sit amet consectetu. Suspendisse suspendisse tempor ipsum sit egestas nunc.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'CAV for Endorsement to DFA',
                'description' => 'Certification, Authentication, Verification (CAV) for endorsement to DFA',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Completion Form',
                'description' => 'Verification for INC (Incomplete) Grades.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Authentication of Documents',
                'description' => 'Lorem ipsum dolor sit amet consectetu. Suspendisse suspendisse tempor ipsum sit egestas nunc.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Evaluation of Grades',
                'description' => 'Lorem ipsum dolor sit amet consectetu. Suspendisse suspendisse tempor ipsum sit egestas nunc.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('forms');
    }
};
