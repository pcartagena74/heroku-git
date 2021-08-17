<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;

class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    public function showLinkRequestForm_inModal()
    {
        $message = view('v1.modals.email')->renderSections()['content'];
        //json_encode(array("status" => "success", "message" => $message));
        return json_encode(['status' => 'success', 'message' => $message]);
    }

    public function rules()
    {
        return [
            'email'    => 'required|email|max:255',
            'g-recaptcha-response' => 'required|recaptcha',
        ];
    }
}
