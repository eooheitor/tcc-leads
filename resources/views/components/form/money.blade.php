@props(['name','label','value'=>'','placeholder'=>'','disabled'=>false])

<div class="mb-4">
  <label for="{{ $name }}" class="block text-sm font-medium text-gray-700 mb-1">
    {{ $label }}
    @if($disabled)
      <span class="ml-1 text-xs text-gray-500">(bloqueado)</span>
    @endif
  </label>

  <div class="relative">
    <input
      type="text"
      name="{{ $name }}"
      id="{{ $name }}"
      value="{{ old($name, $value) }}"
      placeholder="{{ $placeholder }}"
      @if($disabled)
        disabled aria-disabled="true" tabindex="-1" title="Campo não editável"
        class="pl-8 pr-3 py-2 w-full border border-gray-300 rounded-md shadow-sm
               bg-gray-100 cursor-not-allowed opacity-70"
      @else
        class="pl-8 pr-3 py-2 w-full border border-gray-300 rounded-md shadow-sm
               focus:ring-indigo-500 focus:border-indigo-500"
        inputmode="decimal"
        pattern="^\d+([,]\d{2})?$"
        oninput="this.value = this.value.replace(/[^0-9,]/g, '').replace(/(,.*?),/g, '$1');"
      @endif
    />
  </div>
</div>
