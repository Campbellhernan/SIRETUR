<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDocumentosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('documentos', function (Blueprint $table) {
            $table->increments('id');
            $table->string('url');
            $table->string('place_id');
            $table->string('direccion');
            $table->string('nombre');
            $table->text('description');
            $table->text('palabras_clave');
            $table->decimal('rating');
            $table->integer('cluster')->nullable()->default(null);
            $table->string('foto_referencia');
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
        Schema::dropIfExists('documentos');
    }
}
