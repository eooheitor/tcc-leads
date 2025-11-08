@props(['name','label','value'=>'','disabled'=>false])

<label class="block text-sm font-medium text-gray-700 mb-1" for="{{ $name }}">
    {{ $label }} @if($disabled)<span class="ml-1 text-xs text-gray-500">(bloqueado)</span>@endif
</label>

<input
    type="date"
    name="{{ $name }}"
    id="{{ $name }}"
    value="{{ $value }}"
    @if($disabled) disabled aria-disabled="true" tabindex="-1" title="Campo não editável" @endif
    class="w-full border-gray-300 rounded-md shadow-sm focus:ring-black focus:border-black
         {{ $disabled ? 'bg-gray-100 cursor-not-allowed opacity-70' : '' }}" />