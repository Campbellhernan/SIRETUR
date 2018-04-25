<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Mpociot\HasCompositeKey\HasCompositeKey;
class Coleccion extends Model
{
        use HasCompositeKey;
        protected $fillable = ['tf_idf'];
        protected $primaryKey = ['documento_id', 'termino'];
}
