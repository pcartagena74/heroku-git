<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class MailGunController extends Controller
{
    public function send(Request $request){
        $title = $request->input('title');
        $content = $request->input('content');
        $toaddr = $request->input('to');

        Mail::send('v1.email_templates.basic', ['title' => $title, 'content' => $content], function ($message)
        {

            $message->from('support@mcentric.org', 'mCentric Support');

            $message->to('register@fierce.net');

        });

        return response()->json(['message' => 'Request completed']);
    }
}
