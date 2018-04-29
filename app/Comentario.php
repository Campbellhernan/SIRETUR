<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Comentario extends Model
{
    protected $fillable = ['place_id', 'documento_id','comentario'];
    protected $guarded = ['id'];
}
