<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sensor_readings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cat_id')->constrained()->cascadeOnDelete();
            $table->float('temperature'); // e.g. 38.5
            $table->unsignedInteger('bpm');
            $table->enum('activity', ['low', 'medium', 'high'])->default('medium');
            $table->enum('source', ['direct_api', 'telegram', 'mock'])->default('mock');
            $table->timestamp('read_at')->useCurrent();
            $table->timestamps();

            $table->index(['cat_id', 'read_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sensor_readings');
    }
};
