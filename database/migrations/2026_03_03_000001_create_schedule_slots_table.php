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
        Schema::create('schedule_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('university_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('period_id')
                ->constrained()
                ->restrictOnDelete();
            $table->foreignId('clinic_id')
                ->constrained('clinics')
                ->restrictOnDelete();
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');
            $table->unsignedInteger('available_slots');
            $table->boolean('allow_student_booking')->default(false);
            $table->boolean('allow_student_enrollment')->default(false);
            $table->boolean('allow_procedure_booking')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['university_id', 'date']);
            $table->index(['clinic_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedule_slots');
    }
};
