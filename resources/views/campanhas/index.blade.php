<x-app-layout>
    <x-grid 
        :grid="$grid"
        :rows="$campanhas"
        :columns="$grid->getColumns()"
        formView="campanhas.form"
        :form="$form"
        :formData="['campanhas' => $campanhas]"
    />
</x-app-layout>
