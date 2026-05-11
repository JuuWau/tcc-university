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
        Schema::create('schedule_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_slot_id')
                ->constrained('schedule_slots')
                ->cascadeOnDelete();
            $table->foreignId('student_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->enum('status', [
                'active',
                'cancelled',
                'missed',
                'attended',
            ])->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['schedule_slot_id', 'student_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedule_enrollments');
    }
};
