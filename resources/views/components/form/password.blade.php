@props(['name','label','placeholder' => '', 'disabled' => false])

<div class="mb-3">
    <label for="{{ $name }}" class="block text-sm font-medium text-gray-700">
        {{ $label }}
        @if($disabled)
        <span class="ml-1 text-xs text-gray-500">(bloqueado)</span>
        @endif
    </label>

    <input
        type="password"
        name="{{ $name }}"
        id="{{ $name }}"
        autocomplete="new-password"
        placeholder="{{ $placeholder }}"
        @if($disabled) disabled aria-disabled="true" tabindex="-1" title="Campo não editável" @endif
        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm
           focus:border-[#8a1590] focus:ring-[#8a1590]
           {{ $disabled ? 'bg-gray-100 cursor-not-allowed opacity-70' : '' }}">
</div>