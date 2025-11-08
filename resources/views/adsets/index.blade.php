<x-app-layout>
    <x-grid 
        :grid="$grid"
        :rows="$adsets"
        :columns="$grid->getColumns()"
        formView="adsets.form"
        :form="$form"
        :formData="['adsets' => $adsets]"
    />
</x-app-layout>

