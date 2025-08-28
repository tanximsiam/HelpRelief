<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */

    public function up()
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            // Replaced disaster_id with campaign_id to directly associate tasks with a campaign
            $table->foreignId('campaign_id')->constrained('disaster_campaign_assignments');
            $table->foreignId('assigned_to')->constrained('users'); // the volunteer
            $table->foreignId('created_by')->constrained('users');  // NGO or admin
            // 'task_type' column removed per new requirements
            $table->foreignId('aid_request_id')->nullable(); // if linked
            $table->string('location');
            $table->timestamp('start_time');
            $table->timestamp('end_time')->nullable();
            $table->enum('aid_type', ['financial', 'medical', 'resource']);
            $table->enum('urgency', ['low', 'medium', 'high', 'critical']);
            $table->text('description')->nullable();
            $table->enum('status', ['pending', 'assigned', 'rejected', 'completed'])->default('assigned');
            $table->text('ngo_remarks')->nullable();
            $table->timestamps();
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
