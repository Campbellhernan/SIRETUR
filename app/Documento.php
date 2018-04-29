<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Documento extends Model
{
    protected $fillable = ['url', 'place_id', 'direccion', 'nombre', 'descripcion','palabras_clave', 'rating','foto_referencia'];
  protected $guarded = ['id'];
}
