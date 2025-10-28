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

    <div class="h-16 flex items-center px-6 border-b bg-[#000000]"
            :class="sidebarOpen ? 'justify-between' : 'justify-center'">
            <span x-show="sidebarOpen" class="text-xl font-bold text-white">Meu Painel</span>
            <button @click="sidebarOpen = !sidebarOpen" class="text-white ml-auto p-2 rounded-md hover:bg-gray-100 hover:text-black focus:outline-none"
                x-show="sidebarOpen || !sidebarOpen">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
        </div>

        <nav class="flex-1 p-4 space-y-2">
            <a href="/"
                :class="sidebarOpen ? 'justify-start' : 'justify-center'"
                class="flex items-center gap-2 px-4 py-2 rounded-lg transition hover:bg-[#000000] hover:text-white text-gray-700">
                <x-heroicon-o-home class="w-5 h-5 flex-shrink-0" />
                <span x-show="sidebarOpen" class="whitespace-nowrap font-medium">Início</span>
            </a>

            <a href="/clientes"
                :class="sidebarOpen ? 'justify-start' : 'justify-center'"
                class="flex items-center gap-2 px-4 py-2 rounded-lg transition hover:bg-[#000000] hover:text-white text-gray-700">
                <x-heroicon-o-list-bullet class="w-5 h-5 flex-shrink-0" />
                <span x-show="sidebarOpen" class="whitespace-nowrap font-medium">Clientes</span>
            </a>

            <a href="/mensagens"
                :class="sidebarOpen ? 'justify-start' : 'justify-center'"
                class="flex items-center gap-2 px-4 py-2 rounded-lg transition hover:bg-[#000000] hover:text-white text-gray-700">
                <x-heroicon-o-chat-bubble-left-right class="w-5 h-5 flex-shrink-0" />
                <span x-show="sidebarOpen" class="whitespace-nowrap font-medium">Mensagens</span>
            </a>

            <a href="/campanhas"
                :class="sidebarOpen ? 'justify-start' : 'justify-center'"
                class="flex items-center gap-2 px-4 py-2 rounded-lg transition hover:bg-[#000000] hover:text-white text-gray-700">
                <x-heroicon-o-megaphone class="w-5 h-5 flex-shrink-0" />
                <span x-show="sidebarOpen" class="whitespace-nowrap font-medium">Campanhas</span>
            </a>

            <a href="/anuncios"
                :class="sidebarOpen ? 'justify-start' : 'justify-center'"
                class="flex items-center gap-2 px-4 py-2 rounded-lg transition hover:bg-[#000000] hover:text-white text-gray-700">
                <x-heroicon-o-rectangle-stack class="w-5 h-5 flex-shrink-0" />
                <span x-show="sidebarOpen" class="whitespace-nowrap font-medium">Anúncios</span>
            </a>
        </nav>
    </aside>

    <div class="flex-1 flex flex-col">
        <header class="h-16 shadow flex justify-end items-center px-6">
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" class="flex items-center space-x-2 focus:outline-none">
                    <span class="font-medium">{{ auth()->user()->name }}</span>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <div x-show="open" @click.away="open = false"
                    class="absolute right-0 mt-2 w-40 bg-white rounded-lg shadow-lg py-2 z-50">
                    <a href="{{ route('profile.edit') }}" class="block px-4 py-2 hover:bg-gray-100">Perfil</a>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full text-left px-4 py-2 hover:bg-gray-100">
                            Sair
                        </button>
                    </form>

                </div>
            </div>
        </header>

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