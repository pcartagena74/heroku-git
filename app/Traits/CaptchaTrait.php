<?php
/**
 * Comment: created for use with Google Captcha front-end interface
 *          per: https://tuts.codingo.me/google-recaptcha-in-laravel-application
 * Created: 3/16/2017
 */

namespace App\Traits;

use Illuminate\Support\Facades\Request;
use ReCaptcha\ReCaptcha;

trait CaptchaTrait
{
    public function captchaCheck()
    {
        $response = Request::get('g-recaptcha-response');
        $remoteip = $_SERVER['REMOTE_ADDR'];
        $secret = env('RECAPTCHA_PRIVATE_KEY');

        $recaptcha = new ReCaptcha($secret);
        $resp = $recaptcha->verify($response, $remoteip);
        if ($resp->isSuccess()) {
            return 1;
        } else {
            return 0;
        }
    }
}
