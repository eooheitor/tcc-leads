<div class="mb-3">
    <label for="{{ $name }}" class="block text-sm font-medium text-gray-700">{{ $label }}</label>
    <input type="text" name="{{ $name }}" id="{{ $name }}" value="{{ old($name, $value ?? '') }}"
        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#8a1590] focus:ring-[#8a1590] sm:text-sm">
</div>
