<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('clinic_specialty', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')
                ->constrained('clinics')
                ->cascadeOnDelete();
            $table->foreignId('specialty_id')
                ->constrained('specialties')
                ->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        DB::statement("
            CREATE UNIQUE INDEX clinic_specialty_unique_active
            ON clinic_specialty (clinic_id, specialty_id)
            WHERE deleted_at IS NULL
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clinic_specialty');
    }
};
