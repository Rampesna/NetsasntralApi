<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;

class Base
{
    public $_token;

    public function callApi($url, $method, $headers = [], $params = [])
    {
        return Http::withHeaders($headers)->$method($url, $params);
    }

}
