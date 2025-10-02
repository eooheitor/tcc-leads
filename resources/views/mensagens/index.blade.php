<x-app-layout>
    <x-grid 
        :grid="$grid"
        :rows="$mensagens"
        :columns="$grid->getColumns()"
        formView="mensagens.form"
        :form="$form"
        :formData="['mensagens' => $mensagens]"
    />
</x-app-layout>
