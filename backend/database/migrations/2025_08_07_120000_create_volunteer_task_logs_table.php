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
        Schema::create('volunteer_task_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('tasks')->onDelete('cascade');
            $table->foreignId('volunteer_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('disaster_id')->nullable()->constrained('disasters')->onDelete('set null');
            $table->enum('status', ['assigned', 'accepted', 'started', 'ended', 'verified', 'abandoned', 'failed', 'no_show'])->default('assigned');
            $table->timestamp('check_in')->nullable()->comment('Admin-confirmed task start');
            $table->timestamp('check_out')->nullable()->comment('Admin-confirmed task end (nullable)');
            $table->timestamp('expected_end')->nullable()->comment('Expected task completion date');
            $table->foreignId('start_verified_by')->nullable()->constrained('users')->onDelete('set null')->comment('Admin who performed check-in');
            $table->foreignId('end_verified_by')->nullable()->constrained('users')->onDelete('set null')->comment('Admin who confirmed task end (nullable)');
            $table->enum('report', ['normal', 'early_exit', 'abandoned', 'no_show'])->default('normal');
            $table->timestamps();

            // Indexes for better query performance
            $table->index(['task_id']);
            $table->index(['volunteer_id', 'status']);
            $table->index(['disaster_id']);
            $table->index(['status']);
            $table->index(['check_in']);
            $table->index(['check_out']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('volunteer_task_logs');
    }
};
