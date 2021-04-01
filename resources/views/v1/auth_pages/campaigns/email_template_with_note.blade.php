@php
    /**
     * Comment: View for email builder
     * Created: 13-April-2020
     */
@endphp
@if(!empty($note))
    <table width="100%" cellspacing="0" cellpadding="0" border="0"
           style="background:rgb(233, 234, 234) none repeat scroll 0% 0% / auto padding-box border-box;">
        <tbody>
        <tr>
            <td>
                <div style="margin:0 auto;width:600px;padding:0px">
                    <table class="main" width="100%" cellspacing="0" cellpadding="0" border="0"
                           style="background-color: rgb(255, 255, 255); width: 600px; border-spacing: 0px; border-collapse: collapse; text-size-adjust: 100%;"
                           align="center" data-last-type="background">
                        <tbody>
                        <tr>
                            <td class="element-content" align="left"
                                style="padding: 10px 50px; font-family: Arial; font-size: 13px; color: rgb(0, 0, 0); line-height: 22px; border-collapse: collapse; text-size-adjust: 100%;">
                                <div class="test-text element-contenteditable active">
                                    {{$note}}
                                </div>
                            </td>
                        </tr>
                        </tbody>
                    </table>

                </div>
            </td>
        </tr>
        </tbody>
    </table>
@endif
{!! $html !!}
