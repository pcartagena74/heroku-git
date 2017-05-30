<?php
/**
 * Comment: Privacy and Return Policy page
 * Created: 3/18/2017
 */
?>
@extends('v1.layouts.no-auth_no-nav')

@section('content')

<div class="col-md-offset-1 col-sm-offset-1 col-md-10 col-sm-10 col-xs-10" style="padding-top: 20px;">
    <div class="container">
        <p>You can see all of mCentric's currently-active policies.</p>
        <ul class="nav nav-tabs">
            <li class="active"><a data-toggle="tab" href="#privacy">Privacy Policy</a></li>
            <li><a data-toggle="tab" href="#refunds">Refund Policy</a></li>
            <li><a data-toggle="tab" href="#terms">Terms of Service</a></li>
        </ul>

        <div class="tab-content">
            <div id="privacy" class="tab-pane fade in active">
                <h2>mCentric's Privacy Policy</h2>
                <ol>
                    <li><b>Definitions</b></li>
                    <br/>
                    <ul>
                        <li><b>Client:</b> mCentric clients are the chapters, associations, or other organizations who
                            sign up with mCentric to facilitate the management of their member events and data.
                        </li>
                        <li><b>User:</b> mCentric users include any individual associated with a client who needs to
                            register for any mCentric-facilitated service a client may be offering to its members.
                        </li>
                    </ul>
                    <br/>

                    <li><b>Privacy Policy</b>
                        <br/>
                        mCentric is committed to protecting private information of all users of the site. mCentric has no
                        intention to sell, rent or otherwise provide any user's personal information to any third party
                        unless required by law.
                    </li>
                    <br/>

                    <li><b>User Information Collection</b><br/>
                        mCentric is provided with information from our clients on their members in order to simplify
                        their users' interaction with our site. mCentric may collect and/or facilitate the collection,
                        of additional information in conjunction with the purchase of any client services.  <br />
                        No personal financial data of any kind is stored in mCentric's database and as such, cannot
                        be subject to compromise.
                    </li>
                    <br/>

                    <li><b>Disclosure of Personal Information</b><br/>
                        mCentric facilitates the sending of billing information to our third party payment processor,
                        Stripe.com. No other information is disclosed to any other party for any reason, except where
                        required by law. mCentric will adhere to any local, state, and federal laws that require
                        providing your information.
                    </li>
                    <br/>

                    <li><b>Disclosure of Non-Personal Information</b><br/>
                        In the course of using mCentric to facilitate the services of its clients, non-personal
                        information is collected
                        (e.g., Company name, Industry, etc.) that may be aggregated into reports for its clients.
                    </li>
                    <br/>

                    <li><b>Use of Cookies</b><br/>
                        mCentric.org makes limited use of cookies in order to facilitate the user experience on the
                        site. A cookie is a text file stored
                        on your hard drive by our site that saves information for the site to "remember" you. For
                        example: if you check the "remember me"
                        checkbox on our login form, a cookie can be created to do just that.<p></p>
                        Cookies do not run programs or viruses. mCentric does not use cookies to store sensitive data.
                    </li>
                    <br/>

                    <li><b>Security of Personal Data</b><br/>
                        mCentric uses Secured Socket Layer (SSL) technology to provide you with the safest, most secure
                        purchase experience possible.
                        SSL technology enables encryption of sensitive information, including passwords and credit card
                        numbers, during online transactions.
                        <p></p>
                        Passwords are encrypted before getting stored in our database and the hardware and software that
                        runs mCentric.org is hosted by Heroke.com, a Salesforce Company.
                    </li>
                    <br/>

                    <li><b>Links to Third Party Sites</b><br/>
                        mCentric may link to other web sites unrelated to mCentric, including its clients' websites.
                        These sites are not under the control nor maintained by mCentric. Such links do not constitute
                        an endorsement by mCentric of those other sites, the content displayed therein, or the persons
                        or entities associated therewith. You acknowledge that mCentric is providing these links to you
                        only as a convenience, and you agree that mCentric is not responsible for the content of such
                        sites. Your use of these other sites is subject to the respective terms of use and privacy
                        policies located on those sites.
                    </li>
                    <br/>

                    <li><b>Acceptance</b><br/>
                        Use of mCentric.org signifies your acceptance to this privacy notice. If you do not agree, do
                        not use this site.
                        Your continued use of mCentric.org after any changes to this privacy policy indicates your
                        agreement to those changes.
                    </li>
                    <br/>

                    <li><b>Questions/Comments</b><br/>
                        You can direct questions about this policy to questions at mCentric dot org.
                    </li>
                    <br/>

                </ol>

            </div>
            <div id="refunds" class="tab-pane fade">
                <h2>mCentric's Refund Policy</h2>
                <ol>
                    <li><b>mCentric Client Services</b><br/>
                        mCentric offers the use of its site to clients for a recurring monthly or annual fee.<br />
                        mCentric may also provide payment processing for events that clients may wish to hold.<br />
                        Fees associated with processed transactions are not refundable.<p>
                        Clients that wish to terminate the use of mCentric's services may do so in writing via the
                        mCentric support interface.
                    </li>
                    <br/>

                    <li><b>mCentric Client Event Registrations</b>
                        mCentric will honor the refund policy for individual event refunds based on the refund policy
                        set forth by the client. The client continues to be responsible for any fees associated with
                        canceled registrations.
                    </li>
                    <br/>
                </ol>

            </div>
            <div id="terms" class="tab-pane fade">
                <h2>mCentric's Terms of Service</h2>
                <p>Last updated: March 18, 2017</p>
                <p>Please read these Terms of Service (&quot;Terms&quot;, &quot;Terms of Service&quot;) carefully before
                    using the www.mCentric.org website (the &quot;Service&quot;) operated by mCentric (&quot;us&quot;,
                    &quot;we&quot;, or &quot;our&quot;).</p>
                <p>Your access to and use of the Service is conditioned on your acceptance of and compliance with these
                    Terms. These Terms apply to all visitors, users and others who access or use the Service.</p>
                <p>By accessing or using the Service you agree to be bound by these Terms. If you disagree with any part of
                    the terms then you may not access the Service.
                </p>
                <p><strong>Accounts</strong></p>
                <p>When you create an account with us, you must provide us information that is accurate, complete, and
                    current at all times. Failure to do so constitutes a breach of the Terms, which may result in immediate
                    termination of your account on our Service.</p>
                <p>You are responsible for safeguarding the password that you use to access the Service and for any
                    activities or actions under your password, whether your password is with our Service or a third-party
                    service.</p>
                <p>You agree not to disclose your password to any third party. You must notify us immediately upon becoming
                    aware of any breach of security or unauthorized use of your account.</p>
                <p><strong>Links To Other Web Sites</strong></p>
                <p>Our Service may contain links to third-party web sites or services that are not owned or controlled by
                    mCentric.</p>
                <p>mCentric has no control over, and assumes no responsibility for, the content, privacy policies, or
                    practices of any third party web sites or services. You further acknowledge and agree that mCentric
                    shall not be responsible or liable, directly or indirectly, for any damage or loss caused or alleged to
                    be caused by or in connection with use of or reliance on any such content, goods or services available
                    on or through any such web sites or services.</p>
                <p>We strongly advise you to read the terms and conditions and privacy policies of any third-party web sites
                    or services that you visit.</p>
                <p><strong>Termination</strong></p>
                <p>We may terminate or suspend access to our Service immediately, without prior notice or liability, for any
                    reason whatsoever, including without limitation if you breach the Terms.</p>
                <p>All provisions of the Terms which by their nature should survive termination shall survive termination,
                    including, without limitation, ownership provisions, warranty disclaimers, indemnity and limitations of
                    liability.</p>
                <p>We may terminate or suspend your account immediately, without prior notice or liability, for any reason
                    whatsoever, including without limitation if you breach the Terms.</p>
                <p>Upon termination, your right to use the Service will immediately cease. If you wish to terminate your
                    account, you may simply discontinue using the Service.</p>
                <p>All provisions of the Terms which by their nature should survive termination shall survive termination,
                    including, without limitation, ownership provisions, warranty disclaimers, indemnity and limitations of
                    liability.</p>
                <p><strong>Governing Law</strong></p>
                <p>These Terms shall be governed and construed in accordance with the laws of Massachusetts, United States,
                    without regard to its conflict of law provisions.</p>
                <p>Our failure to enforce any right or provision of these Terms will not be considered a waiver of those
                    rights. If any provision of these Terms is held to be invalid or unenforceable by a court, the remaining
                    provisions of these Terms will remain in effect. These Terms constitute the entire agreement between us
                    regarding our Service, and supersede and replace any prior agreements we might have between us regarding
                    the Service.</p>
                <p><strong>Changes</strong></p>
                <p>We reserve the right, at our sole discretion, to modify or replace these Terms at any time. If a revision
                    is material we will try to provide at least 30 days notice prior to any new terms taking effect. What
                    constitutes a material change will be determined at our sole discretion.</p>
                <p>By continuing to access or use our Service after those revisions become effective, you agree to be bound
                    by the revised terms. If you do not agree to the new terms, please stop using the Service.</p>
                <p><strong>Contact Us</strong></p>
                <p>If you have any questions about these Terms, please contact us.</p>

            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // function([string1, string2],target id,[color1,color2])
    consoleText(['Marketing', 'Mailings', 'Meetings', 'Integrated Membership Management'], 'text', ['black', 'black', 'black']);

    function consoleText(words, id, colors) {
        if (colors === undefined) colors = ['black'];
        var visible = true;
        var con = document.getElementById('console');
        var letterCount = 1;
        var x = 1;
        var waiting = false;
        var target = document.getElementById(id);
        target.setAttribute('style', 'color:' + colors[0]);
        window.setInterval(function () {

            if (letterCount === 0 && waiting === false) {
                waiting = true;
                target.innerHTML = words[0].substring(0, letterCount);
                window.setTimeout(function () {
                    var usedColor = colors.shift();
                    colors.push(usedColor);
                    var usedWord = words.shift();
                    words.push(usedWord);
                    x = 1;
                    target.setAttribute('style', 'color:' + colors[0]);
                    letterCount += x;
                    waiting = false;
                }, 1500);
            } else if (letterCount === words[0].length + 1 && waiting === false) {
                waiting = true;
                window.setTimeout(function () {
                    x = -1;
                    letterCount += x;
                    waiting = false;
                }, 1000)
            } else if (waiting === false) {
                if (x === -1) {
                    target.innerHTML = '';
                    letterCount = 0;
                } else {
                    target.innerHTML = words[0].substring(0, letterCount);
                    letterCount += x;
                }
            }
        }, 60);
        window.setInterval(function () {
            if (visible === true) {
                con.className = 'console-underscore hidden';
                visible = false;

            } else {
                con.className = 'console-underscore';
                visible = true;
            }
        }, 2000);
    }
</script>
@endsection
