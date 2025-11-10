{{-- resources/views/adsets/partials/ads_table.blade.php --}}

@php
/** @var array<int, array|object> $anuncios */
    @endphp

    <div class="overflow-x-auto border border-gray-200 rounded-md"
        x-ref="adsGridContainer">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-3 py-2 text-left font-medium text-gray-500 uppercase tracking-wide text-[11px]">
                        Nome
                    </th>
                    <th class="px-3 py-2 text-left font-medium text-gray-500 uppercase tracking-wide text-[11px]">
                        Imagem
                    </th>
                    <th class="px-3 py-2 text-left font-medium text-gray-500 uppercase tracking-wide text-[11px]">
                        Status
                    </th>
                    <th class="px-3 py-2 text-left font-medium text-gray-500 uppercase tracking-wide text-[11px]">
                        Tipo de destino
                    </th>
                    <th class="px-3 py-2 text-right font-medium text-gray-500 uppercase tracking-wide text-[11px]">
                        Ações
                    </th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-100 bg-white">
                @forelse($anuncios as $ad)
                @php
                $adObj = is_array($ad) ? (object) $ad : $ad;
                $status = strtoupper($adObj->status ?? 'PAUSED');
                @endphp
                <tr class="hover:bg-gray-50">
                    <td class="px-3 py-2">
                        {{ $adObj->name ?? '—' }}
                    </td>

                    <td class="px-3 py-2">
                        @php
                        $img = data_get($adObj, 'creative.image_url');
                        @endphp
                        @if($img)
                        <div class="flex items-center space-x-2">
                            <img src="{{ $img }}" class="w-10 h-10 rounded object-cover" alt="Criativo">
                        </div>
                        @else
                        <span class="text-xs text-gray-400">Sem imagem</span>
                        @endif
                    </td>

                    <td class="px-3 py-2">
                        @if($status === 'ACTIVE')
                        <span class="inline-flex px-2 py-0.5 text-[11px] rounded-full bg-green-100 text-green-700">
                            ACTIVE
                        </span>
                        @elseif($status === 'PAUSED')
                        <span class="inline-flex px-2 py-0.5 text-[11px] rounded-full bg-yellow-100 text-yellow-700">
                            PAUSED
                        </span>
                        @else
                        <span class="inline-flex px-2 py-0.5 text-[11px] rounded-full bg-gray-100 text-gray-600">
                            {{ $status }}
                        </span>
                        @endif
                    </td>

                    <td class="px-3 py-2">
                        {{ $adObj->destination_type ?? '—' }}
                    </td>

                    <td class="px-3 py-2 text-right space-x-1">
                        <button
                            type="button"
                            class="inline-flex items-center px-2 py-1 text-xs rounded bg-black text-white hover:bg-gray-800"
                            data-ad-edit-id="{{ $adObj->id ?? '' }}">
                            <x-heroicon-o-pencil-square class="w-3.5 h-3.5" />
                        </button>

                        <button
                            type="button"
                            class="inline-flex items-center px-2 py-1 text-xs rounded bg-red-600 text-white hover:bg-red-700"
                            data-ad-delete-id="{{ $adObj->id ?? '' }}">
                            <x-heroicon-o-trash class="w-3.5 h-3.5" />
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-3 py-4 text-center text-xs text-gray-400">
                        Nenhum anúncio neste conjunto ainda.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>