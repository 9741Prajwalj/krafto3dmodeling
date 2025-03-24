<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBoxTables extends Migration
{
    public function up() {
        Schema::create('box_configurations', function (Blueprint $table) {
            $table->id();
            $table->float('breadth');
            $table->float('length');
            $table->float('height');
            $table->float('thickness');
            $table->integer('flute_freq');
            $table->string('model_path')->nullable(); // Add this
            $table->string('image_path')->nullable(); // Add this
            $table->timestamps();
        });

        Schema::create('box_faces', function (Blueprint $table) {
            $table->id();
            $table->foreignId('box_configuration_id')->constrained()->onDelete('cascade');
            $table->enum('face_name', ['front', 'back', 'left', 'right', 'top', 'bottom']);
            $table->boolean('has_attachment')->default(false);
            $table->timestamps(); // Added timestamps
        });

        Schema::create('attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('box_face_id')->constrained()->onDelete('cascade');
            $table->string('type')->nullable();
            $table->string('content')->nullable();
            $table->string('font_style')->nullable();
            $table->string('font_size')->nullable();
            $table->float('position_x')->default(0);
            $table->float('position_y')->default(0);
            $table->float('position_z')->default(0);
            $table->float('rotation_x')->default(0);
            $table->float('rotation_y')->default(0);
            $table->float('rotation_z')->default(0);
            $table->float('scale_x')->default(1); // Add this
            $table->float('scale_y')->default(1); // Add this
            $table->float('scale_z')->default(1); // Add this
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('attachments');
        Schema::dropIfExists('box_faces');
        Schema::dropIfExists('box_configurations');
    }
}