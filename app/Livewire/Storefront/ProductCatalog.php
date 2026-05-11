<?php

namespace App\Livewire\Storefront;

use App\Models\Product;
use App\Models\Categorie; // Asegúrate de que el modelo se llame así según tu migración
use App\Models\Category;
use Livewire\Component;
use Livewire\WithPagination;

class ProductCatalog extends Component
{
    use WithPagination;

    public $search = '';
    public $category_id = null; // Para filtrar por categoría

    public function updatingSearch()
    {
        $this->resetPage();
    }

    // Si cambia la categoría, regresamos a la página 1
    public function selectCategory($id = null)
    {
        $this->category_id = $id;
        $this->resetPage();
    }

    public function render()
    {
        $productos = Product::where('estado', true)
            ->with(['productoOpciones.atributo', 'productoOpciones.valor'])
            ->where(function ($query) {
                $query->where('nombre', 'like', '%' . $this->search . '%')
                    ->orWhere('codigo', 'like', '%' . $this->search . '%');
            })
            ->when($this->category_id, function ($query) {
                $query->where('categorie_id', $this->category_id);
            })
            ->latest()
            ->paginate(25);

        return view('livewire.storefront.product-catalog', [
            'productos' => $productos,
            'categorias' => Category::all()
        ])->layout('components.layouts.app');
    }
}
