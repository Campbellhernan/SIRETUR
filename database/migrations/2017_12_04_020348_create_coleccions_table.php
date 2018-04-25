<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateColeccionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coleccions', function (Blueprint $table) {
            $table->integer('documento_id');
            $table->string('termino');
            $table->decimal('tf_idf', 28, 10);
            $table->timestamps();
            
            $table->primary(['termino','documento_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('coleccions');
    }
}
