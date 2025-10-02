@props(['grid', 'rows', 'columns', 'form'])

<div
    x-data="gridData()"
    x-init="init()">
    {{-- Header --}}
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">{{ $grid->getTitle() }}</h1>
        <button
            @click="openModal()"
            class="bg-[#000000] text-white px-4 py-2 rounded-md shadow hover:bg-[#222] transition">
            Adicionar
        </button>
    </div>

    {{-- Modal --}}
    <template x-if="showModal">
        <div
            x-transition
            class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
            @click.self="closeModal()">
            <div class="bg-white rounded-lg shadow-lg w-full max-w-2xl p-6 relative max-h-[90vh] overflow-y-auto">
                <button
                    @click="closeModal()"
                    class="absolute top-3 right-3 text-gray-500 hover:text-gray-700 text-2xl">
                    &times;
                </button>

                {{-- Título do Modal --}}
                <h2 class="text-xl font-bold text-gray-800 mb-4" x-text="modalTitle"></h2>

                {{-- Loading --}}
                <div x-show="loading" class="text-center py-4">
                    <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-[#000000]"></div>
                    <p class="mt-2 text-gray-600">Carregando...</p>
                </div>

                {{-- Formulário dinâmico --}}
                <form x-show="!loading" @submit.prevent="handleFormSubmit" class="space-y-4">
                    {!! $form->render() !!}
                </form>
            </div>
        </div>
    </template>

    {{-- Grid --}}
    <div class="overflow-x-auto bg-white rounded-lg shadow">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-[#000000]">
                <tr>
                    @foreach($columns as $col)
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">
                        {{ $col["label"] }}
                    </th>
                    @endforeach
                    <th class="px-6 py-3 text-right text-xs font-medium text-white uppercase tracking-wider">Ações</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($rows as $row)
                <tr class="hover:bg-gray-50 transition">
                    @foreach($columns as $col)
                    <td class="px-6 py-4 whitespace-nowrap text-gray-700">
                        {!! $col["callback"] ? $col["callback"]($row) : $row->{$col["key"]} !!}
                    </td>
                    @endforeach

                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                        {{-- Botão Editar --}}
                        <button
                            @click="openModal({{ $row->id }})"
                            class="inline-flex items-center px-3 py-1 rounded-md bg-[#000000] text-white hover:bg-[#222] transition">
                            <x-heroicon-o-pencil-square class="w-4 h-4 mr-1" />
                        </button>

                        {{-- Botão Excluir --}}
                        <button
                            @click="deleteItem({{ $row->id }})"
                            class="inline-flex items-center px-3 py-1 rounded-md bg-red-600 text-white hover:bg-red-700 transition">
                            <x-heroicon-o-trash class="w-4 h-4 mr-1" />
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="{{ count($columns) + 1 }}" class="px-6 py-4 text-center text-gray-500">
                        Não há nada cadastrado.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Paginação --}}
    @if(method_exists($rows, 'links'))
    <div class="mt-4">
        {{ $rows->links() }}
    </div>
    @endif
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('gridData', () => ({
            ...window.gridData(),
            csrfToken: '{{ csrf_token() }}',
            storeUrl: '{{ route($grid->getRouteCreate()) }}',
            editBaseUrl: "{{ url(Str::plural($grid->getModelName())) }}",
            deleteBaseUrl: "{{ url(Str::plural($grid->getModelName())) }}",
        }));
    });
</script>