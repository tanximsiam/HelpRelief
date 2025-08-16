<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDisasterCampaignAssignmentsTable extends Migration
{
    public function up()
    {
        Schema::create('disaster_campaign_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('disaster_id')->constrained('disasters')->onDelete('cascade');
            $table->foreignId('ngo_id')->constrained('users')->onDelete('cascade'); // NGO role user
            $table->foreignId('assigned_by')->constrained('users')->onDelete('cascade'); // Admin
            $table->enum('status', ['active', 'inactive', 'pending'])->default('pending');
            $table->enum('help_needed', ['low', 'medium', 'high'])->default('medium');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('disaster_campaign_assignments');
    }
}
