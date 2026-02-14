<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class FriendlycaptchaService
{
    protected const API_URL = 'https://api.friendlycaptcha.com/api/v1/siteverify';

    public static function verify(string $solution): bool
    {
        $client = new Client;

        try {
            $res = $client->request('POST', self::API_URL, [
                'form_params' => [
                    'solution' => $solution,
                    'secret' => config('captcha.secret'),
                    'sitekey' => config('captcha.sitekey'),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Could not verify captcha: '.$e->getMessage());

            return true;
        }

        // As per https://docs.friendlycaptcha.com/#/installation?id=verification-best-practices
        if ($res->getStatusCode() > 299) {
            return true;
        }

        $response = json_decode($res->getBody());

        return $response->success;
    }
}
