@props(['name','label','options'=>[],'selected'=>null,'disabled'=>false])

<div class="mb-4">
  <label for="{{ $name }}" class="block text-sm font-medium text-gray-700">
    {{ $label }}
    @if($disabled)
      <span class="ml-1 text-xs text-gray-500">(bloqueado)</span>
    @endif
  </label>

  <select
    name="{{ $name }}"
    id="{{ $name }}"
    @if($disabled) disabled aria-disabled="true" tabindex="-1" title="Campo não editável" @endif
    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm
           focus:border-indigo-500 focus:ring-indigo-500
           {{ $disabled ? 'bg-gray-100 cursor-not-allowed opacity-70' : '' }}">
    @foreach($options as $key => $value)
      <option value="{{ $key }}" {{ (string)$key === (string)$selected ? 'selected' : '' }}>
        {{ $value }}
      </option>
    @endforeach
  </select>
</div>
