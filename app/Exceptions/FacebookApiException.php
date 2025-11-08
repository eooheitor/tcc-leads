<?php

namespace App\Exceptions;

use Illuminate\Http\Client\Response;
use Exception;

class FacebookApiException extends Exception
{
    protected int $status = 400;
    protected array $raw = [];

    public static function fromResponse(Response $resp): self
    {
        $json = $resp->json() ?? [];
        $err  = $json['error'] ?? [];
        $msg  = $err['error_user_msg']
             ?? $err['message']
             ?? 'Falha na API do Facebook.';

        $ex = new self($msg, (int)($err['code'] ?? 0));
        $ex->status = $resp->status();
        $ex->raw    = [
            'status'        => $resp->status(),
            'code'          => $err['code']        ?? null,
            'subcode'       => $err['error_subcode'] ?? null,
            'type'          => $err['type']        ?? null,
            'user_title'    => $err['error_user_title'] ?? null,
            'user_msg'      => $err['error_user_msg']  ?? null,
            'fbtrace_id'    => $err['fbtrace_id']  ?? null,
            'full'          => $json,
        ];

        return $ex;
    }

    public function getStatus(): int { return $this->status; }
    public function getRaw(): array   { return $this->raw; }
    public function getUserMessage(): ?string { return $this->message ?: null; }
}
