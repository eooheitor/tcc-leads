<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Painel' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 text-gray-900">
    <!-- Navbar -->
    <nav class="bg-white border-b shadow px-6 py-3 flex justify-between items-center">
        <!-- Logo -->
        <div class="flex items-center space-x-4">
            <a href="{{ url('/') }}" class="text-xl font-bold text-blue-600">Meu Painel</a>
            <!-- Links -->
            <div class="hidden md:flex space-x-6">
                <a href="#" class="hover:text-blue-600">Dashboard</a>
                <a href="#" class="hover:text-blue-600">Clientes</a>
                <a href="#" class="hover:text-blue-600">Projetos</a>
            </div>
        </div>

        <!-- Usuário com dropdown -->
        <div x-data="{ open: false }" class="relative">
            <button @click="open = !open" class="flex items-center space-x-2 focus:outline-none">
                <span class="font-medium">Usuário</span>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div x-show="open" @click.away="open = false"
                 class="absolute right-0 mt-2 w-40 bg-white rounded-md shadow-lg py-2 z-50">
                <a href="#" class="block px-4 py-2 hover:bg-gray-100">Perfil</a>
                <a href="#" class="block px-4 py-2 hover:bg-gray-100">Sair</a>
            </div>
        </div>
    </nav>

    <!-- Conteúdo -->
    <main class="p-6">
        {{ $slot }}
    </main>
</body>
</html>
