<?php

namespace App\Services;

abstract class FacebookAbastractService
{
    protected $accessToken;
    protected $accountId = 'act_1179676623705612';
    protected $baseUrl = "https://graph.facebook.com/v23.0/";

    public function __construct()
    {
        $this->accessToken = config('services.facebook.access_token');
        $this->accountId = config('services.facebook.account_id');
    }
}