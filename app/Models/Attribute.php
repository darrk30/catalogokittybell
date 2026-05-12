<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Attribute extends Model
{
    // use SoftDeletes;
    
    protected $fillable = [
        'nombre',
        'estado',
        'user_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function values()
    {
        return $this->hasMany(Value::class);
    }
    
    public function productoOpciones(){
        return $this->hasMany(ProductOption::class);
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
        // static::addGlobalScope('usuario_propietario', function ($builder) {
        //     if (Auth::check()) {
        //         $builder->where('user_id', Auth::id());
        //     }
        // });
    }
}
