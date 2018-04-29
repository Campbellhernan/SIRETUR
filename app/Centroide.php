<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Mpociot\HasCompositeKey\HasCompositeKey;

class Centroide extends Model
{
    use HasCompositeKey;
    protected $fillable = ['valor'];
    
    protected $primaryKey = ['centroide', 'termino'];
}
