<div x-data="{
    open: false,
    step: 'cart',
    nombre: '',
    dni: '',
    celular: '',
    provincia: '',
    direccion: '',
    descripcion: '',
    goToCheckout() { this.step = 'checkout' },
    backToCart() { this.step = 'cart' }
}" @open-cart.window="open = true; step = 'cart'" x-show="open" class="fixed inset-0 z-[100]"
    style="display: none;">

    {{-- Overlay --}}
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="open = false">
    </div>

    {{-- Modal centrado --}}
    <div class="absolute inset-0 flex items-center justify-center p-4 pointer-events-none">
        <div class="pointer-events-auto bg-white w-full max-w-2xl max-h-[92vh] flex flex-col shadow-2xl" @click.stop>

            {{-- ══ PASO 1: CARRITO ══ --}}
            <div x-show="step === 'cart'" class="flex flex-col flex-1 min-h-0">

                {{-- Header --}}
                <div class="px-8 py-5 border-b border-gray-100 flex justify-between items-center flex-shrink-0">
                    <div>
                        <h2 class="text-[11px] font-black uppercase tracking-[0.35em] text-gray-900">Tu Pedido</h2>
                        <p class="text-[9px] text-gray-400 uppercase tracking-widest mt-0.5">{{ count($cart) }}
                            {{ count($cart) === 1 ? 'producto' : 'productos' }}</p>
                    </div>
                    <div class="flex items-center gap-4">
                        @if (count($cart) > 0)
                            <button wire:click="clearCart"
                                class="text-[9px] uppercase tracking-widest text-red-400 font-bold hover:text-red-600 transition">
                                Vaciar
                            </button>
                        @endif
                        <button @click="open = false" class="text-gray-300 hover:text-black transition p-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path d="M6 18L18 6M6 6l12 12" stroke-width="2" stroke-linecap="round" />
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Productos --}}
                <div class="flex-1 overflow-y-auto px-8 py-6 space-y-5">
                    @forelse($cart as $key => $item)
                        @php
                            $precioBase = $item['precio_original'] ?? $item['precio'];
                            $tieneDescuento = $precioBase > $item['precio'];
                            $porcentaje = $tieneDescuento
                                ? round((($precioBase - $item['precio']) / $precioBase) * 100)
                                : 0;
                        @endphp

                        <div class="flex gap-4 pb-5 border-b border-gray-50 last:border-0">
                            <div class="w-20 h-24 bg-[#F9F9F9] flex-shrink-0 relative overflow-hidden">
                                <img src="{{ Storage::url($item['imagen']) }}" class="w-full h-full object-contain p-1">
                                @if ($tieneDescuento)
                                    <div
                                        class="absolute top-0 left-0 bg-black text-white text-[7px] font-black px-1.5 py-0.5 uppercase tracking-widest">
                                        -{{ $porcentaje }}%
                                    </div>
                                @endif
                            </div>

                            <div class="flex-1 min-w-0">
                                <div class="flex justify-between items-start gap-2">
                                    <div>
                                        <p
                                            class="text-[11px] font-black uppercase tracking-wide text-gray-900 leading-tight">
                                            {{ $item['nombre'] }}</p>
                                        <div class="mt-1.5 flex flex-wrap gap-x-3">
                                            @foreach ($item['variantes'] as $attr => $val)
                                                <span
                                                    class="text-[9px] text-gray-400 uppercase tracking-widest">{{ $attr }}:
                                                    <span
                                                        class="text-gray-600 font-bold">{{ $val }}</span></span>
                                            @endforeach
                                        </div>
                                    </div>
                                    <button wire:click="removeItem('{{ $key }}')"
                                        class="text-gray-200 hover:text-red-400 transition flex-shrink-0">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path d="M6 18L18 6M6 6l12 12" stroke-width="2" stroke-linecap="round" />
                                        </svg>
                                    </button>
                                </div>

                                <div class="mt-4 flex justify-between items-end">
                                    <div>
                                        @if ($tieneDescuento)
                                            <p class="text-[9px] line-through text-gray-300">S/
                                                {{ number_format($precioBase, 2) }}</p>
                                            <p class="text-sm font-black text-red-600">S/
                                                {{ number_format($item['precio'], 2) }}</p>
                                        @else
                                            <p class="text-sm font-black text-gray-900">S/
                                                {{ number_format($item['precio'], 2) }}</p>
                                        @endif
                                    </div>
                                    <span
                                        class="text-[9px] font-bold text-gray-400 uppercase tracking-widest bg-gray-50 px-3 py-1">x{{ $item['cantidad'] }}</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="flex flex-col items-center justify-center py-20 text-center space-y-3">
                            <svg class="w-10 h-10 text-gray-200" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" />
                            </svg>
                            <p class="text-[10px] text-gray-400 uppercase tracking-[0.3em]">Carrito Vacío</p>
                            <button @click="open = false"
                                class="text-[9px] font-black border-b border-black pb-0.5 uppercase tracking-widest hover:opacity-50 transition">
                                Explorar Tienda
                            </button>
                        </div>
                    @endforelse
                </div>

                {{-- Footer --}}
                @if (count($cart) > 0)
                    <div class="px-8 py-5 border-t border-gray-100 bg-gray-50/40 space-y-3 flex-shrink-0">
                        <div class="flex justify-between items-center">
                            <span class="text-[9px] font-bold uppercase tracking-[0.3em] text-gray-400">Total</span>
                            <span class="text-lg font-black text-gray-900">
                                S/ {{ number_format(collect($cart)->sum(fn($i) => $i['precio'] * $i['cantidad']), 2) }}
                            </span>
                        </div>
                        <button @click="goToCheckout()"
                            class="w-full bg-black text-white py-4 text-[10px] font-bold uppercase tracking-[0.35em] hover:bg-gray-800 transition active:scale-[0.98]">
                            Continuar con el pedido →
                        </button>
                        <p class="text-[9px] text-gray-400 uppercase tracking-widest text-center">Envío a todo el Perú •
                            Pago seguro</p>
                    </div>
                @endif
            </div>

            {{-- ══ PASO 2: CHECKOUT ══ --}}
            <div x-show="step === 'checkout'" class="flex flex-col flex-1 min-h-0">

                {{-- Header --}}
                <div class="px-8 py-5 border-b border-gray-100 flex justify-between items-center flex-shrink-0">
                    <div class="flex items-center gap-3">
                        <button @click="backToCart()" class="text-gray-300 hover:text-black transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                            </svg>
                        </button>
                        <div>
                            <h2 class="text-[11px] font-black uppercase tracking-[0.35em] text-gray-900">Datos de Envío
                            </h2>
                            <p class="text-[9px] text-gray-400 uppercase tracking-widest mt-0.5">Envío por Shalom</p>
                        </div>
                    </div>
                    <button @click="open = false" class="text-gray-300 hover:text-black transition p-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path d="M6 18L18 6M6 6l12 12" stroke-width="2" stroke-linecap="round" />
                        </svg>
                    </button>
                </div>

                {{-- Formulario --}}
                <div class="flex-1 overflow-y-auto px-8 py-6 space-y-5">

                    {{-- Resumen del pedido --}}
                    <div class="bg-gray-50 px-5 py-4 space-y-2">
                        <p class="text-[9px] font-black uppercase tracking-[0.3em] text-gray-400 mb-3">Resumen del
                            pedido</p>
                        @foreach ($cart as $key => $item)
                            @php
                                $codigo = str_pad(explode('_', $key)[1] ?? '0000', 4, '0', STR_PAD_LEFT);
                                $variantes = collect($item['variantes'])->values()->implode(' - ');
                            @endphp
                            <div class="flex justify-between items-start gap-4">
                                <span class="text-[11px] text-gray-600 leading-snug">
                                    <span class="font-black text-gray-900">{{ $codigo }}</span>
                                    — {{ $item['nombre'] }}
                                    @if ($variantes)
                                        <span class="text-gray-500">- {{ $variantes }}</span>
                                    @endif
                                    <span class="text-gray-400 font-bold"> ×{{ $item['cantidad'] }}</span>
                                </span>
                                <span class="text-[11px] font-black text-gray-900 flex-shrink-0">S/
                                    {{ number_format($item['precio'] * $item['cantidad'], 2) }}</span>
                            </div>
                        @endforeach
                        <div class="pt-3 mt-1 border-t border-gray-200 flex justify-between items-center">
                            <span class="text-[9px] font-black uppercase tracking-widest text-gray-500">Total a
                                pagar</span>
                            <span class="text-base font-black text-black">S/
                                {{ number_format(collect($cart)->sum(fn($i) => $i['precio'] * $i['cantidad']), 2) }}</span>
                        </div>
                    </div>

                    {{-- Campos --}}
                    <div class="space-y-4">
                        <div>
                            <label
                                class="block text-[9px] font-bold uppercase tracking-[0.25em] text-gray-400 mb-1.5">Nombres
                                completos</label>
                            <input x-model="nombre" type="text" placeholder="Ej: Juan Pérez García"
                                class="w-full border border-gray-200 px-4 py-3 text-[13px] text-gray-900 placeholder-gray-300 focus:outline-none focus:border-black transition">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label
                                    class="block text-[9px] font-bold uppercase tracking-[0.25em] text-gray-400 mb-1.5">DNI</label>
                                <input x-model="dni" type="text" maxlength="8" placeholder="12345678"
                                    class="w-full border border-gray-200 px-4 py-3 text-[13px] text-gray-900 placeholder-gray-300 focus:outline-none focus:border-black transition">
                            </div>
                            <div>
                                <label
                                    class="block text-[9px] font-bold uppercase tracking-[0.25em] text-gray-400 mb-1.5">Celular</label>
                                <input x-model="celular" type="text" maxlength="9" placeholder="987654321"
                                    class="w-full border border-gray-200 px-4 py-3 text-[13px] text-gray-900 placeholder-gray-300 focus:outline-none focus:border-black transition">
                            </div>
                        </div>
                        <div>
                            <label
                                class="block text-[9px] font-bold uppercase tracking-[0.25em] text-gray-400 mb-1.5">Provincia
                                / Ciudad</label>
                            <input x-model="provincia" type="text" placeholder="Ej: Lima, Trujillo, Arequipa..."
                                class="w-full border border-gray-200 px-4 py-3 text-[13px] text-gray-900 placeholder-gray-300 focus:outline-none focus:border-black transition">
                        </div>
                        <div>
                            <label
                                class="block text-[9px] font-bold uppercase tracking-[0.25em] text-gray-400 mb-1.5">Agencia
                                Shalom más cercana</label>
                            <input x-model="direccion" type="text"
                                placeholder="Ej: Shalom Av. España 123, Trujillo"
                                class="w-full border border-gray-200 px-4 py-3 text-[13px] text-gray-900 placeholder-gray-300 focus:outline-none focus:border-black transition">
                        </div>
                        <div>
                            <label class="block text-[9px] font-bold uppercase tracking-[0.25em] text-gray-400 mb-1.5">
                                Descripción adicional
                                <span class="text-gray-300 normal-case tracking-normal font-normal">(opcional)</span>
                            </label>
                            <textarea x-model="descripcion" rows="3" placeholder="Indicaciones extras para tu pedido..."
                                class="w-full border border-gray-200 px-4 py-3 text-[13px] text-gray-900 placeholder-gray-300 focus:outline-none focus:border-black transition resize-none"></textarea>
                        </div>
                    </div>
                </div>

                {{-- Footer con botón WhatsApp --}}
                <div class="px-8 py-5 border-t border-gray-100 flex-shrink-0 space-y-2">
                    <button
                        @click="
                            if (!nombre || !dni || !celular || !provincia || !direccion) {
                                alert('Por favor completa todos los campos obligatorios.');
                                return;
                            }
                            const productos = @js(
                                collect($cart)
                                    ->map(function ($item, $key) {
                                        $codigo = str_pad(explode('_', $key)[1] ?? '0000', 4, '0', STR_PAD_LEFT);
                                        $variantes = collect($item['variantes'])->values()->implode(' - ');
                                        $linea = $codigo . ' - ' . $item['nombre'];
                                        if ($variantes) {
                                            $linea .= ' - ' . $variantes;
                                        }
                                        $linea .= ' x' . $item['cantidad'];
                                        return $linea;
                                    })
                                    ->values()
                                    ->toArray(),
                            );
                            const total = 'S/ {{ number_format(collect($cart)->sum(fn($i) => $i['precio'] * $i['cantidad']), 2) }}';
                            let msg = '🛍️ *NUEVO PEDIDO*\n\n';
                            msg += '👤 *Cliente:* ' + nombre + '\n';
                            msg += '🪪 *DNI:* ' + dni + '\n';
                            msg += '📱 *Celular:* ' + celular + '\n';
                            msg += '📍 *Provincia:* ' + provincia + '\n';
                            msg += '🏪 *Agencia Shalom:* ' + direccion + '\n';
                            if (descripcion) msg += '📝 *Notas:* ' + descripcion + '\n';
                            msg += '\n📦 *PRODUCTOS:*\n';
                            productos.forEach(p => { msg += '• ' + p + '\n'; });
                            msg += '\n💰 *TOTAL: ' + total + '*';
                            const url = 'https://wa.me/51948798072?text=' + encodeURIComponent(msg);
                            window.open(url, '_blank');
                            $wire.clearCart();
                            step = 'done';
                        "
                        class="w-full bg-[#25D366] text-white py-4 text-[10px] font-bold uppercase tracking-[0.3em] hover:bg-[#1ebe5a] transition active:scale-[0.98] flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z" />
                        </svg>
                        Enviar Pedido por WhatsApp
                    </button>
                    <p class="text-[9px] text-gray-400 uppercase tracking-widest text-center">Te contactaremos para
                        confirmar tu pedido</p>
                </div>
            </div>

            <div x-show="step === 'done'"
                class="flex flex-col items-center justify-center flex-1 min-h-0 px-8 py-16 text-center space-y-6">

                {{-- Ícono check --}}
                <div class="w-16 h-16 rounded-full bg-[#25D366]/10 flex items-center justify-center">
                    <svg class="w-8 h-8 text-[#25D366]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>

                {{-- Mensaje --}}
                <div class="space-y-2">
                    <h2 class="text-[13px] font-black uppercase tracking-[0.3em] text-gray-900">¡Pedido Registrado!
                    </h2>
                    <p class="text-[11px] text-gray-400 leading-relaxed uppercase tracking-widest">Gracias por su
                        confianza</p>
                    <p class="text-[11px] text-gray-400 leading-relaxed">En breve nos comunicaremos con usted para
                        confirmar su pedido.</p>
                </div>

                {{-- Botón cerrar --}}
                <button @click="open = false; step = 'cart'"
                    class="mt-4 bg-black text-white px-10 py-3.5 text-[10px] font-bold uppercase tracking-[0.35em] hover:bg-gray-800 transition active:scale-[0.98]">
                    Seguir Comprando
                </button>
            </div>
        </div>
    </div>
</div>
