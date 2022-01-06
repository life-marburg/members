@if(config('captcha.enable'))
    <div class="frc-captcha mt-4 !w-full !border-0" data-sitekey="{{ config('captcha.sitekey') }}"></div>

    <script src="{{ asset('js/captcha.js') }}" async defer></script>
@endif
