<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'codigo',
        'nombre',
        'descripcion',
        'precio',
        'precio_con_descuento',
        'descuento',
        'stock',
        'imagen_path',
        'imagen_path_tallas',
        'categorie_id',
        'estado',
        'slug',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function productoOpciones()
    {
        return $this->hasMany(ProductOption::class);
    }

    public function categoria()
    {
        return $this->belongsTo(Category::class, 'categorie_id');
    }

    public function imagenes()
    {
        return $this->morphMany(Image::class, 'imageable')
            ->orderBy('orden'); // Para que siempre salgan en el orden que elegiste
    }

    public function getStockCalculadoAttribute()
    {
        $opcionesActivas = $this->productoOpciones()->where('estado', true)->get();
        if ($opcionesActivas->isEmpty()) {
            return $this->stock;
        }
        $sumaOpciones = $opcionesActivas->sum('stock');
        if ($sumaOpciones <= 0) {
            return $this->stock;
        }
        return $sumaOpciones;
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

        // 3. ELIMINA LA IMAGEN: Se ejecuta cuando se actualiza la imagen del producto
        static::updating(function ($model) {
            if ($model->isDirty('imagen_path') && $model->getOriginal('imagen_path')) {
                Storage::disk('public')->delete($model->getOriginal('imagen_path'));
            }
        });

        // 4. ELIMINA LA IMAGEN: Se ejecuta cuando se elimina un producto
        static::forceDeleted(function ($model) {
            if ($model->imagen_path) {
                Storage::disk('public')->delete($model->imagen_path);
            }
        });
    }
}
