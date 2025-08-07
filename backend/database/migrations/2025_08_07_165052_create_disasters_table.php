<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDisastersTable extends Migration
{
    public function up()
    {
        Schema::create('disasters', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('disaster_type', ['flood', 'earthquake', 'storm', 'fire', 'cyclone']);
            $table->string('location');
            $table->date('start_date');
            $table->enum('status', ['pending_verification', 'active', 'closed'])->default('active');
            $table->text('description')->nullable();
            $table->foreignId('created_by')->constrained('users'); // who reported/created it
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('disasters');
    }
};