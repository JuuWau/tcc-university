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
        Schema::create('student_reasons', function (Blueprint $table) {
            $table->id();

            $table->enum('type', [
                'leave_of_absence',
                'transfer',
                'withdrawal',
                'graduation',
                'delinquency',
                'administrative',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_reasons');
    }
};
