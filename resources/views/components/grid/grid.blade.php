@props(['grid', 'rows', 'columns', 'form'])

<div
    x-data="gridData()"
    x-init="init()"
    class="text-sm">
    {{-- Header --}}
    <div class="flex justify-between items-center mb-3">
        <h1 class="text-lg font-semibold text-gray-800 truncate">{{ $grid->getTitle() }}</h1>
        <button
            @click="openModal()"
            class="bg-[#000000] text-white px-3 py-1.5 rounded-md text-sm shadow hover:bg-[#222] transition">
            + Adicionar
        </button>
    </div>

    {{-- Modal --}}
    <template x-if="showModal">
        <div
            x-transition
            class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40"
            @click.self="closeModal()">
            <div class="bg-white rounded-md shadow-lg w-full max-w-md p-4 relative max-h-[85vh] overflow-y-auto">
                <button
                    @click="closeModal()"
                    class="absolute top-2 right-2 text-gray-500 hover:text-gray-700 text-xl leading-none">
                    &times;
                </button>

                <h2 class="text-base font-semibold text-gray-800 mb-3" x-text="modalTitle"></h2>

                {{-- Loading --}}
                <div x-show="loading" class="text-center py-3">
                    <div class="inline-block animate-spin rounded-full h-6 w-6 border-b-2 border-[#000000]"></div>
                    <p class="mt-2 text-gray-600 text-sm">Carregando...</p>
                </div>

                {{-- Formulário dinâmico --}}
                @if(isset($form) && $form)
                <form x-show="!loading" @submit.prevent="handleFormSubmit" class="space-y-3">
                    {!! $form->render() !!}
                </form>
                @endif
            </div>
        </div>
    </template>

    {{-- Grid --}}
    <div class="overflow-x-auto bg-white rounded-md shadow-sm border border-gray-100">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-[#000000]">
                <tr>
                    @foreach($columns as $col)
                    <th class="px-3 py-2 text-left text-[11px] font-medium text-white uppercase tracking-wider">
                        {{ $col["label"] }}
                    </th>
                    @endforeach
                    <th class="px-3 py-2 text-right text-[11px] font-medium text-white uppercase tracking-wider">Ações</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
                @forelse($rows as $row)
                <tr class="hover:bg-gray-50 transition">
                    @foreach($columns as $col)
                    <td class="px-3 py-2 whitespace-nowrap text-gray-700 align-middle">
                        {!! $col["callback"] ? $col["callback"]($row) : e(data_get($row, $col["key"])) !!}
                    </td>
                    @endforeach

                    <td class="px-3 py-2 whitespace-nowrap text-right text-xs font-medium space-x-1">
                        {{-- Editar --}}
                        <button
                            @click="openModal({{ $row->id }})"
                            class="inline-flex items-center px-2 py-1 rounded bg-[#000000] text-white hover:bg-[#222] transition">
                            <x-heroicon-o-pencil-square class="w-3.5 h-3.5" />
                        </button>

                        {{-- Excluir --}}
                        <button
                            @click="deleteItem({{ $row->id }})"
                            class="inline-flex items-center px-2 py-1 rounded bg-red-600 text-white hover:bg-red-700 transition">
                            <x-heroicon-o-trash class="w-3.5 h-3.5" />
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="{{ count($columns) + 1 }}" class="px-3 py-3 text-center text-gray-500 text-sm">
                        Nenhum registro encontrado.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Paginação --}}
    @if(is_object($rows) && method_exists($rows, 'links'))
    <div class="mt-2">
        {{ $rows->links() }}
    </div>
    @endif
</div>

<script>
    Alpine.data('gridData', () => ({
        ...window.gridData(),
        csrfToken: '{{ csrf_token() }}',
        storeUrl: '{{ Route::has($grid->getRouteCreate()) ? route($grid->getRouteCreate()) : null }}',
        editBaseUrl: '{{ Route::has($grid->getRouteEdit()) ? url(Str::plural($grid->getModelName())) : null }}',
        deleteBaseUrl: '{{ Route::has($grid->getRouteDelete()) ? url(Str::plural($grid->getModelName())) : null }}',
    }));
</script>