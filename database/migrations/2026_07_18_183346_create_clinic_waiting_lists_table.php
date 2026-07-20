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
        Schema::create('clinic_waiting_lists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('clinic_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->timestamp('enrolled_at');
            $table->timestamps();
            $table->unique(['patient_id', 'clinic_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clinic_waiting_lists');
    }
};
