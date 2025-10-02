<div class="mb-4">
    <label for="{{ $name }}" class="block text-sm font-medium text-gray-700">
        {{ $label }}
    </label>
    <select name="{{ $name }}" id="{{ $name }}"
        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
        @foreach($options as $key => $value)
            <option value="{{ $key }}" {{ (string)$key === (string)$selected ? 'selected' : '' }}>
                {{ $value }}
            </option>
        @endforeach
    </select>
</div>
