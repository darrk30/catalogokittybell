<div class="max-w-[1300px] mx-auto px-4 py-8 font-sans text-black">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">

        {{-- 🖼️ GALERÍA IZQUIERDA --}}
        <div class="lg:col-span-7 flex flex-col-reverse md:flex-row gap-4">
            {{-- Miniaturas laterales (Más compactas) --}}
            <div class="flex md:flex-col gap-2 w-full md:w-20 overflow-x-auto no-scrollbar">
                <button wire:click="cambiarImagen('{{ $producto->imagen_path }}')"
                    class="aspect-square w-20 border {{ $imagenPrincipal === $producto->imagen_path ? 'border-black' : 'border-gray-100' }} bg-[#F9F9F9] transition-all rounded-sm overflow-hidden">
                    <img src="{{ Storage::disk('public')->url($producto->imagen_path) }}"
                        class="w-full h-full object-contain p-1">
                </button>

                @foreach ($producto->imagenes as $img)
                    <button wire:click="cambiarImagen('{{ $img->path }}')"
                        class="aspect-square w-20 border {{ $imagenPrincipal === $img->path ? 'border-black' : 'border-gray-100' }} bg-[#F9F9F9] transition-all rounded-sm overflow-hidden">
                        <img src="{{ Storage::disk('public')->url($img->path) }}"
                            class="w-full h-full object-contain p-1">
                    </button>
                @endforeach
            </div>

            {{-- Imagen Principal (Padding reducido y Badge de Descuento) --}}
            {{-- Imagen Principal --}}
            <div class="flex-1 bg-[#F9F9F9] aspect-[4/5] relative overflow-hidden rounded-sm group">

                {{-- ⬅️ Botón Izquierdo --}}
                <button wire:click="imagenAnterior"
                    class="absolute cursor-pointer left-2 top-1/2 -translate-y-1/2 z-20 bg-white/80 hover:bg-white p-2 shadow-md  group-hover:opacity-100 border border-black/5">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                        stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                    </svg>
                </button>

                <img src="{{ Storage::disk('public')->url($imagenPrincipal) }}"
                    class="w-full h-full object-contain p-6 transition-all duration-500 group-hover:scale-105">

                {{-- ➡️ Botón Derecho --}}
                <button wire:click="imagenSiguiente"
                    class="absolute  cursor-pointer right-2 top-1/2 -translate-y-1/2 z-20 bg-white/80 hover:bg-white p-2 shadow-md group-hover:opacity-100 border border-black/5">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                        stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                    </svg>
                </button>

                {{-- Badge de descuento --}}
                @if ($producto->descuento > 0)
                    <div
                        class="absolute top-0 left-0 bg-black text-white text-[10px] font-black px-3 py-1.5 uppercase tracking-widest z-10">
                        -{{ $producto->descuento }}%
                    </div>
                @endif
            </div>
        </div>

        {{-- 📝 INFO DERECHA (Espaciado más compacto: space-y-6) --}}
        <div class="lg:col-span-5 space-y-6">
            <div class="space-y-2">
                <h1 class="text-3xl font-black uppercase tracking-tighter leading-none text-gray-900">
                    {{ $producto->nombre }}</h1>

                {{-- Lógica de Precios --}}
                <div class="flex items-center gap-3">
                    @if ($producto->descuento > 0)
                        @php
                            $precioConDescuento = $producto->precio * (1 - $producto->descuento / 100);
                        @endphp
                        <span class="text-2xl font-black text-red-600">S/
                            {{ number_format($precioConDescuento, 2) }}</span>
                        <span class="text-sm text-gray-300 line-through font-medium">S/
                            {{ number_format($producto->precio, 2) }}</span>
                    @else
                        <span class="text-2xl font-black text-gray-900">S/
                            {{ number_format($producto->precio, 2) }}</span>
                    @endif
                </div>
            </div>

            <hr class="border-gray-100">

            {{-- 📦 DESCRIPCIÓN --}}
            <div class="space-y-2">
                <h3 class="text-[10px] font-bold uppercase tracking-[0.3em] text-gray-400">Descripción</h3>
                <div class="text-[13px] text-gray-600 leading-relaxed font-light">
                    {{ $producto->descripcion ?: 'Sin descripción detallada.' }}
                </div>
                {{-- Enlace de tallas (solo si hay imagen) --}}
                @if ($producto->imagen_path_tallas)
                    <button wire:click="abrirModalTallas"
                        class="inline-flex items-center gap-1.5 text-[12px] font-bold uppercase tracking-[0.2em] text-black underline underline-offset-4 hover:text-gray-500 transition-colors mt-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                            stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3.75 3.75v4.5m0-4.5h4.5m-4.5 0L9 9M3.75 20.25v-4.5m0 4.5h4.5m-4.5 0L9 15M20.25 3.75h-4.5m4.5 0v4.5m0-4.5L15 9m5.25 11.25h-4.5m4.5 0v-4.5m0 4.5L15 15" />
                        </svg>
                        Ver guía de tallas
                    </button>
                @endif
            </div>

            {{-- 🎨 VARIANTES --}}
            <div class="space-y-6">
                @foreach ($producto->productoOpciones->groupBy('atributo.nombre') as $nombreAtributo => $opciones)
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <label
                                class="text-[10px] font-bold uppercase tracking-[0.2em] text-gray-900">{{ $nombreAtributo }}</label>
                            <span class="text-[9px] text-gray-400 uppercase font-bold">
                                {{ $opciones->where('valor.id', $selectedOptions[$nombreAtributo])->first()->valor->nombre }}
                            </span>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            @foreach ($opciones->unique('value_id') as $opt)
                                @php $isSelected = $selectedOptions[$nombreAtributo] == $opt->valor->id; @endphp

                                @if (strtolower($nombreAtributo) === 'color')
                                    <button wire:click="selectOption('{{ $nombreAtributo }}', {{ $opt->valor->id }})"
                                        class="w-8 h-8 rounded-full border {{ $isSelected ? 'border-black scale-110 shadow-sm' : 'border-gray-100 hover:border-gray-300' }} p-0.5 transition-all">
                                        <div class="w-full h-full rounded-full"
                                            style="background-color: {{ $opt->valor->valor }}"></div>
                                    </button>
                                @else
                                    <button wire:click="selectOption('{{ $nombreAtributo }}', {{ $opt->valor->id }})"
                                        class="px-4 py-2 border text-[10px] font-bold uppercase tracking-widest transition-all
                                            {{ $isSelected ? 'bg-black text-white border-black' : 'bg-white text-black border-gray-200 hover:border-black' }}">
                                        {{ $opt->valor->nombre }}
                                    </button>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- 🛒 ACCIONES --}}
            <div class="pt-4 space-y-4">
                <div class="flex items-center gap-3">
                    {{-- Selector de cantidad más compacto --}}
                    <div class="flex items-center border border-gray-100 h-12">
                        <button wire:click="decrementar"
                            class="px-4 text-gray-400 hover:text-black transition">-</button>
                        <span class="w-10 text-center font-bold text-xs">{{ $cantidad }}</span>
                        <button wire:click="incrementar"
                            class="px-4 text-gray-400 hover:text-black transition">+</button>
                    </div>

                    <button wire:click="agregarAlCarrito"
                        class="flex-1 bg-black text-white h-12 text-[10px] font-bold uppercase tracking-[0.3em] hover:bg-gray-800 transition-all active:scale-[0.98] shadow-lg shadow-black/5">
                        Añadir al Carrito
                    </button>
                </div>

                {{-- Garantía/Envío (Pequeño detalle extra) --}}
                <p class="text-[9px] text-gray-400 uppercase tracking-widest text-center">Envío a todo el Perú • Pago
                    seguro</p>
            </div>
        </div>
    </div>
    {{-- 📐 MODAL DE TALLAS --}}
    @if ($modalTallasAbierto && $producto->imagen_path_tallas)
        <div wire:click.self="cerrarModalTallas"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">

            <div class="bg-white relative max-w-2xl w-full max-h-[90vh] overflow-y-auto rounded-sm shadow-2xl">
                {{-- Header --}}
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                    <span class="text-[12px] font-bold uppercase tracking-[0.5em] text-gray-900">Guía de tallas</span>
                    <button wire:click="cerrarModalTallas" class="text-gray-400 hover:text-black transition p-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                            stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- Imagen --}}
                <div class="p-6">
                    <img src="{{ Storage::disk('public')->url($producto->imagen_path_tallas) }}"
                        class="w-full h-auto object-contain" alt="Guía de tallas {{ $producto->nombre }}">
                </div>
            </div>
        </div>
    @endif
</div>
