<?php

namespace App\Http\Controllers;

use App\Person;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use RuntimeException;

class MailGunController extends Controller
{
    public function send(Request $request)
    {
        $title = $request->input('title');
        $content = $request->input('content');
        $toaddr = $request->input('to');

        Mail::send('v1.email_templates.basic', ['title' => $title, 'content' => $content], function ($message) {

            $message->from('support@mcentric.org', 'mCentric Support');

            $message->to('register@fierce.net');
        });

        return response()->json(['message' => 'Request completed']);
    }

    public function testmail()
    {
        $this->currentPerson = Person::find(auth()->id());
        $email = 'pcartagena@partners.org';
        $subject = 'test email2';
        $name = 'Phil Cartagena';
        $model = $this->currentPerson;

        Mail::send('v1.email_templates.test', [], function ($message) use ($email, $subject, $name, $model) {
            $message->from('office@pmimassbay.org', 'PMI Office');
            $message->sender('office@pmimassbay.org', 'PMI Office');
            $message->to($email, $name);
            $message->subject($subject);

            // Create a custom header that we can later retrieve
            $message->getHeaders()->addTextHeader('X-Model-ID', $model->id);
        });
    }

    public function bugsnag()
    {

        Bugsnag::notifyException(new RuntimeException("Test error"));
    }
}