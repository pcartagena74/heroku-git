<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta content="text/html; charset=utf-8" http-equiv="Content-Type"/>
        <meta content="width=device-width, initial-scale=1" name="viewport"/>
        <style type="text/css">
            @import url(http://fonts.googleapis.com/css?family=Droid+Sans);

      img {
        max-width: 600px;
        outline: none;
        text-decoration: none;
        -ms-interpolation-mode: bicubic;
    }

    a {
        text-decoration: none;
        border: 0;
        outline: none;
        color: #bbbbbb;
    }

    a img {
        border: none;
    }

    td, h1, h2, h3  {
        font-family: Helvetica, Arial, sans-serif;
        font-weight: 400;
    }

    td {
        text-align: center;
    }

    body {
        -webkit-font-smoothing:antialiased;
        -webkit-text-size-adjust:none;
        width: 100%;
        height: 100%;
        color: {{ $setting->grab('email.color_body_bg') }};
        background: #ffffff;
        font-size: 16px;
    }

    table {
        border-collapse: collapse !important;
    }

    .subject {
        color: #ffffff;
        font-size: 36px;
    }

    .force-full-width {
      width: 100% !important;
  }
        </style>
        <style media="screen" type="text/css">
            @media screen {
    td, h1, h2, h3 {
      font-family: 'Droid Sans', 'Helvetica Neue', 'Arial', 'sans-serif' !important;
  }
}
        </style>
        <style media="only screen and (max-width: 480px)" type="text/css">
            @media only screen and (max-width: 480px) {

      table[class="w320"] {
        width: 320px !important;
    }
}
        </style>
    </head>
    <body bgcolor="#fff" class="body" style="padding:0; margin:0; display:block; background:#fff; -webkit-text-size-adjust:none">
        <br>
            <table align="center" cellpadding="0" cellspacing="0" height="100%" width="100%">
                <tr>
                    <td align="center" bgcolor="#fff" valign="top" width="100%">
                        <center>
                            <table cellpadding="0" cellspacing="0" class="w320" style="margin: 0 auto;" width="600">
                                <tr>
                                    <td align="center" valign="top">
                                        <table bgcolor="{{ $setting->grab('email.color_header_bg') }}" cellpadding="0" cellspacing="0" style="margin: 0 auto;" width="100%">
                                            <tr>
                                                <td class="subject">
                                                    <br>
                                                        @yield('subject')
                                                    </br>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <center>
                                                        <table cellpadding="0" cellspacing="0" width="80%">
                                                            <tr>
                                                                <td align="left" style="margin: 20px; text-align: left color:#187272;">
                                                                    <br>
                                                                        @yield('content')
                                                                        <br>
                                                                            <br>
                                                                            </br>
                                                                        </br>
                                                                    </br>
                                                                </td>
                                                            </tr>
                                                        </table>
                                                    </center>
                                                </td>
                                            </tr>
                                        </table>
                                        <table bgcolor="{{ $setting->grab('email.color_content_bg') }}" cellpadding="0" cellspacing="0" style="margin: 0 auto;" width="100%">
                                            <tr>
                                                <td>
                                                    <center>
                                                        <table cellpadding="0" cellspacing="0" style="margin: 0 auto;" width="60%">
                                                            <tr>
                                                                <td style="color:#933f24;">
                                                                    <br>
                                                                        @yield('link')
                                                                        <br>
                                                                            <br>
                                                                            </br>
                                                                        </br>
                                                                    </br>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td style="color:#933f24;">
                                                                    {{ $setting->grab('email.signoff') }}
                                                                    <br>
                                                                        {{ $setting->grab('email.signature') }}
                                                                        <br>
                                                                            <br>
                                                                            </br>
                                                                        </br>
                                                                    </br>
                                                                </td>
                                                            </tr>
                                                        </table>
                                                    </center>
                                                </td>
                                            </tr>
                                            @if(App\Models\Ticketit\AgentOver::isAdmin())
                                            <tr>
                                                <td>
                                                    <div>
                                                        <!--[if mso]>
                        <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="http://" style="height:50px;v-text-anchor:middle;width:200px;" arcsize="8%" stroke="f" fillcolor="{{ $setting->grab('email.color_button_bg') }}">
                          <w:anchorlock/>
                          <center>
                            <![endif]-->
                                                        <a href="{{ url($setting->grab('admin_route')) }}" style="background-color:{{ $setting->grab('email.color_button_bg') }};border-radius:4px;color:#ffffff;display:inline-block;font-family: Helvetica, Arial, sans-serif;font-size:16px;font-weight:bold;line-height:50px;text-align:center;text-decoration:none;width:200px;-webkit-text-size-adjust:none;">
                                                            {{ $setting->grab('email.dashboard') }}
                                                        </a>
                                                        <!--[if mso]>
                          </center>
                        </v:roundrect>
                        <![endif]-->
                                                    </div>
                                                    <br>
                                                        <br>
                                                        </br>
                                                    </br>
                                                </td>
                                            </tr>
                                            @endif
                                        </table>
                                        <table bgcolor="{{ $setting->grab('email.color_footer_bg') }}" cellpadding="0" cellspacing="0" class="force-full-width" style="margin: 0 auto">
                                            <tr>
                                                <td style="background-color:{{ $setting->grab('email.color_footer_bg') }};">
                                                    <br>
                                                        <br>
                                                            @if( (bool) $setting->grab('email.google_plus_link') )
                                                            <a href="{{ $setting->grab('email.google_plus_link') }}">
                                                                <img alt="google+" src="https://www.filepicker.io/api/file/R4VBTe2UQeGdAlM7KDc4"/>
                                                            </a>
                                                            @endif
                      @if( (bool) $setting->grab('email.facebook_link') )
                                                            <a href="{{ $setting->grab('email.facebook_link') }}">
                                                                <img alt="facebook" src="https://www.filepicker.io/api/file/cvmSPOdlRaWQZnKFnBGt"/>
                                                            </a>
                                                            @endif
                      @if( (bool) $setting->grab('email.twitter_link') )
                                                            <a href="{{ $setting->grab('email.twitter_link') }}">
                                                                <img alt="twitter" src="https://www.filepicker.io/api/file/Gvu32apSQDqLMb40pvYe"/>
                                                            </a>
                                                            @endif
                                                            <br>
                                                                <br>
                                                                </br>
                                                            </br>
                                                        </br>
                                                    </br>
                                                </td>
                                            </tr>
                                            <!-- Not implemented
            <tr>
                <td style="color:#bbbbbb; font-size:12px;">
                  <a href="#">View in browser</a> | <a href="#">Unsubscribe</a> | <a href="#">Contact</a>
                  <br><br>
              </td>
          </tr>
          -->
                                            <tr>
                                                <td style="color:#933f24; font-size:12px;">
                                                    <a href="{{ $setting->grab('email.footer_link') }}">
                                                        {{ $setting->grab('email.footer') }}
                                                    </a>
                                                    <br>
                                                        <br>
                                                        </br>
                                                    </br>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </center>
                    </td>
                </tr>
            </table>
        </br>
    </body>
</html>
