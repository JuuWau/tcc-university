<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Restore Laravel cache tables (used when CACHE_STORE=database).
     */
    public function up(): void {}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Schema::dropIfExists('cache');
        // Schema::dropIfExists('cache_locks');
    }
};
