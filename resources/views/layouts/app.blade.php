<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Painel' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="//unpkg.com/alpinejs" defer></script>
    <link rel="stylesheet" href="https://unpkg.com/trix@2.0.8/dist/trix.css">
    <script src="https://unpkg.com/trix@2.0.8/dist/trix.umd.min.js"></script>
</head>

<body class="bg-gray-100 flex h-screen" x-data="{ sidebarOpen: true }">
    <aside :class="sidebarOpen ? 'w-64' : 'w-16'"
        class="bg-white shadow-md flex flex-col transition-all duration-300">

        <!-- Sidebar Header -->
        <div class="h-16 flex items-center px-4 border-b bg-black"
            :class="sidebarOpen ? 'justify-between' : 'justify-center'">

            <!-- Ícone (aparece só quando sidebarOpen = true) -->
            <a href="/"
                x-show="sidebarOpen"
                class="flex items-center space-x-2 transition-all duration-300">
                <x-heroicon-o-building-office-2 class="w-10 h-10 text-white" />
            </a>

            <!-- Botão de abrir/fechar -->
            <button
                @click="sidebarOpen = !sidebarOpen"
                class="text-white ml-auto p-2 rounded-md hover:bg-gray-800 hover:text-white focus:outline-none transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
        </div>

        <!-- Sidebar Menu -->
        <nav class="flex-1 p-4 space-y-2">
            @php
            $links = [
            ['url' => '/', 'icon' => 'home', 'label' => 'Início'],
            ['url' => '/clientes', 'icon' => 'list-bullet', 'label' => 'Leads'],
            ['url' => '/mensagens', 'icon' => 'chat-bubble-left-right', 'label' => 'Mensagens'],
            ['url' => '/campanhas', 'icon' => 'megaphone', 'label' => 'Campanhas'],
            ['url' => '/anuncios', 'icon' => 'rectangle-stack', 'label' => 'Anúncios'],
            ['url' => '/adsets', 'icon' => 'rectangle-stack', 'label' => 'Ad Sets'],
            ];
            @endphp

            @foreach ($links as $link)
            <a href="{{ $link['url'] }}"
                :class="sidebarOpen ? 'justify-start' : 'justify-center'"
                class="flex items-center gap-2 px-4 py-2 rounded-lg transition hover:bg-black hover:text-white text-gray-700">
                <x-dynamic-component :component="'heroicon-o-' . $link['icon']" class="w-5 h-5 flex-shrink-0" />
                <span x-show="sidebarOpen" class="whitespace-nowrap font-medium">{{ $link['label'] }}</span>
            </a>
            @endforeach
        </nav>
    </aside>

    <!-- Main content -->
    <div class="flex-1 flex flex-col">
        <!-- HEADER -->
        <header class="h-16 bg-white shadow flex justify-end items-center px-6 border-b">
            <div x-data="{ open: false }" class="relative">
                <button
                    @click="open = !open"
                    class="flex items-center space-x-2 px-3 py-2 rounded-lg hover:bg-gray-100 transition">
                    <div class="flex items-center space-x-2">
                        <div class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center">
                            <x-heroicon-o-user class="w-5 h-5 text-gray-600" />
                        </div>
                        <span class="font-medium text-gray-800">{{ auth()->user()->name }}</span>
                    </div>
                    <svg class="w-5 h-5 text-gray-600 transition-transform duration-200"
                        :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <!-- Dropdown -->
                <div x-show="open" @click.away="open = false"
                    x-transition.origin.top.right
                    class="absolute right-0 mt-2 w-44 bg-white rounded-lg shadow-lg py-2 z-50 border border-gray-100">
                    <a href="{{ route('profile.edit') }}"
                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Perfil</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                            class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                            Sair
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <!-- PAGE CONTENT -->
        <main class="flex-1 p-6 overflow-y-auto">
            @hasSection('content')
            @yield('content')
            @else
            {{ $slot ?? '' }}
            @endif
        </main>
    </div>
</body>


</html>