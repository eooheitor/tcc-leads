<div>
    <label for="{{ $name }}" class="block text-sm font-medium text-gray-700">{{ $label }}</label>
    <input type="color" name="{{ $name }}" id="{{ $name }}" value="{{ old($name, $value ?? '#000000') }}"
        class="mt-1 h-10 w-16 cursor-pointer border border-gray-300 rounded">
</div>
