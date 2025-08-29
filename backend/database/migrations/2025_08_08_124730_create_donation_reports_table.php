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
        Schema::create('donation_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained('disaster_campaign_assignments')->onDelete('cascade');
            // Mapped amounts for each aid type
            $table->decimal('amount_received_financial', 15, 2)->default(0);
            $table->decimal('amount_used_financial', 15, 2)->default(0);
            $table->decimal('amount_received_medical', 15, 2)->default(0);
            $table->decimal('amount_used_medical', 15, 2)->default(0);
            $table->decimal('amount_received_resource', 15, 2)->default(0);
            $table->decimal('amount_used_resource', 15, 2)->default(0);
            $table->text('usage_breakdown')->nullable(); // JSON/notes
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('donation_reports');
    }
};
