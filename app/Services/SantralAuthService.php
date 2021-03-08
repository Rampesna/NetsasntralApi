<?php

namespace App\Services;

use Illuminate\Support\Facades\Cookie;

class SantralAuthService
{
    public function login()
    {
        $params = [
            'input_user' => env('NETSANTRAL_USER'),
            'input_pass' => env('NETSANTRAL_PASSWORD')
        ];

        $curl = curl_init('http://uyumsoft.netasistan.com/login_check');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
        curl_setopt($curl, CURLOPT_HEADER, 1);
        $response = curl_exec($curl);

        preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $response, $matches);

        $cookies = [];

        foreach ($matches[1] as $item) {
            parse_str($item, $cookie);
            $cookies = array_merge($cookies, $cookie);
        }

        session(['MY_PHPSESSID' => $cookies["PHPSESSID"]]);
    }
}
