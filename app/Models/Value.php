<?php

namespace App\Models;

use Attribute;
use Illuminate\Database\Eloquent\Model;

class Value extends Model
{
    protected $fillable = [
        'nombre',
        'valor',
        'estado',
        'attribute_id'
    ];

    public function attribute(){
        return $this->belongsTo(Attribute::class);
    }

    public function productoOpciones(){
        return $this->hasMany(ProductOption::class);
    }
}
