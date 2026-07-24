<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cat_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['warning', 'critical', 'info']);
            $table->enum('vital', ['temperature', 'bpm', 'activity']);
            $table->string('value');
            $table->float('threshold', 6.2)->nullable();
            $table->text('message');
            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamps();

            $table->index(['cat_id', 'acknowledged_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alerts');
    }
};
