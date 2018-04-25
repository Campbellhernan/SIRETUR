<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCentroidesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('centroides', function (Blueprint $table) {
            $table->integer('centroide');
            $table->string('termino');
            $table->decimal('valor',28, 10);
            $table->timestamps();
            
            $table->primary(['centroide','termino']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('centroides');
    }
}
