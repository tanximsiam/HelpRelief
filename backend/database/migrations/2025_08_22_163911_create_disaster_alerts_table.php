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
        Schema::create('disaster_alerts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('disaster_type');
            $table->enum('status', ['Ongoing', 'Past', 'Unknown']);
            $table->text('description')->nullable();
            $table->timestamp('reported_at')->nullable();
            $table->json('divisions');
            $table->enum('confirmed',['pending', 'confirmed', 'rejected'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('disaster_alerts');
    }
};
