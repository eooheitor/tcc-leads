@php
/** @var \App\View\Forms\AdSetForm $form */
@endphp

<div class="space-y-4">
    {{-- Tabs --}}
    <div class="border-b border-gray-200">
        <nav class="flex space-x-4">
            {{-- Aba Conjunto --}}
            <button
                type="button"
                @click="activeTab = 'conjunto'"
                :class="[
                'px-4 py-2 text-sm font-medium',
                activeTab === 'conjunto'
                    ? 'border-b-2 border-black text-black'
                    : 'border-b-2 border-transparent text-gray-500'
            ].join(' ')">
                Conjunto
            </button>

            {{-- Aba Anúncios --}}
            <button
                type="button"
                @click="adsEnabled && (activeTab = 'anuncios')"
                :disabled="!adsEnabled"
                :title="adsEnabled ? '' : 'Salve o conjunto para gerenciar os anúncios.'"
                :class="[
                'px-4 py-2 text-sm font-medium',
                adsEnabled ? '' : 'cursor-not-allowed text-gray-300',
                activeTab === 'anuncios'
                    ? 'border-b-2 border-black text-black'
                    : 'border-b-2 border-transparent text-gray-500'
            ].join(' ')">
                Anúncios
            </button>
        </nav>
    </div>

    {{-- ================= ABA CONJUNTO ================= --}}
    <section x-show="activeTab === 'conjunto'" x-cloak class="space-y-4">
        <div>
            <h3 class="text-sm font-semibold text-gray-800 mb-2">
                Configurações do Conjunto
            </h3>
            <p class="text-xs text-gray-500">
                Defina campanha, orçamento, segmentação e período de veiculação.
            </p>
        </div>

        {{-- Campos do conjunto (2 colunas) --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            {!! $conjuntoHtml !!}
        </div>

        {{-- Rodapé / botão submit --}}
        <div class="flex justify-end pt-3 border-t border-gray-100">
            <button
                type="submit"
                class="px-4 py-2 bg-black text-white rounded-md hover:bg-[#222] transition">
                {{ $form->isEditMode() ? 'Atualizar Conjunto' : 'Criar Conjunto' }}
            </button>
        </div>
    </section>

    {{-- ================= ABA ANÚNCIOS ================= --}}
    <section x-show="activeTab === 'anuncios'" class="space-y-4">
        {{-- Bloco dos dados do anúncio --}}
        <div class="border border-gray-200 rounded-md p-4 bg-gray-50">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <h3 class="text-sm font-semibold text-gray-800">
                        <span x-show="!currentAdId">Criar novo anúncio</span>
                        <span x-show="currentAdId" x-cloak>Editar anúncio</span>
                    </h3>
                    <p class="text-xs text-gray-500">
                        Este anúncio será vinculado ao conjunto selecionado.
                    </p>
                </div>
            </div>

            {{-- ⚠️ AQUI: wrapper fixo com id, fora do x-html --}}
            <div id="adsetAdsForm" class="space-y-4">
                <div x-html="adsFormHtml"></div>
                <input type="hidden" name="adset_id" :value="currentAdSetId">
            </div>

            <div class="mt-4 flex justify-end">
                <button
                    type="button"
                    @click="handleAdFormSubmit"
                    class="px-4 py-2 bg-black text-white rounded-md hover:bg-[#222] transition">
                    <span x-show="!currentAdId">Criar anúncio</span>
                    <span x-show="currentAdId" x-cloak>Atualizar anúncio</span>
                </button>
            </div>
        </div>

        {{-- Grid de anúncios --}}
        <div class="bg-white p-4 rounded-lg shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-semibold text-gray-800">
                    Anúncios deste conjunto
                </h3>
            </div>

            <div x-ref="adsGridContainer" x-html="adsGridHtml"></div>
        </div>
    </section>

</div>