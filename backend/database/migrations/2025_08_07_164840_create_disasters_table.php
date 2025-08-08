<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDisastersTable extends Migration
{
    public function up()
    {
        Schema::create('disasters', function (Blueprint $table) {
            $table->id(); // Assuming UUID for the primary key
            $table->string('name');
            $table->enum('disaster_type', ['flood', 'earthquake', 'storm', 'wildfire', 'drought', 'other']);
            $table->string('location');
            $table->date('start_date');
            $table->enum('status', ['pending_verification', 'active', 'closed']);
            $table->text('description')->nullable();
            $table->foreignId('created_by')->constrained('ngo_staff');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('disasters');
    }
}
