<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AdSets + Anúncios</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="//unpkg.com/alpinejs" defer></script>
</head>

<body class="bg-gray-100"
    x-data="{
        showModal: false,
        activeTab: 'conjunto',
        hasAdset: false, // vira true quando 'salvar conjunto'
      }">

    {{-- TOPO + GRID SIMPLES --}}
    <header class="bg-white shadow">
        <div class="max-w-7xl mx-auto px-4 py-4 flex items-center justify-between">
            <h1 class="text-lg font-semibold text-gray-800">
                Conjuntos de Anúncios
            </h1>

            <button
                @click="showModal = true; activeTab = 'conjunto'; hasAdset = false"
                class="inline-flex items-center px-4 py-2 rounded-md bg-black text-white text-sm font-medium hover:bg-gray-800">
                + Novo Conjunto
            </button>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 py-6">
        <div class="bg-white rounded-lg shadow border border-gray-200 overflow-hidden">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3 py-2 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Conjunto</th>
                        <th class="px-3 py-2 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Campanha</th>
                        <th class="px-3 py-2 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                        <th class="px-3 py-2 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Otimização</th>
                        <th class="px-3 py-2 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Orçamento Diário</th>
                        <th class="px-3 py-2 text-right text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">

                    {{-- FAKES --}}
                    <tr class="hover:bg-gray-50">
                        <td class="px-3 py-2">Conjunto Julho Leads</td>
                        <td class="px-3 py-2">Campanha Julho teste 3</td>
                        <td class="px-3 py-2">
                            <span class="inline-flex px-2 py-0.5 text-[11px] rounded-full bg-green-100 text-green-700">
                                ACTIVE
                            </span>
                        </td>
                        <td class="px-3 py-2">Cliques no Link</td>
                        <td class="px-3 py-2">R$ 25,00</td>
                        <td class="px-3 py-2 text-right space-x-1">
                            <button
                                @click="showModal = true; activeTab = 'conjunto'; hasAdset = true"
                                class="inline-flex items-center px-2 py-1 text-xs rounded bg-black text-white hover:bg-gray-800">
                                Editar
                            </button>
                            <button class="inline-flex items-center px-2 py-1 text-xs rounded bg-red-600 text-white hover:bg-red-700">
                                Excluir
                            </button>
                        </td>
                    </tr>

                    <tr class="hover:bg-gray-50">
                        <td class="px-3 py-2">Conjunto Catálogo Prata</td>
                        <td class="px-3 py-2">Campanha Catálogo Joias</td>
                        <td class="px-3 py-2">
                            <span class="inline-flex px-2 py-0.5 text-[11px] rounded-full bg-yellow-100 text-yellow-700">
                                PAUSED
                            </span>
                        </td>
                        <td class="px-3 py-2">Conversões (Offsite)</td>
                        <td class="px-3 py-2">R$ 10,00</td>
                        <td class="px-3 py-2 text-right space-x-1">
                            <button
                                @click="showModal = true; activeTab = 'conjunto'; hasAdset = true"
                                class="inline-flex items-center px-2 py-1 text-xs rounded bg-black text-white hover:bg-gray-800">
                                Editar
                            </button>
                            <button class="inline-flex items-center px-2 py-1 text-xs rounded bg-red-600 text-white hover:bg-red-700">
                                Excluir
                            </button>
                        </td>
                    </tr>

                </tbody>
            </table>
        </div>
    </main>

    {{-- MODAL COM TABS CONJUNTO / ANÚNCIOS --}}
    <template x-if="showModal">
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
            <div class="bg-white rounded-lg shadow-2xl w-full max-w-5xl max-h-[90vh] flex flex-col overflow-hidden">

                {{-- CABEÇALHO --}}
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">
                            <span x-show="!hasAdset">Novo Conjunto</span>
                            <span x-show="hasAdset">Editar Conjunto</span>
                        </h2>
                        <p class="text-xs text-gray-500">
                            Configure o conjunto e depois gerencie os anúncios vinculados.
                        </p>
                    </div>
                    <button
                        @click="showModal = false"
                        class="text-gray-400 hover:text-gray-600 text-xl">&times;</button>
                </div>

                {{-- TABS NO TOPO DO MODAL --}}
                <div class="px-6 pt-3 border-b border-gray-200 bg-white">
                    <nav class="flex space-x-4">
                        {{-- TAB CONJUNTO --}}
                        <button type="button"
                            @click="activeTab = 'conjunto'"
                            :class="activeTab === 'conjunto'
                                    ? 'px-4 py-2 text-sm font-medium border-b-2 border-purple-600 text-purple-700'
                                    : 'px-4 py-2 text-sm font-medium text-gray-500 border-b-2 border-transparent hover:text-gray-700'">
                            Conjunto
                        </button>

                        {{-- TAB ANÚNCIOS --}}
                        <button type="button"
                            @click="hasAdset && (activeTab = 'anuncios')"
                            :class="[
                                    'px-4 py-2 text-sm font-medium border-b-2',
                                    hasAdset
                                        ? (activeTab === 'anuncios'
                                            ? 'border-purple-600 text-purple-700'
                                            : 'border-transparent text-gray-500 hover:text-gray-700')
                                        : 'border-transparent text-gray-300 cursor-not-allowed'
                                ]"
                            :title="hasAdset ? '' : 'Salve o conjunto para liberar os anúncios.'">
                            Anúncios
                        </button>
                    </nav>
                </div>

                {{-- CONTEÚDO DAS TABS --}}
                <div class="flex-1 overflow-y-auto px-6 py-4 space-y-6 bg-gray-50">

                    {{-- ===================== TAB CONJUNTO ===================== --}}
                    <section x-show="activeTab === 'conjunto'">
                        <h3 class="text-sm font-semibold text-gray-800 mb-3">
                            Configurações do conjunto
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-white p-4 rounded-lg shadow-sm">

                            {{-- Nome --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Nome do Conjunto
                                </label>
                                <input type="text"
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:ring-purple-600 focus:border-purple-600 text-sm"
                                    placeholder="Ex.: Conjunto Julho Leads">
                            </div>

                            {{-- Campanha --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Campanha
                                </label>
                                <select
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:ring-purple-600 focus:border-purple-600 text-sm">
                                    <option>Campanha Julho teste 3</option>
                                    <option>Campanha Leads Condomínio</option>
                                    <option>Campanha Anúncios Residenciais</option>
                                </select>
                            </div>

                            {{-- Status --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Status
                                </label>
                                <select
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:ring-purple-600 focus:border-purple-600 text-sm">
                                    <option value="PAUSED">Pausado</option>
                                    <option value="ACTIVE">Ativo</option>
                                </select>
                            </div>

                            {{-- Otimização --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Otimização
                                </label>
                                <select
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:ring-purple-600 focus:border-purple-600 text-sm">
                                    <option>Cliques no Link</option>
                                    <option>Impressões</option>
                                    <option>Conversões</option>
                                    <option>Geração de Leads</option>
                                </select>
                            </div>

                            {{-- Cobrança --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Cobrança
                                </label>
                                <select
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:ring-purple-600 focus:border-purple-600 text-sm">
                                    <option>Impressões</option>
                                    <option>Cliques</option>
                                    <option>Leads</option>
                                </select>
                            </div>

                            {{-- Orçamento Diário --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Orçamento Diário (R$)
                                </label>
                                <input type="text"
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:ring-purple-600 focus:border-purple-600 text-sm"
                                    placeholder="Ex.: 25,00">
                            </div>

                            {{-- Lance --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Lance (R$)
                                </label>
                                <input type="text"
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:ring-purple-600 focus:border-purple-600 text-sm"
                                    placeholder="Ex.: 5,00">
                            </div>

                            {{-- Idades --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Idade mínima
                                </label>
                                <input type="number" min="13"
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:ring-purple-600 focus:border-purple-600 text-sm"
                                    value="18">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Idade máxima
                                </label>
                                <input type="number" min="13"
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:ring-purple-600 focus:border-purple-600 text-sm"
                                    value="65">
                            </div>

                            {{-- País --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    País
                                </label>
                                <select
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:ring-purple-600 focus:border-purple-600 text-sm">
                                    <option value="BR">Brasil</option>
                                </select>
                            </div>

                            {{-- Datas --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Data de Início
                                </label>
                                <input type="date"
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:ring-purple-600 focus:border-purple-600 text-sm">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Data de Término
                                </label>
                                <input type="date"
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:ring-purple-600 focus:border-purple-600 text-sm">
                            </div>
                        </div>

                        {{-- Rodapé da tab conjunto --}}
                        <div class="flex justify-end space-x-3 mt-4">
                            <button
                                @click="showModal = false"
                                class="px-4 py-2 text-sm rounded-md border border-gray-300 text-gray-700 hover:bg-gray-50">
                                Cancelar
                            </button>
                            <button
                                @click="
                                    // demo: marca como salvo e ativa a tab de anúncios
                                    hasAdset = true;
                                    activeTab = 'anuncios';
                                "
                                class="px-4 py-2 text-sm rounded-md bg-purple-600 text-white hover:bg-purple-700">
                                Salvar conjunto
                            </button>
                        </div>
                    </section>

                    {{-- ===================== TAB ANÚNCIOS ===================== --}}
                    <section x-show="activeTab === 'anuncios'">
                        <div class="bg-white p-4 rounded-lg shadow-sm mb-4">
                            <div class="flex items-center justify-between mb-3">
                                <div>
                                    <h3 class="text-sm font-semibold text-gray-800">
                                        Criar novo anúncio
                                    </h3>
                                    <p class="text-xs text-gray-500">
                                        Este anúncio será vinculado ao conjunto selecionado.
                                    </p>
                                </div>
                            </div>

                            {{-- Form simples de anúncio (fake) --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">
                                        Nome do anúncio
                                    </label>
                                    <input type="text"
                                        class="w-full border-gray-300 rounded-md shadow-sm focus:ring-purple-600 focus:border-purple-600"
                                        placeholder="Ex.: Anúncio catálogo outubro">
                                </div>

                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">
                                        Status
                                    </label>
                                    <select
                                        class="w-full border-gray-300 rounded-md shadow-sm focus:ring-purple-600 focus:border-purple-600">
                                        <option value="PAUSED">Pausado</option>
                                        <option value="ACTIVE">Ativo</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">
                                        Imagem do anúncio (criativo)
                                    </label>
                                    <input type="file"
                                        class="w-full text-sm border-gray-300 rounded-md shadow-sm focus:ring-purple-600 focus:border-purple-600">
                                </div>

                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">
                                        URL de destino
                                    </label>
                                    <input type="url"
                                        class="w-full border-gray-300 rounded-md shadow-sm focus:ring-purple-600 focus:border-purple-600"
                                        placeholder="https://">
                                </div>
                            </div>

                            <div class="mt-3 flex justify-end">
                                <button
                                    class="px-3 py-1.5 text-xs rounded-md bg-purple-600 text-white hover:bg-purple-700">
                                    Criar anúncio
                                </button>
                            </div>
                        </div>

                        {{-- GRID DE ANÚNCIOS EXISTENTES (fake) --}}
                        <div class="bg-white p-4 rounded-lg shadow-sm">
                            <div class="flex items-center justify-between mb-3">
                                <h3 class="text-sm font-semibold text-gray-800">
                                    Anúncios deste conjunto
                                </h3>
                                <span class="text-xs text-gray-400">
                                    3 anúncios ativos
                                </span>
                            </div>

                            <div class="overflow-x-auto border border-gray-200 rounded-md">
                                <table class="min-w-full text-sm">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-3 py-2 text-left text-[11px] font-medium text-gray-500 uppercase tracking-wide">Nome</th>
                                            <th class="px-3 py-2 text-left text-[11px] font-medium text-gray-500 uppercase tracking-wide">Criativo</th>
                                            <th class="px-3 py-2 text-left text-[11px] font-medium text-gray-500 uppercase tracking-wide">Status</th>
                                            <th class="px-3 py-2 text-left text-[11px] font-medium text-gray-500 uppercase tracking-wide">Tipo de destino</th>
                                            <th class="px-3 py-2 text-left text-[11px] font-medium text-gray-500 uppercase tracking-wide">Otimização</th>
                                            <th class="px-3 py-2 text-right text-[11px] font-medium text-gray-500 uppercase tracking-wide">Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 bg-white">

                                        <tr class="hover:bg-gray-50">
                                            <td class="px-3 py-2">Anúncio efetivo de julho</td>
                                            <td class="px-3 py-2">
                                                <div class="flex items-center space-x-2">
                                                    <img src="https://via.placeholder.com/48"
                                                        class="w-10 h-10 rounded object-cover" alt="">
                                                    <span class="text-xs text-gray-500">carrossel_julho.png</span>
                                                </div>
                                            </td>
                                            <td class="px-3 py-2">
                                                <span class="inline-flex px-2 py-0.5 text-[11px] rounded-full bg-green-100 text-green-700">
                                                    ACTIVE
                                                </span>
                                            </td>
                                            <td class="px-3 py-2">Website</td>
                                            <td class="px-3 py-2">Conversões (fora do site)</td>
                                            <td class="px-3 py-2 text-right space-x-1">
                                                <button class="inline-flex items-center px-2 py-1 text-xs rounded bg-black text-white hover:bg-gray-800">
                                                    Editar
                                                </button>
                                                <button class="inline-flex items-center px-2 py-1 text-xs rounded bg-red-600 text-white hover:bg-red-700">
                                                    Excluir
                                                </button>
                                            </td>
                                        </tr>

                                        <tr class="hover:bg-gray-50">
                                            <td class="px-3 py-2">Anúncio catálogo joias</td>
                                            <td class="px-3 py-2">
                                                <div class="flex items-center space-x-2">
                                                    <img src="https://via.placeholder.com/48"
                                                        class="w-10 h-10 rounded object-cover" alt="">
                                                    <span class="text-xs text-gray-500">catalogo_prata.jpg</span>
                                                </div>
                                            </td>
                                            <td class="px-3 py-2">
                                                <span class="inline-flex px-2 py-0.5 text-[11px] rounded-full bg-yellow-100 text-yellow-700">
                                                    PAUSED
                                                </span>
                                            </td>
                                            <td class="px-3 py-2">Website</td>
                                            <td class="px-3 py-2">Cliques no Link</td>
                                            <td class="px-3 py-2 text-right space-x-1">
                                                <button class="inline-flex items-center px-2 py-1 text-xs rounded bg-black text-white hover:bg-gray-800">
                                                    Editar
                                                </button>
                                                <button class="inline-flex items-center px-2 py-1 text-xs rounded bg-red-600 text-white hover:bg-red-700">
                                                    Excluir
                                                </button>
                                            </td>
                                        </tr>

                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </section>

                </div>
            </div>
        </div>
    </template>

</body>

</html>