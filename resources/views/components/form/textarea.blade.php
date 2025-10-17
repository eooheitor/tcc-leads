<div class="mb-3">
    <label for="{{ $name }}" class="block text-sm font-medium text-gray-700">{{ $label }}</label>
    <input id="{{ $name }}" type="hidden" name="{{ $name }}" value="{{ old($name, $value ?? '') }}">
    <trix-editor
        input="{{ $name }}"
        class="trix-content mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:border-black focus:ring-black sm:text-sm min-h-[200px]"></trix-editor>
</div>