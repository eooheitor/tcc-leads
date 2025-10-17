<x-app-layout>
    <x-grid 
        :grid="$grid"
        :rows="$anuncios"
        :columns="$grid->getColumns()"
        formView="anuncios.form"
        :formData="['anuncios' => $anuncios]"
    />
</x-app-layout>
