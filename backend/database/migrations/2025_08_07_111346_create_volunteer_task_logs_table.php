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
            $table->foreignId('task_id')->constrained('tasks');
            $table->foreignId('volunteer_id')->constrained('users');
            $table->foreignId('disaster_id')->constrained('disasters');
            $table->enum('status', ['assigned', 'accepted', 'started', 'ended', 'verified', 'abandoned', 'failed']);
            $table->timestamp('check_in')->nullable();
            $table->timestamp('check_out')->nullable();
            $table->foreignId('start_verified_by')->nullable()->constrained('users');
            $table->foreignId('end_verified_by')->nullable()->constrained('users');
            $table->enum('report', ['normal', 'abandoned', 'incomplete'])->default('normal');
            $table->timestamps();
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
