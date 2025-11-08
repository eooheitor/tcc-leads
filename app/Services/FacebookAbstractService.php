<?php

namespace App\Services;

use App\Exceptions\FacebookApiException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

abstract class FacebookAbstractService
{
    protected string $accessToken;
    protected string $accountId;
    protected string $baseUrl = 'https://graph.facebook.com/v23.0/';

    public function __construct()
    {
        $this->accessToken = (string) config('services.facebook.access_token');
        $this->accountId   = (string) config('services.facebook.account_id');
    }

    protected function accountPrefix(): string
    {
        return 'act_' . $this->accountId . '/';
    }

    protected function urlForAccount(string $resource): string
    {
        return "{$this->baseUrl}{$this->accountPrefix()}{$resource}";
    }

    protected function urlForId(string $id): string
    {
        return "{$this->baseUrl}{$id}";
    }

    protected function fields(array $fields): string
    {
        return implode(',', $fields);
    }

    protected function get(string $url, array $query = []): Response
    {
        return Http::get($url, ['access_token' => $this->accessToken] + $query);
    }

    protected function postForm(string $url, array $data = []): Response
    {
        return Http::asForm()->post($url, ['access_token' => $this->accessToken] + $data);
    }

    protected function delete(string $url, array $query = []): Response
    {
        return Http::delete($url, ['access_token' => $this->accessToken] + $query);
    }

    protected function throwIfFailed(Response $resp): void
    {
        if ($resp->failed()) {
            throw FacebookApiException::fromResponse($resp);
        }
    }

    protected function unwrapData(Response $resp): array
    {
        $json = $resp->json();
        return $json['data'] ?? ($json ?? []);
    }

    protected function ensureId(string $id, string $label = 'id'): string
    {
        $id = trim($id);
        if ($id === '') {
            throw new \InvalidArgumentException("{$label} inválido.");
        }
        return $id;
    }

    protected function cleanIntString(int|string|null $value): ?string
    {
        if ($value === null || $value === '') return null;
        return (string) preg_replace('/\D/', '', (string) $value);
    }
}
