<?php

namespace App\Livewire\Storefront;

use App\Models\Product;
use Livewire\Component;

class ProductDetail extends Component
{
    public Product $producto;
    public $imagenPrincipal;
    public $cantidad = 1;
    public bool $modalTallasAbierto = false;

    // Almacena las opciones seleccionadas (ej: ['Color' => 1, 'Talla' => 5])
    public $selectedOptions = [];

    public function mount(Product $producto)
    {
        $this->producto = $producto->load(['imagenes', 'productoOpciones.atributo', 'productoOpciones.valor']);
        $this->imagenPrincipal = $this->producto->imagen_path;

        // Inicializar selecciones con la primera opción disponible de cada atributo
        foreach ($this->producto->productoOpciones->groupBy('atributo.nombre') as $nombreAtributo => $opciones) {
            $this->selectedOptions[$nombreAtributo] = $opciones->first()->valor->id;
        }
    }

    public function selectOption($atributo, $valorId)
    {
        $this->selectedOptions[$atributo] = $valorId;
    }

    public function cambiarImagen($path)
    {
        $this->imagenPrincipal = $path;
    }

    public function incrementar()
    {
        $this->cantidad++;
    }
    public function decrementar()
    {
        if ($this->cantidad > 1) $this->cantidad--;
    }

    // Agrega estos dos métodos:
    public function abrirModalTallas(): void
    {
        $this->modalTallasAbierto = true;
    }

    public function cerrarModalTallas(): void
    {
        $this->modalTallasAbierto = false;
    }

    public function agregarAlCarrito()
    {
        // Generamos un ID único basado en el producto y sus opciones seleccionadas
        $variantKey = 'prod_' . $this->producto->id . '_' . collect($this->selectedOptions)->values()->implode('_');

        $cart = session()->get('cart', []);

        if (isset($cart[$variantKey])) {
            $cart[$variantKey]['cantidad'] += $this->cantidad;
        } else {
            // Obtenemos los nombres legibles de las variantes para el carrito
            $detalles = [];
            foreach ($this->selectedOptions as $atributo => $valorId) {
                $opcion = $this->producto->productoOpciones->firstWhere('valor.id', $valorId);
                $detalles[$atributo] = $opcion->valor->nombre;
            }

            $cart[$variantKey] = [
                'id' => $this->producto->id,
                'nombre' => $this->producto->nombre,
                'precio' => $this->producto->precio_con_descuento ?? $this->producto->precio,
                'imagen' => $this->producto->imagen_path,
                'variantes' => $detalles,
                'cantidad' => $this->cantidad,
            ];
        }

        session()->put('cart', $cart);

        // Notificamos a los demás componentes
        $this->dispatch('cart-updated');
        $this->dispatch('open-cart'); // Activa el drawer en el layout
    }

    public function imagenSiguiente()
    {
        $todasLasImagenes = array_merge([$this->producto->imagen_path], $this->producto->imagenes->pluck('path')->toArray());
        $currentIndex = array_search($this->imagenPrincipal, $todasLasImagenes);

        $nextIndex = ($currentIndex + 1) % count($todasLasImagenes);
        $this->imagenPrincipal = $todasLasImagenes[$nextIndex];
    }

    public function imagenAnterior()
    {
        $todasLasImagenes = array_merge([$this->producto->imagen_path], $this->producto->imagenes->pluck('path')->toArray());
        $currentIndex = array_search($this->imagenPrincipal, $todasLasImagenes);

        $prevIndex = ($currentIndex - 1 + count($todasLasImagenes)) % count($todasLasImagenes);
        $this->imagenPrincipal = $todasLasImagenes[$prevIndex];
    }

    public function render()
    {
        return view('livewire.storefront.product-detail')
            ->layout('components.layouts.app', [
                'title' => $this->producto->nombre . ' - Kittybell'
            ]);
    }
}
