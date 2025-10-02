<div class="flex items-center mb-3">
    <input id="{{ $name }}" name="{{ $name }}" type="checkbox" value="1"
        @checked(old($name, $checked ?? false))
        class="h-4 w-4 text-[#8a1590] focus:ring-[#8a1590] border-gray-300 rounded">
    <label for="{{ $name }}" class="ml-2 block text-sm text-gray-700">{{ $label }}</label>
</div>
