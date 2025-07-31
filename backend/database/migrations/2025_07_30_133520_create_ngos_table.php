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
            $table->text('description')->nullable();
            $table->string('website')->nullable();
            $table->string('email');
            $table->string('phone');
            $table->string('based_in');
            $table->string('registration_no')->nullable();
            $table->year('established_year')->nullable();
            $table->string('director_name')->nullable();
            $table->string('director_phone')->nullable();
            $table->integer('num_employees')->nullable();
            $table->string('logo_url')->nullable();
            $table->boolean('approved')->default(false);
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
