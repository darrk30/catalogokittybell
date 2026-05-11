<div>
    <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 py-10 font-sans text-black">

        {{-- Buscador y Categorías: IZQUIERDA a DERECHA --}}
        <div class="flex flex-col md:flex-row md:items-end mb-12 gap-6 md:gap-10">

            {{-- Buscador (izquierda) --}}
            <div class="relative w-full md:w-72 flex-shrink-0">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="BUSCAR PRODUCTOS..."
                    class="w-full border-0 border-b border-gray-200 bg-transparent py-3 pl-0 pr-10 text-sm font-light text-gray-900 focus:border-black focus:outline-none focus:ring-0 transition-colors placeholder-gray-400 tracking-[0.2em] uppercase">
                <svg class="absolute right-0 top-3.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>

            {{-- Divisor vertical (solo desktop) --}}
            <div class="hidden md:block w-px h-8 bg-gray-100 flex-shrink-0"></div>

            {{-- Categorías (derecha, scroll horizontal) --}}
            <div
                class="flex-1 overflow-x-auto no-scrollbar flex items-end gap-8 whitespace-nowrap border-b border-gray-100 pb-4">
                <button wire:click="selectCategory(null)"
                    class="cursor-pointer text-[11px] uppercase tracking-[0.3em] flex-shrink-0
                        {{ is_null($category_id) ? 'font-bold border-b-2 border-black pb-0' : 'text-gray-400 hover:text-black' }}
                        transition-all pb-2">
                    Ver Todo
                </button>

                @foreach ($categorias as $cat)
                    <button wire:click="selectCategory({{ $cat->id }})"
                        class="cursor-pointer text-[11px] uppercase tracking-[0.3em] flex-shrink-0
                            {{ $category_id == $cat->id ? 'font-bold border-b-2 border-black pb-0' : 'text-gray-400 hover:text-black' }}
                            transition-all pb-2"
                        wire:key="cat-{{ $cat->id }}">
                        {{ $cat->nombre }}
                    </button>
                @endforeach
            </div>
        </div>

        {{-- Grid de Productos --}}
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-x-8 gap-y-12">
            @foreach ($productos as $producto)
                @php
                    $imagenesList = $producto->imagenes ?? collect();
                    $imgHover = $imagenesList->firstWhere('orden', 0) ?? $imagenesList->first();

                    $opcionesColor = $producto->productoOpciones
                        ->filter(function ($opt) {
                            return $opt->atributo && strtolower($opt->atributo->nombre) === 'color';
                        })
                        ->unique('value_id');
                @endphp

                <div class="group relative flex flex-col transition-all border border-black/10 shadow-sm hover:shadow-md p-2 bg-white"
                    wire:key="prod-{{ $producto->id }}">

                    {{-- Contenedor de Imagen con Enlace --}}
                    <div class="relative w-full aspect-[3/4] mb-5 overflow-hidden bg-[#F9F9F9]">

                        {{-- Enlace que cubre toda la imagen --}}
                        <a href="{{ route('product.detail', $producto->id) }}" class="absolute inset-0 z-0">
                            @if ($producto->imagen_path)
                                <img src="{{ Storage::disk('public')->url($producto->imagen_path) }}"
                                    alt="{{ $producto->nombre }}"
                                    class="product-img-main absolute inset-0 w-full h-full object-contain mix-blend-multiply
                                transition-all duration-500 ease-in-out {{ $imgHover ? 'group-hover:opacity-0 group-hover:scale-105' : 'group-hover:scale-105' }}">
                            @else
                                <span
                                    class="absolute inset-0 flex items-center justify-center text-[10px] tracking-[0.2em] text-gray-300 uppercase">Sin
                                    Imagen</span>
                            @endif

                            @if ($imgHover)
                                <img src="{{ Storage::disk('public')->url($imgHover->path) }}"
                                    alt="{{ $producto->nombre }} — vista alternativa"
                                    class="absolute inset-0 w-full h-full object-contain p-4 mix-blend-multiply
                                opacity-0 scale-105 transition-all duration-500 ease-in-out group-hover:opacity-100 group-hover:scale-100">
                            @endif
                        </a>

                        {{-- Mini carrusel (z-10 para estar sobre el link) --}}
                        @if ($imagenesList->count() > 1)
                            <div
                                class="carousel-dots absolute bottom-3 left-0 right-0 flex justify-center gap-1.5 opacity-0 group-hover:opacity-100 transition-opacity duration-300 z-10">
                                @foreach ($imagenesList as $i => $img)
                                    <button type="button"
                                        onclick="setCarouselImg(this, '{{ Storage::disk('public')->url($img->path) }}', {{ $i }})"
                                        class="carousel-dot w-1.5 h-1.5 rounded-full bg-black/20 transition-all duration-200
                                    {{ $i === 0 ? 'bg-black/70 w-3' : '' }}">
                                    </button>
                                @endforeach
                            </div>
                        @endif

                        {{-- Badge de descuento (z-10) --}}
                        @if ($producto->descuento > 0)
                            <div
                                class="absolute top-0 left-0 bg-black text-white text-[11px] px-3 py-1.5 font-bold tracking-widest z-10 pointer-events-none">
                                -{{ $producto->descuento }}%
                            </div>
                        @endif
                    </div>

                    {{-- Información del producto con Enlace --}}
                    <div class="flex flex-col flex-grow text-left">
                        <a href="{{ route('product.detail', $producto->id) }}"
                            class="block group-hover:opacity-70 transition-opacity">
                            <h3
                                class="text-[13px] font-bold tracking-[0.1em] uppercase mb-1 line-clamp-2 leading-tight text-gray-900">
                                {{ $producto->nombre }}
                            </h3>

                            @if ($producto->descripcion)
                                <p class="text-[11px] text-gray-400 font-light leading-relaxed mb-3">
                                    {{ Str::limit($producto->descripcion, 30, '...') }}
                                </p>
                            @endif

                            <div class="flex items-baseline justify-start gap-4 mb-4">
                                @if ($producto->descuento > 0)
                                    <span class="text-lg font-black tracking-tighter">
                                        S/ {{ number_format($producto->precio_con_descuento, 2) }}
                                    </span>
                                    <span class="text-xs text-gray-300 line-through font-light tracking-wide">
                                        S/ {{ number_format($producto->precio, 2) }}
                                    </span>
                                @else
                                    <span class="text-lg font-black tracking-tighter text-gray-900">
                                        S/ {{ number_format($producto->precio, 2) }}
                                    </span>
                                @endif
                            </div>
                        </a>

                        {{-- Colores (.stop para no activar el link del padre si decides envolver todo) --}}
                        @if ($opcionesColor->count() > 0)
                            <div class="flex gap-3 items-center flex-wrap">
                                @foreach ($opcionesColor as $opcion)
                                    <div class="group/color relative">
                                        <div class="w-3.5 h-3.5 rounded-full border border-gray-200 transition-transform hover:scale-125 shadow-sm"
                                            style="background-color: {{ $opcion->valor->valor }};">
                                        </div>
                                        <span
                                            class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 hidden group-hover/color:block
                                    bg-black text-white text-[9px] px-2 py-1 uppercase tracking-widest
                                    whitespace-nowrap z-10 pointer-events-none shadow-xl">
                                            {{ $opcion->valor->nombre }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        @if ($productos->isEmpty())
            <div class="text-center py-20 uppercase text-[11px] tracking-widest text-gray-400">
                No se encontraron productos en esta categoría
            </div>
        @endif

        {{-- Paginación --}}
        @if ($productos->hasPages())
            <div class="mt-24 flex justify-center border-t border-gray-100 pt-12">
                <div class="flex items-center gap-16">
                    @if (!$productos->onFirstPage())
                        <button wire:click="previousPage"
                            class="cursor-pointer text-xs uppercase tracking-[0.3em] hover:font-bold transition-all">
                            Anterior
                        </button>
                    @endif

                    <span class="text-[10px] text-gray-300 uppercase tracking-[0.5em]">
                        {{ $productos->currentPage() }} / {{ $productos->lastPage() }}
                    </span>

                    @if ($productos->hasMorePages())
                        <button wire:click="nextPage"
                            class="cursor-pointer text-xs uppercase tracking-[0.3em] hover:font-bold transition-all">
                            Siguiente
                        </button>
                    @endif
                </div>
            </div>
        @endif
    </div>

    <style>
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }

        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        /* Tooltip de colores */
        .group\/color:hover span::after {
            content: '';
            position: absolute;
            top: 100%;
            left: 50%;
            margin-left: -4px;
            border-width: 4px;
            border-style: solid;
            border-color: black transparent transparent transparent;
        }

        /* Punto activo del carrusel */
        .carousel-dot.active {
            background-color: rgba(0, 0, 0, 0.7) !important;
            width: 0.75rem !important;
        }

        /* Suaviza el antialiasing */
        body {
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        /* Asegura que el botón agregar no tape los dots del carrusel */
        .group:hover .carousel-dots {
            bottom: 3.5rem;
        }
    </style>

    <script>
        /**
         * Cambia la imagen principal de una carta de producto usando el mini-carrusel de puntos.
         * @param {HTMLElement} dotEl  - El punto clickeado
         * @param {string}      imgSrc - URL de la imagen a mostrar
         * @param {number}      idx    - Índice del punto
         */
        function setCarouselImg(dotEl, imgSrc, idx) {
            const card = dotEl.closest('.group');
            const mainImg = card.querySelector('.product-img-main');
            const hoverImg = card.querySelector('img:not(.product-img-main)');
            const dots = card.querySelectorAll('.carousel-dot');

            /* Actualizar punto activo */
            dots.forEach(d => d.classList.remove('active'));
            dotEl.classList.add('active');

            /* Si hay imagen principal, cambiar su src */
            if (mainImg) {
                mainImg.src = imgSrc;
            }

            /* Limpiar estado hover para que no tape la imagen elegida */
            if (hoverImg && idx === 0) {
                hoverImg.style.opacity = '';
            }
        }
    </script>
</div>
