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
        Schema::create('volunteer_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            // Changed to campaign_id
            $table->foreignId('campaign_id')->constrained('disaster_campaign_assignments');
            $table->foreignId('ngo_id')->constrained('ngos');
            $table->enum('status', [
                'approved', 'flagged'
            ])->default('approved');
            $table->timestamp('registered_at')->nullable();
            $table->boolean('availability')->default(true);
            $table->text('skills')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('volunteer_registrations');
    }
};
