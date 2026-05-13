<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductOption extends Model
{
    protected $fillable = [
        'codigo',
        'precio_extra',
        'stock',
        'imagen_path',
        'estado',
        'attribute_id',
        'value_id',
        'product_id',
    ];

    public function imagenes()
    {
        return $this->morphMany(Image::class, 'imageable')
            ->orderBy('orden'); // Para que siempre salgan en el orden que elegiste
    }

    public function atributo()
    {
        // AQUÍ ESTÁ EL FIX: Le decimos explícitamente que use 'attribute_id'
        return $this->belongsTo(Attribute::class, 'attribute_id');
    }

    public function valor()
    {
        // AQUÍ TAMBIÉN: Le decimos explícitamente que use 'value_id'
        return $this->belongsTo(Value::class, 'value_id');
    }

    public function producto()
    {
        // Por consistencia, también lo ponemos aquí
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function exclusiones()
    {
        return $this->hasMany(Exclusion::class, 'producto_opciones_id');
    }
}
