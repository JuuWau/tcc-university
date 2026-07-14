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
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_enrollment_id')
                ->constrained('schedule_enrollments')
                ->cascadeOnDelete();
            $table->foreignId('patient_id')
                ->constrained('patients')
                ->cascadeOnDelete();
            $table->foreignId('student_id')
                ->constrained('students')
                ->cascadeOnDelete();
             $table->foreignId('procedure_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->dateTime('scheduled_start_at');
            $table->dateTime('scheduled_end_at');
            $table->enum('status', [
                'scheduled',
                'confirmed',
                'completed',
                'canceled',
                'no_show',
                'rescheduled'
            ])->default('scheduled');
            $table->text('notes')->nullable();
            $table->dateTime('started_at')->nullable();
            $table->dateTime('finished_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['student_id', 'scheduled_start_at', 'scheduled_end_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
