<div class="mb-4">
    <label for="{{ $name }}" class="block text-sm font-medium text-gray-700">
        {{ $label }}
    </label>
    <input
        type="file"
        name="{{ $name }}"
        id="{{ $name }}"
        class="mt-1 block w-full text-sm text-gray-900 border border-gray-300 rounded-md cursor-pointer bg-gray-50 focus:outline-none
               file:bg-gray-200 file:text-gray-700 file:font-semibold file:border-0 file:py-2 file:px-4 file:mr-4 hover:file:bg-gray-300">

    {{-- Exibe a imagem atual de forma simples, se ela existir --}}
    @if ($currentFile)
    <div class="mt-4">
        <p class="text-sm text-gray-500">Imagem atual:</p>
        <img src="{{ asset('storage/' . $currentFile) }}" alt="Imagem atual" class="mt-2 h-20 w-20 rounded-md object-cover border border-gray-200">
    </div>
    @endif

    {{-- Exibe erros de validação, se houver --}}
    @error($name)
    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>