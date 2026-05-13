<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Exclusion extends Model
{
    protected $table = 'exclusions'; // o 'Explusiones' si así se llama

    protected $fillable = [
        'producto_opciones_id',
        'attribute_id',   // ← nombre real en tu BD
        'value_id',       // ← nombre real en tu BD
    ];

    public function opcionBase()
    {
        return $this->belongsTo(ProductOption::class, 'producto_opciones_id');
    }

    public function atributo()
    {
        return $this->belongsTo(Attribute::class, 'attribute_id');
    }

    public function valor()
    {
        return $this->belongsTo(Value::class, 'value_id');
    }
}