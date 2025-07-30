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
        Schema::create('ngos', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('rep_contact'); // Representative name
            $table->string('rep_designation')->nullable(); // Rep's designation
            $table->string('rep_email')->unique(); // Rep's email
            $table->string('rep_phone')->nullable(); // Rep's phone number
            $table->text('description')->nullable();
            $table->string('website')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ngos');
    }
};
