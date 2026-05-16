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
    public $selectedOptions = []; // ['Color' => value_id, 'Talla' => value_id]

    public function mount(Product $producto)
    {
        $this->producto = $producto;
        $this->cargarOpciones();
        $this->imagenPrincipal = $this->producto->imagen_path;

        // foreach ($this->producto->productoOpciones->groupBy('atributo.nombre') as $nombreAtributo => $opciones) {
        //     $primeraOpcion = $opciones->first();
        //     $this->selectedOptions[$nombreAtributo] = $primeraOpcion->valor->id;
        // }

        // $this->actualizarImagenPorColor();
    }

    public function hydrate(): void
    {
        $this->cargarOpciones();
    }

    private function cargarOpciones(): void
    {
        $this->producto->load([
            'imagenes',
            'productoOpciones' => fn($q) => $q->where('estado', true)
                                            ->with([
                                                'atributo',
                                                'valor',
                                                'imagenes',
                                                'exclusiones',
                                            ]),
        ]);
    }

    public function selectOption(string $atributo, int $valorId): void
    {
        // Si ya está seleccionado → deseleccionar (toggle)
        if (($this->selectedOptions[$atributo] ?? null) == $valorId) {
            $this->selectedOptions[$atributo] = null;
        } else {
            $this->selectedOptions[$atributo] = $valorId;
        }

        if (strtolower($atributo) === 'color') {
            $this->actualizarImagenPorColor();
        }
    }

    // ── Actualiza la imagen según el color seleccionado ──────
    private function actualizarImagenPorColor(): void
    {
        $colorValueId = collect($this->selectedOptions)
            ->first(fn($vid, $attr) => strtolower($attr) === 'color');

        if (!$colorValueId) return;

        $opcionColor = $this->producto->productoOpciones
            ->first(
                fn($op) =>
                strtolower($op->atributo?->nombre ?? '') === 'color' &&
                    $op->valor->id === $colorValueId
            );

        if (!$opcionColor) {
            // No encontró la opción → resetear al principal
            $this->imagenPrincipal = $this->producto->imagen_path;
            return;
        }

        if ($opcionColor->imagen_path) {
            // La opción tiene imagen propia → usarla
            $this->imagenPrincipal = $opcionColor->imagen_path;
        } elseif ($opcionColor->imagenes->isNotEmpty()) {
            // La opción tiene imágenes morphMany → usar la primera
            $this->imagenPrincipal = $opcionColor->imagenes->first()->path;
        } else {
            // ← ESTE ELSE ES EL FIX: la opción no tiene imagen → volver al principal
            $this->imagenPrincipal = $this->producto->imagen_path;
        }
    }

    public function getValoresBloqueadosProperty(): array
    {
        $bloqueados = [];

        foreach ($this->selectedOptions as $atributo => $valorId) {
            if (!$valorId) continue; // ← ignorar deseleccionados

            $opcion = $this->producto->productoOpciones
                ->first(
                    fn($op) =>
                    strtolower($op->atributo?->nombre ?? '') === strtolower($atributo) &&
                        $op->valor->id == $valorId
                );

            if (!$opcion) continue;

            foreach ($opcion->exclusiones as $excl) {
                $bloqueados[$excl->value_id] = true;
            }
        }

        // Exclusiones inversas — también ignorar nulls
        $selectedValueIds = array_filter(array_values($this->selectedOptions));

        foreach ($this->producto->productoOpciones as $opcion) {
            foreach ($opcion->exclusiones as $excl) {
                if (in_array($excl->value_id, $selectedValueIds)) {
                    $bloqueados[$opcion->value_id] = true;
                }
            }
        }

        return $bloqueados;
    }


    public function getMontoExtraProperty(): float
    {
        $selectedIds = array_filter(array_values($this->selectedOptions));

        return $this->producto->productoOpciones  // ← colección ya filtrada, sin ()
            ->whereIn('value_id', $selectedIds)
            ->sum('precio_extra') ?? 0;
    }

    // ── Opción actualmente seleccionada (para precio/código) ─
    public function getOpcionSeleccionadaProperty(): ?\App\Models\ProductOption
    {
        // Busca la opción que coincida con TODAS las selecciones actuales
        foreach ($this->producto->productoOpciones as $opcion) {
            $coincide = true;
            foreach ($this->selectedOptions as $atributo => $valorId) {
                if (
                    strtolower($opcion->atributo?->nombre ?? '') === strtolower($atributo) &&
                    $opcion->valor->id != $valorId
                ) {
                    $coincide = false;
                    break;
                }
            }
            if ($coincide) return $opcion;
        }
        return null;
    }

    // ── Precio final con precio_extra si aplica ───────────────
    public function getPrecioFinalProperty(): float
    {
        $base = $this->producto->precio_con_descuento ?? $this->producto->precio;

        // Sumar precio_extra de TODAS las opciones seleccionadas
        $extra = 0;
        foreach ($this->selectedOptions as $atributo => $valorId) {
            $opcion = $this->producto->productoOpciones
                ->first(
                    fn($op) =>
                    strtolower($op->atributo?->nombre ?? '') === strtolower($atributo) &&
                        $op->valor->id == $valorId
                );
            if ($opcion && $opcion->precio_extra > 0) {
                $extra += $opcion->precio_extra;
            }
        }

        return $base + $extra;
    }

    // ── Código a mostrar ──────────────────────────────────────
    // Si alguna opción seleccionada tiene código propio → usarlo
    // Si no → código principal del producto
    public function getCodigoFinalProperty(): string
    {
        foreach ($this->selectedOptions as $atributo => $valorId) {
            $opcion = $this->producto->productoOpciones
                ->first(
                    fn($op) =>
                    strtolower($op->atributo?->nombre ?? '') === strtolower($atributo) &&
                        $op->valor->id == $valorId
                );
            if ($opcion && !empty($opcion->codigo)) {
                return $opcion->codigo;
            }
        }
        return $this->producto->codigo;
    }

    // ── Navegación de imágenes ────────────────────────────────
    public function cambiarImagen($path): void
    {
        $this->imagenPrincipal = $path;
    }

    public function imagenSiguiente(): void
    {
        $todas = $this->todasLasImagenes();
        $idx   = array_search($this->imagenPrincipal, $todas);
        $this->imagenPrincipal = $todas[($idx + 1) % count($todas)];
    }

    public function imagenAnterior(): void
    {
        $todas = $this->todasLasImagenes();
        $idx   = array_search($this->imagenPrincipal, $todas);
        $this->imagenPrincipal = $todas[($idx - 1 + count($todas)) % count($todas)];
    }

    private function todasLasImagenes(): array
    {
        return array_merge(
            [$this->producto->imagen_path],
            $this->producto->imagenes->pluck('path')->toArray()
        );
    }

    // ── Modal tallas ──────────────────────────────────────────
    public function abrirModalTallas(): void
    {
        $this->modalTallasAbierto = true;
    }
    public function cerrarModalTallas(): void
    {
        $this->modalTallasAbierto = false;
    }

    // ── Cantidad ──────────────────────────────────────────────
    public function incrementar(): void
    {
        $this->cantidad++;
    }
    public function decrementar(): void
    {
        if ($this->cantidad > 1) $this->cantidad--;
    }

    // ── Agregar al carrito ────────────────────────────────────
    public function agregarAlCarrito(): void
    {
        $atributos = $this->producto->productoOpciones
            ->pluck('atributo.nombre')
            ->unique()
            ->values();

        $sinSeleccionar = $atributos->filter(
            fn($attr) => empty($this->selectedOptions[$attr])
        );

        if ($sinSeleccionar->isNotEmpty()) {
            $lista = $sinSeleccionar->implode(', ');
            $this->dispatch('notify', [
                'type'    => 'error',
                'message' => "Por favor selecciona: {$lista}",
            ]);
            return;
        }

        $variantKey = 'prod_' . $this->producto->id . '_'
            . collect($this->selectedOptions)->values()->implode('_');

        $cart = session()->get('cart', []);

        if (isset($cart[$variantKey])) {
            $cart[$variantKey]['cantidad'] += $this->cantidad;
        } else {
            // Variantes legibles para mostrar en el carrito
            $detalles = [];
            foreach ($this->selectedOptions as $atributo => $valorId) {
                $opcion = $this->producto->productoOpciones
                    ->first(
                        fn($op) =>
                        strtolower($op->atributo?->nombre ?? '') === strtolower($atributo) &&
                            $op->valor->id == $valorId
                    );
                if ($opcion) {
                    $detalles[$atributo] = $opcion->valor->nombre;
                }
            }

            $cart[$variantKey] = [
                'id'               => $this->producto->id,
                'codigo'           => $this->codigoFinal,        // ← código de opción o del producto
                'nombre'           => $this->producto->nombre,
                'precio'           => $this->precioFinal,        // ← precio con extras
                'precio_original'  => $this->producto->precio,
                'imagen'           => $this->imagenPrincipal,    // ← imagen del color seleccionado
                'variantes'        => $detalles,
                'cantidad'         => $this->cantidad,
            ];
        }

        session()->put('cart', $cart);
        $this->dispatch('cart-updated');
        $this->dispatch('open-cart');
    }

    public function render()
    {
        return view('livewire.storefront.product-detail')
            ->layout('components.layouts.app', [
                'title' => $this->producto->nombre . ' - Kittybell'
            ]);
    }
}
