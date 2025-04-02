<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBoxDesignsTable extends Migration
{
    public function up()
    {
        Schema::create('box_designs', function (Blueprint $table) {
            // Box configuration fields
            $table->id();
            $table->float('breadth');
            $table->float('length');
            $table->float('height');
            $table->float('thickness');
            $table->integer('flute_freq');
            $table->string('model_path')->nullable();
            $table->string('image_path')->nullable();
            
            // Face configuration (using JSON columns to store all faces)
            $table->json('faces')->nullable()->comment('JSON containing all face configurations');
            
            // Alternatively, if you prefer separate columns for each face:
            /*
            $table->json('front_face')->nullable();
            $table->json('back_face')->nullable();
            $table->json('left_face')->nullable();
            $table->json('right_face')->nullable();
            $table->json('top_face')->nullable();
            $table->json('bottom_face')->nullable();
            */
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('box_designs');
    }
}