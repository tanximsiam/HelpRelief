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
        Schema::create('aid_supports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('disaster_id')->constrained('disasters');
            $table->foreignId('ngo_id')->constrained('ngos'); // NGO
            $table->enum('aid_type', ['financial', 'medical', 'resource']);
            $table->string('quantity');
            $table->text('description')->nullable();
            $table->string('contact')->nullable();
            $table->enum('status', ['collecting', 'processing', 'received', 'rejected'])->default('collecting');
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
