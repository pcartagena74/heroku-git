<?php

namespace App\Rules;

use GuzzleHttp\Client;
use Illuminate\Contracts\Validation\Rule;

class GoogleCaptcha implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  mixed  $value
     */
    public function passes(string $attribute, $value): bool
    {
        $client = new Client;
        $response = $client->post(
            'https://www.google.com/recaptcha/api/siteverify',
            ['form_params' => [
                'secret' => env('RECAPTCHA_PRIVATE_KEY'),
                'response' => $value,
            ],
            ]
        );

        $body = json_decode((string) $response->getBody());

        return $body->success;
    }

    /**
     * Get the validation error message.
     */
    public function message(): string
    {
        return trans('messages.errors.google_recaptcha');
    }
}
