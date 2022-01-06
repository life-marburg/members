<?php

namespace App\Rules;

use App\Services\FriendlycaptchaService;
use Illuminate\Contracts\Validation\Rule;

class FriendlyCaptcha implements Rule
{
    private FriendlycaptchaService $friendlyCaptchaService;

    public function __construct(FriendlycaptchaService $friendlycaptchaService)
    {
        $this->friendlyCaptchaService = $friendlycaptchaService;
    }

    public function passes($attribute, $value): bool
    {
        return $this->friendlyCaptchaService->verify($value);
    }

    public function message(): string
    {
        return __('The captcha is invalid.');
    }
}
