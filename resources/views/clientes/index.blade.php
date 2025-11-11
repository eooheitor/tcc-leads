<x-app-layout>
    @if(!empty($importCount) && $importCount > 0)
    <div class="mb-4 rounded-md bg-green-50 border border-green-200 px-4 py-2 text-sm text-green-800">
        {{ $importCount }} novo(s) lead(s) importado(s) da Meta nos últimos 7 dias.
    </div>
    @endif
    <x-grid
        :grid="$grid"
        :rows="$clientes"
        :columns="$grid->getColumns()"
        formView="clientes.form"
        :form="$form"
        :formData="['clientes' => $clientes, 'mensagens' => $mensagens]" />
</x-app-layout>