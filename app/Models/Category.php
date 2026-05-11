<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Category extends Model
{
    protected $fillable = [
        'nombre',
        'estado',
        'user_id'
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class);
    }

    public function productos()
    {
        return $this->hasMany(Product::class, 'categorie_id');
    }

    protected static function booted()
    {
        // 1. ASIGNACIÓN AUTOMÁTICA: Se ejecuta al crear un registro
        static::creating(function ($model) {
            if (Auth::check() && !$model->user_id) {
                $model->user_id = Auth::id();
            }
        });

        // 2. FILTRADO AUTOMÁTICO: Se ejecuta en cada consulta (SELECT)
        static::addGlobalScope('usuario_propietario', function ($builder) {
            if (Auth::check()) {
                $builder->where('user_id', Auth::id());
            }
        });
    }
}
