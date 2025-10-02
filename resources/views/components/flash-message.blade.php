@if (session('success'))
    <div 
        x-data="{ show: true }" 
        x-show="show" 
        x-init="setTimeout(() => show = false, 4000)" 
        class="bg-green-500 text-white px-4 py-2 rounded shadow">
        {{ session('success') }}
    </div>
@endif

@if (session('error'))
    <div 
        x-data="{ show: true }" 
        x-show="show" 
        x-init="setTimeout(() => show = false, 4000)" 
        class="bg-red-500 text-white px-4 py-2 rounded shadow">
        {{ session('error') }}
    </div>
@endif