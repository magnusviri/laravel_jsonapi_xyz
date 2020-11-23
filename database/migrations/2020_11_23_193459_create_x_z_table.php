<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateXZTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('x_z', function (Blueprint $table) {
            // $table->id();
            // $table->timestamps();
            $table->bigInteger('x_id')->unsigned()->index();
            $table->foreign('x_id')->references('id')->on('x_e_s');
            $table->bigInteger('z_id')->unsigned()->index();
            $table->foreign('z_id')->references('id')->on('z_s');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('x_z');
    }
}
