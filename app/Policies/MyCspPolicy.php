<?php

namespace App\Policies;

use Spatie\Csp\Directive;
use Spatie\Csp\Keyword;
use Spatie\Csp\Policies\Basic;
use Spatie\Csp\Nonce\RandomString;
use Illuminate\Support\Facades\View;

class MyCspPolicy extends Basic
{
    public function configure(): void
    {
        // Generate nonces for script and style
        $scriptNonce = (new RandomString)->generate();
        $styleNonce = (new RandomString)->generate();

        // Share nonces with views
        View::share('cspScriptNonce', $scriptNonce);
        View::share('cspStyleNonce', $styleNonce);

        $this
            ->addDirective(Directive::DEFAULT, Keyword::SELF)
            ->addDirective(Directive::FRAME, [
                Keyword::SELF,
                '*.google.com',
                '*.google.it',
                '*.stripe.com'
            ])
            ->addDirective(Directive::FRAME_ANCESTORS, [
                Keyword::SELF,
                '*.pmimassbay.org'
            ])
            ->addDirective(Directive::CONNECT, [
                Keyword::SELF,
                '*.fontawesome.com',
                '*.stripe.com',
                'wss:',
                '*.amazonaws.com'
            ])
            ->addDirective(Directive::SCRIPT, [
                Keyword::SELF,
                "nonce-{$scriptNonce}",
                Keyword::UNSAFE_INLINE,
                '*.bootstrapcdn.com',
                '*.cloudflare.com',
                '*.datatables.net',
                '*.fontawesome.com',
                '*.github.io',
                '*.google.com',
                '*.googleapis.com',
                '*.googletagmanager.com',
                '*.gstatic.com',
                '*.jquery.com',
                '*.jsdelivr.net',
                '*.mcentric.org',
                '*.newrelic.com',
                '*.stripe.com',
                '*.unpkg.com',
            ])
            ->addDirective(Directive::STYLE, [
                Keyword::SELF,
                Keyword::UNSAFE_INLINE,
                "nonce-{$styleNonce}",
                '*.mcentric.org',
                '*.cloudflare.com',
                '*.github.io',
                '*.googleapis.com',
                'cdn.datatables.net',
                '*.bootstrapcdn.com',
                '*.fontawesome.com'
            ])
            ->addDirective(Directive::FONT, [
                Keyword::SELF,
                'data:',
                '*.gstatic.com',
                '*.cloudflare.com',
                '*.bootstrapcdn.com',
                '*.fontawesome.com'
            ])
            ->addDirective(Directive::IMG, [
                Keyword::SELF,
                'data:',
                '*.amazonaws.com',
                'cdn.datatables.net',
                '*.stripe.com'
            ])
            ->addDirective(Directive::FORM_ACTION, [
                Keyword::SELF,
                '*.stripe.com'
            ])
            ->addDirective(Directive::OBJECT, Keyword::NONE)
            ->addDirective(Directive::BASE, Keyword::SELF);
    }
}