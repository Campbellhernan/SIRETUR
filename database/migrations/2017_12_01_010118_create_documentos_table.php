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
            $table->string('fuente_descripcion',250)->nullable()->default(null);
            $table->text('palabras_clave');
            $table->decimal('rating')->nullable()->default(0);
            $table->integer('cluster')->nullable()->default(null);
            $table->decimal('latitud', 28, 10);
            $table->decimal('longitud', 28, 10);
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
