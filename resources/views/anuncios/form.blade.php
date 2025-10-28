@props([
'modalId',
'size' => 'max-w-2xl',
])

<x-modal :modalId="$modalId" :title="$form->getTitle()" size="max-w-4xl">
    <form
        id="anuncioForm"
        action="{{ $form->getRouteForm() }}"
        method="POST"
        enctype="multipart/form-data">
        @csrf
        @if (strtoupper($form->getMethod()) === 'PUT')
        @method('PUT')
        @endif

        {!! $form->render() !!}

        <button
            type="submit"
            class="mt-4 px-4 py-2 bg-purple-600 text-white rounded hover:bg-purple-700">
            Salvar
        </button>
    </form>
</x-modal>