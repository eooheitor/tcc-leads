<div class="space-y-1">
    <label class="block text-sm font-medium text-gray-700">{{ $label }}</label>
    <input
        type="file"
        name="{{ $name }}"
        multiple
        @if(!empty($accept)) accept="{{ $accept }}" @endif
        class="block w-full text-sm text-gray-700 file:mr-4 file:py-2 file:px-3 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-black file:text-white hover:file:bg-gray-800" />
</div>