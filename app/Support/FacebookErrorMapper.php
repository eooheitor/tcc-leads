<?php

namespace App\Support;

class FacebookErrorMapper
{
    /**
     * Recebe o array "error" da Graph API e devolve
     * ['message' => 'texto amigável', 'status' => 4xx/5xx]
     */
    public static function map(array $e): array
    {
        $code    = (int)($e['code'] ?? 0);
        $sub     = (int)($e['error_subcode'] ?? 0);
        $msg     = mb_strtolower((string)($e['message'] ?? ''));
        $type    = (string)($e['type'] ?? '');

        if ($sub === 2446375 || str_contains($msg, 'budget') && str_contains($msg, 'too low')) {
            return [
                'message' => 'Orçamento diário de campanha muito baixo. Aumente o valor (R$ 10,00 ou mais).',
                'status'  => 422,
            ];
        }

        if ($code === 100 && str_contains($msg, 'objective') && str_contains($msg, 'invalid')) {
            return [
                'message' => 'Objetivo inválido para a campanha. Selecione um objetivo suportado.',
                'status'  => 422,
            ];
        }

        if ($code === 100 && in_array($sub, [33, 32], true)) {
            return [
                'message' => 'Operação não suportada para este recurso ou ID inválido.',
                'status'  => 400,
            ];
        }

        if ($code === 190 || ($type === 'OAuthException' && str_contains($msg, 'token'))) {
            return [
                'message' => 'Sessão expirada. Faça login novamente para continuar (token inválido/expirado).',
                'status'  => 401,
            ];
        }

        if (in_array($code, [10, 200], true)) {
            return [
                'message' => 'Permissões insuficientes. Garanta que o token possua ads_management/ads_read e acesso à conta.',
                'status'  => 403,
            ];
        }

        if (in_array($code, [4, 17], true) || str_contains($msg, 'rate limit')) {
            return [
                'message' => 'Muitas requisições no momento. Tente novamente em instantes.',
                'status'  => 429,
            ];
        }

        if ($code === 803 || str_contains($msg, 'does not exist')) {
            return [
                'message' => 'Recurso não encontrado. Verifique o ID informado.',
                'status'  => 404,
            ];
        }

        if ($code === 368) {
            return [
                'message' => 'Ação temporariamente bloqueada pelo Facebook. Tente novamente mais tarde.',
                'status'  => 423,
            ];
        }

        return [
            'message' => 'Não foi possível completar a operação na Meta. Tente novamente ou contate o suporte.',
            'status'  => 422,
        ];
    }
}
