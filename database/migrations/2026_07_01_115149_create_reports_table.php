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
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('neighborhood_id')->constrained('neighborhoods')->cascadeOnDelete();
            $table->enum('status', ['available', 'low', 'none', 'scheduled']);
            $table->text('notes');
            $table->timestamp('reported_at')->useCurrent();
            $table->boolean('verified')->default(false);

            $table->index('neighborhood_id', 'idx_reports_neighborhood');
            $table->index('reported_at', 'idx_reports_reported_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
