<x-app-layout>
    <x-grid
        :grid="$grid"
        :rows="$users"
        :columns="$grid->getColumns()"
        formView="users.form"
        :form="$form"
        :formData="['users' => $users]" />
</x-app-layout>