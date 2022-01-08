<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCarteirasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('carteiras', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('pessoa_id');
            $table->double('saldo', 10, 2);
            $table->foreign('pessoa_id')->references('id')->on('pessoas');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('carteiras', function (Blueprint $table) {
            $table->dropForeign(['pessoa_id']);
        });
        Schema::dropIfExists('carteiras');
    }
}
