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
        Schema::create('ngo_applications', function (Blueprint $table) {
            $table->id();
            $table->string('organization');
            $table->string('contact_person');
            $table->string('designation');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('based_in');
            $table->text('description');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ngo_applications');
    }
};
