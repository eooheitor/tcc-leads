<div class="flex justify-end space-x-2 mt-6">
    <button type="button" @click="currentModal = null"
        class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition">
        Cancelar
    </button>
    <button type="submit"
        class="px-4 py-2 bg-black text-white rounded-md hover:bg-[#222] transition">
        {{ $label }}
    </button>
</div>
