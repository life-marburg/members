@if(config('captcha.enable'))
    <div class="frc-captcha mt-4 !w-full !border-0" data-sitekey="{{ config('captcha.sitekey') }}"></div>

    @vite('resources/js/captcha.js')
@endif
