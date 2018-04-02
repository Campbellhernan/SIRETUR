<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Coleccion extends Model
{
        protected $fillable = [
        'documento_id', 'termino', 'tf_idf',
    ];
}
