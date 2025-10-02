<div class="mb-3">
    <label for="{{ $name }}" class="block text-sm font-medium text-gray-700">{{ $label }}</label>
    <textarea name="{{ $name }}" id="{{ $name }}" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#000000] focus:ring-[#000000] sm:text-sm">{{ old($name, $value ?? '') }}</textarea>
</div>
