<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Tienda KittyBell' }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

<body class="bg-gray-50 text-gray-900 font-sans antialiased">

    <!-- 2. BARRA DE NAVEGACIÓN -->
    <header class="bg-white sticky top-0 z-50 border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-4 h-16 flex items-center justify-between">
            <a href="/" class="inline-block">
                <img src="{{ asset('img/logokittybell.jpeg') }}" alt="Logo Kittybell"
                    class="h-10 md:h-12 w-auto object-contain">
            </a>

            <nav class="flex items-center gap-4">
                <a href="/"
                    class="text-xs font-bold uppercase tracking-widest text-gray-400 hover:text-black">Inicio</a>

                {{-- Llamada segura al componente --}}
                @livewire('storefront.cart-counter')
            </nav>
        </div>
    </header>

    <!-- 3. AQUÍ SE INYECTAN TUS PRODUCTOS -->
    <main class="min-h-screen">
        {{ $slot }}
    </main>

    @livewire('storefront.cart-drawer')

    @livewireScripts
</body>

</html>
