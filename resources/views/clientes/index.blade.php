<x-app-layout>
    <x-grid 
        :grid="$grid"
        :rows="$clientes"
        :columns="$grid->getColumns()"
        formView="clientes.form"
        :form="$form"
        :formData="['clientes' => $clientes, 'mensagens' => $mensagens]"
    />
</x-app-layout>
