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
            // Direct campaign reference (replaces legacy disaster_id)
            $table->foreignId('campaign_id')->nullable()->constrained('disaster_campaign_assignments')->onDelete('set null');
            $table->foreignId('requester_id')->constrained('users')->onDelete('cascade');
            $table->string('location');
            $table->enum('aid_type', ['financial', 'medical', 'resource']);
            $table->enum('urgency', ['low', 'medium', 'high', 'critical']);
            $table->text('description');
            $table->enum('status', ['pending', 'assigned', 'rejected', 'completed'])->default('pending');
            $table->foreignId('task_id')->nullable();
            $table->text('ngo_remarks')->nullable();
            $table->timestamps();

            // Foreign key constraints

            $table->index(['campaign_id', 'status']);
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
