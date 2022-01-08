<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransferenciasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transferencias', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('carteira_pagador_id');
            $table->unsignedInteger('carteira_beneficiario_id');
            $table->enum('status', array_values(config('constants.status_transferencias')));
            $table->double('valor', 10, 2);
            $table->boolean('notificacao_enviada')->default(false);

            $table->foreign('carteira_pagador_id')->references('id')->on('carteiras');
            $table->foreign('carteira_beneficiario_id')->references('id')->on('carteiras');

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
        Schema::table('transferencias', function (Blueprint $table) {
            $table->dropForeign(['carteira_pagador_id']);
            $table->dropForeign(['carteira_beneficiario_id']);
        });
        Schema::dropIfExists('transferencias');
    }
}
