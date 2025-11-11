@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-b from-gray-50 to-white">
    <!-- Background decor -->
    <div aria-hidden="true" class="pointer-events-none fixed inset-0 -z-10 overflow-hidden">
        <div class="absolute -top-24 -right-24 h-72 w-72 rounded-full bg-indigo-200 blur-3xl opacity-30"></div>
        <div class="absolute -bottom-24 -left-24 h-72 w-72 rounded-full bg-sky-200 blur-3xl opacity-30"></div>
    </div>

    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 pt-10 pb-16">
        <!-- Header -->
        <div class="mb-8 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight text-gray-900">Dashboard</h1>
                <p class="text-sm text-gray-500">Resumo geral do seu Construleads.</p>
            </div>
            <div class="flex items-center gap-2">
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open"
                        class="inline-flex items-center rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50">
                        Últimos {{ $days }} dias
                    </button>

                    <div x-show="open"
                        @click.away="open = false"
                        class="absolute right-0 z-10 mt-2 w-40 rounded-xl border border-gray-200 bg-white shadow-lg">

                        @foreach([15, 30, 45, 60, 90] as $d)
                        <a href="{{ route('/', ['days' => $d]) }}"
                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                            Últimos {{ $d }} dias
                        </a>
                        @endforeach
                    </div>
                </div>
            </div>
            <form method="GET" class="flex gap-3 items-center">

                {{-- Filtro Campanhas --}}
                <select name="campaign_id"
                    class="rounded-xl border-gray-300 text-sm"
                    onchange="this.form.submit()">
                    <option value="">Todas campanhas</option>
                    @foreach($campanhas as $c)
                    <option value="{{ $c['id'] }}" {{ $campaignId == $c['id'] ? 'selected' : '' }}>
                        {{ $c['name'] }}
                    </option>
                    @endforeach
                </select>

                {{-- Filtro AdSets --}}
                <select name="adset_id"
                    class="rounded-xl border-gray-300 text-sm"
                    onchange="this.form.submit()">
                    <option value="">Todos conjuntos</option>
                    @foreach($adSets as $a)
                    <option value="{{ $a['id'] }}" {{ $adSetId == $a['id'] ? 'selected' : '' }}>
                        {{ $a['name'] }}
                    </option>
                    @endforeach
                </select>

                {{-- Filtro de período (você já tem esse aqui) --}}
                <input type="hidden" name="days" value="{{ $days }}" />
            </form>
        </div>

        <!-- Stats grid -->
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            <!-- Clientes -->
            <div class="group relative overflow-hidden rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-100 transition hover:shadow-md">
                <div class="flex items-center justify-between">
                    <div class="rounded-xl bg-indigo-50 p-3 text-indigo-600">
                        <!-- users icon -->
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-width="1.5" d="M16 14a4 4 0 1 1 6 3.464V20h-6v-2.536A3.99 3.99 0 0 1 16 14zM9 13a5 5 0 1 0-5-5 5 5 0 0 0 5 5z" />
                            <path stroke-width="1.5" d="M2 20a7 7 0 0 1 14 0v1H2z" />
                        </svg>
                    </div>
                    <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700">
                        +12% vs. mês ant.
                    </span>
                </div>
                <div class="mt-4">
                    <div class="text-3xl font-semibold tracking-tight text-gray-900">{{ $clientesCount }}</div>
                    <div class="mt-1 text-sm text-gray-500">Clientes cadastrados</div>
                </div>
                <div class="absolute -bottom-6 -right-6 h-20 w-20 rounded-full bg-indigo-100 opacity-0 blur-2xl transition group-hover:opacity-100"></div>
            </div>

            <!-- Mensagens -->
            <div class="group relative overflow-hidden rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-100 transition hover:shadow-md">
                <div class="flex items-center justify-between">
                    <div class="rounded-xl bg-sky-50 p-3 text-sky-600">
                        <!-- message icon -->
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-width="1.5" d="M21 12a8 8 0 1 1-3.2-6.4L22 3l-2 4A7.97 7.97 0 0 1 21 12z" />
                            <path stroke-width="1.5" d="M8 13h8M8 9h5" />
                        </svg>
                    </div>
                    <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700">
                        +5% este mês
                    </span>
                </div>
                <div class="mt-4">
                    <div class="text-3xl font-semibold tracking-tight text-gray-900">{{ $mensagensCount }}</div>
                    <div class="mt-1 text-sm text-gray-500">Mensagens cadastradas</div>
                </div>
                <div class="absolute -bottom-6 -right-6 h-20 w-20 rounded-full bg-sky-100 opacity-0 blur-2xl transition group-hover:opacity-100"></div>
            </div>

            <!-- Alcance -->
            <div class="group relative overflow-hidden rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-100 transition hover:shadow-md">
                <div class="flex items-center justify-between">
                    <div class="rounded-xl bg-indigo-50 p-3 text-indigo-600">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor">
                            <path stroke-width="1.5" d="M3 12h18" />
                        </svg>
                    </div>
                </div>
                <div class="mt-4">
                    <div class="text-3xl font-semibold tracking-tight text-gray-900">{{ number_format($reach) }}</div>
                    <div class="mt-1 text-sm text-gray-500">Alcance</div>
                </div>
            </div>

            <!-- Impressões -->
            <div class="group relative overflow-hidden rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-100 transition hover:shadow-md">
                <div class="flex items-center justify-between">
                    <div class="rounded-xl bg-sky-50 p-3 text-sky-600">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor">
                            <path stroke-width="1.5" d="M4 4h16v16H4z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-4">
                    <div class="text-3xl font-semibold tracking-tight text-gray-900">{{ number_format($impressions) }}</div>
                    <div class="mt-1 text-sm text-gray-500">Impressões</div>
                </div>
            </div>

            <!-- Cliques -->
            <div class="group relative overflow-hidden rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-100 transition hover:shadow-md">
                <div class="flex items-center justify-between">
                    <div class="rounded-xl bg-amber-50 p-3 text-amber-600">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor">
                            <path stroke-width="1.5" d="M5 12h14" />
                        </svg>
                    </div>
                </div>
                <div class="mt-4">
                    <div class="text-3xl font-semibold tracking-tight text-gray-900">{{ number_format($clicks) }}</div>
                    <div class="mt-1 text-sm text-gray-500">Cliques</div>
                </div>
            </div>

            <!-- Leads -->
            @if($leads > 0)
            <div class="group relative overflow-hidden rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-100 transition hover:shadow-md">
                <div class="flex items-center justify-between">
                    <div class="rounded-xl bg-fuchsia-50 p-3 text-fuchsia-600">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor">
                            <circle cx="12" cy="12" r="8" stroke-width="1.5" />
                        </svg>
                    </div>
                </div>
                <div class="mt-4">
                    <div class="text-3xl font-semibold tracking-tight text-gray-900">{{ $leads }}</div>
                    <div class="mt-1 text-sm text-gray-500">Leads</div>
                </div>
            </div>
            @endif

            <!-- Valor investido -->
            <div class="group relative overflow-hidden rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-100 transition hover:shadow-md">
                <div class="flex items-center justify-between">
                    <div class="rounded-xl bg-emerald-50 p-3 text-emerald-600">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor">
                            <path stroke-width="1.5" d="M12 3v18" />
                        </svg>
                    </div>
                </div>
                <div class="mt-4">
                    <div class="text-3xl font-semibold tracking-tight text-gray-900">
                        R$ {{ number_format($spend, 2, ',', '.') }}
                    </div>
                    <div class="mt-1 text-sm text-gray-500">Investido nos últimos 30 dias</div>
                </div>
            </div>

        </div>

        <!-- Optional: mini list / últimas atividades -->
        <!-- <div class="mt-10 grid gap-6 lg:grid-cols-3">
            <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-100 lg:col-span-2">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-gray-900">Últimas atividades</h2>
                    <a href="#" class="text-xs font-medium text-indigo-600 hover:text-indigo-700">Ver tudo</a>
                </div>
                <ul class="divide-y divide-gray-100">
                    <li class="flex items-center justify-between py-3">
                        <div class="flex items-center gap-3">
                            <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
                                    <circle cx="12" cy="12" r="4" />
                                </svg>
                            </span>
                            <div>
                                <p class="text-sm font-medium text-gray-900">Novo cliente cadastrado</p>
                                <p class="text-xs text-gray-500">há 2 horas</p>
                            </div>
                        </div>
                        <span class="text-xs text-gray-500">#CL-1029</span>
                    </li>
                    <li class="flex items-center justify-between py-3">
                        <div class="flex items-center gap-3">
                            <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-sky-50 text-sky-600">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
                                    <circle cx="12" cy="12" r="4" />
                                </svg>
                            </span>
                            <div>
                                <p class="text-sm font-medium text-gray-900">Mensagem recebida</p>
                                <p class="text-xs text-gray-500">há 4 horas</p>
                            </div>
                        </div>
                        <span class="text-xs text-gray-500">canal: WhatsApp</span>
                    </li>
                </ul>
            </div>

            <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
                <h2 class="mb-4 text-sm font-semibold text-gray-900">Metas</h2>
                <div class="space-y-4">
                    <div>
                        <div class="mb-1 flex justify-between text-xs">
                            <span class="text-gray-500">Leads do mês</span>
                            <span class="font-medium text-gray-900">72/100</span>
                        </div>
                        <div class="h-2 w-full overflow-hidden rounded-full bg-gray-100">
                            <div class="h-full w-[72%] rounded-full bg-indigo-500"></div>
                        </div>
                    </div>
                    <div>
                        <div class="mb-1 flex justify-between text-xs">
                            <span class="text-gray-500">Taxa de resposta</span>
                            <span class="font-medium text-gray-900">48%</span>
                        </div>
                        <div class="h-2 w-full overflow-hidden rounded-full bg-gray-100">
                            <div class="h-full w-[48%] rounded-full bg-emerald-500"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div> -->

    </div>
</div>
@endsection