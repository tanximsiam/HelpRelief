<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

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
            $table->foreignId('disaster_id')->constrained('disasters');
            $table->foreignId('ngo_id')->constrained('users'); // NGO
            $table->enum('status', ['pending', 'approved', 'rejected', 'active', 'inactive', 'completed'])->default('pending');
            $table->timestamp('registered_at')->nullable();
            $table->string('availability')->nullable();
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
        //
    }
};
