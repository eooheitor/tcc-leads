<?php

namespace App\Http\Controllers;

use App\Exceptions\FacebookApiException;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

abstract class Controller
{
    protected function jsonSuccess(array $data = [], string $message = 'OK', int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
        ] + $data, $status);
    }

    protected function jsonError(string $message, int $status = 400, array $extra = []): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ] + $extra, $status);
    }

    protected function isAjax(Request $request): bool
    {
        return $request->ajax() || $request->wantsJson();
    }

    protected function validateUsing(Request $request, array $rules, array $messages = []): array
    {
        return $request->validate($rules, $messages);
    }

    protected function toCents(?string $brl): ?int
    {
        if ($brl === null || $brl === '') return null;
        $normalized = str_replace('.', '', $brl);
        $normalized = str_replace(',', '.', $normalized);
        $val = floatval($normalized);
        return (int) round($val * 100);
    }

    protected function fromCentsToBrl(int|string|null $cents): string
    {
        $v = is_numeric($cents) ? ((int) $cents) / 100 : 0;
        return number_format($v, 2, ',', '.');
    }

    protected function extractSingleCategory(Request $request, string $keyBase = 'special_ad_categories'): ?string
    {
        $raw = $request->input($keyBase);
        if (is_array($raw)) {
            return $raw[0] ?? null;
        }
        if (is_string($raw) && $raw !== '') {
            return $raw;
        }
        $raw2 = $request->input($keyBase . '.*');
        if (is_array($raw2)) {
            return $raw2[0] ?? null;
        }
        return null;
    }

    protected function handleFacebookException(FacebookApiException $e): JsonResponse
    {
        $status  = method_exists($e, 'getStatus') ? $e->getStatus() : 502;
        $message = method_exists($e, 'getUserMessage') ? $e->getUserMessage() : 'Erro na comunicação com o Facebook API.';
        return $this->jsonError($message, $status);
    }
}
