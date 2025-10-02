@extends('layouts.app')

@section('content')
<div class="flex flex-col items-center min-h-screen bg-gray-100 pt-16">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 w-full max-w-3xl">
        <div class="bg-white rounded-lg shadow flex flex-col items-center justify-center p-8 h-64 w-full">
            <div class="text-4xl font-bold text-black mb-2">{{ $clientesCount }}</div>
            <div class="text-lg text-gray-600">Clientes cadastrados</div>
        </div>
        <div class="bg-white rounded-lg shadow flex flex-col items-center justify-center p-8 h-64 w-full">
            <div class="text-4xl font-bold text-black mb-2">{{ $mensagensCount }}</div>
            <div class="text-lg text-gray-600">Mensagens cadastradas</div>
        </div>
    </div>
</div>
@endsection
