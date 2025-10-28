<?php

namespace App\Services;

abstract class FacebookAbastractService
{
    protected string $accessToken;
    protected string $accountId;   
    protected string $baseUrl = 'https://graph.facebook.com/v23.0/';

    protected function accountPrefix(): string
    {
        return 'act_' . $this->accountId . '/';
    }

    public function __construct()
    {
        $this->accessToken = config('services.facebook.access_token');
        $this->accountId = config('services.facebook.account_id'); 
    }
}
