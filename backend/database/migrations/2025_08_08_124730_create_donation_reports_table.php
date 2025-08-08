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
            $table->foreignId('disaster_id')->constrained()->onDelete('cascade');
            $table->foreignId('ngo_id')->constrained('users')->onDelete('cascade'); // NGO role
            $table->enum('aid_type', ['financial', 'medical', 'resource']);
            $table->decimal('amount_received', 15, 2);
            $table->decimal('amount_used', 15, 2);
            $table->text('usage_breakdown')->nullable(); // JSON/notes
            $table->string('reporting_period');
            $table->boolean('confirmed')->default(false);
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
