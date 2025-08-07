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
        Schema::create('aid_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('disaster_id')->nullable();
            $table->unsignedBigInteger('requester_id');
            $table->string('location');
            $table->string('aid_type');
            $table->enum('urgency', ['low', 'medium', 'high', 'critical']);
            $table->text('description');
            $table->enum('status', ['pending_assignment', 'assigned', 'in_progress', 'completed', 'cancelled'])->default('pending_assignment');
            $table->unsignedBigInteger('task_id')->nullable();
            $table->text('ngo_remarks')->nullable();
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('requester_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['disaster_id', 'status']);
            $table->index(['requester_id']);
            $table->index(['urgency']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aid_requests');
    }
};
