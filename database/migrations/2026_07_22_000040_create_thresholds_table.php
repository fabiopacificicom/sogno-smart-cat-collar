<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('thresholds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cat_id')->nullable()->constrained()->nullOnDelete(); // null = global default
            $table->enum('vital', ['temperature', 'bpm']);
            $table->float('warning_value');
            $table->float('critical_value');
            $table->timestamps();

            $table->unique(['cat_id', 'vital']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('thresholds');
    }
};
