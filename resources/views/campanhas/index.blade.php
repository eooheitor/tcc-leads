<x-app-layout>
    <x-grid 
        :grid="$grid"
        :rows="$campanhas"
        :columns="$grid->getColumns()"
        formView="campanhas.form"
        :formData="['campanhas' => $campanhas]"
    />
</x-app-layout>
