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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('disaster_id')->nullable()->constrained('disasters')->onDelete('set null');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->enum('task_type', ['aid_request', 'delivery', 'rescue', 'medical_assistance', 'resource_distribution', 'evacuation', 'assessment', 'other'])->default('aid_request');
            $table->foreignId('aid_request_id')->nullable()->constrained('aid_requests')->onDelete('set null');
            $table->string('location');
            $table->timestamp('start_time');
            $table->timestamp('end_time');
            $table->enum('aid_type', ['financial', 'medical', 'resource', 'food', 'shelter', 'clothing', 'other'])->nullable();
            $table->enum('urgency', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->text('description');
            $table->enum('status', ['pending', 'assigned', 'rejected', 'completed'])->default('pending');
            $table->text('ngo_remarks')->nullable();
            $table->timestamps();

            // Indexes for better query performance
            $table->index(['disaster_id']);
            $table->index(['assigned_to']);
            $table->index(['created_by']);
            $table->index(['task_type']);
            $table->index(['aid_request_id']);
            $table->index(['aid_type']);
            $table->index(['urgency']);
            $table->index(['status']);
            $table->index(['start_time']);
            $table->index(['end_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
