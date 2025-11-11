<?php

namespace App\Services;

use Carbon\Carbon;

class LeadService
{
    /**
     * Mock dos leads da Meta dos últimos $days dias.
     * Estrutura simulando o retorno de /{form_id}/leads.
     */
    public function fetchRecentLeadsMock(int $days = 7): array
    {
        // Aqui você poderia variar datas etc. Por enquanto, fixo só pra testes.
        return [
            [
                'id'           => 'MOCK_LEAD_1',
                'created_time' => Carbon::now()->subDays(1)->toIso8601String(),
                'field_data'   => [
                    [ 'name' => 'full_name',     'values' => ['Fulano da Silva'] ],
                    [ 'name' => 'phone_number',  'values' => ['(47) 99999-0001'] ],
                    [ 'name' => 'city',          'values' => ['Blumenau'] ],
                    [ 'name' => 'procurava_oque','values' => ['Apartamento 2 quartos'] ],
                    [ 'name' => 'edificacao',    'values' => ['Residencial Jardim Azul'] ],
                ],
            ],
            [
                'id'           => 'MOCK_LEAD_2',
                'created_time' => Carbon::now()->subDays(2)->toIso8601String(),
                'field_data'   => [
                    [ 'name' => 'first_name',    'values' => ['Maria'] ],
                    [ 'name' => 'last_name',     'values' => ['Oliveira'] ],
                    [ 'name' => 'phone',         'values' => ['47 98888-1234'] ],
                    [ 'name' => 'cidade',        'values' => ['Gaspar'] ],
                    [ 'name' => 'interesse',     'values' => ['Casa geminada'] ],
                    [ 'name' => 'edificacao',     'values' => [''] ],
                ],
            ],
        ];
    }
}
