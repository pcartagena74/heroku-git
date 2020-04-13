@php 
if(!isset($campaign)) {
    $campaign = '';
}

$topBits = ''; 

@endphp
@extends('v1.layouts.auth_email_builder', ['topBits' => $topBits])

@section('content')
@php

$blocks_category=get_template_builder_category();


$actual_link = url('/').'/';
$_outputHtml='';
for ($i = 0; $i < sizeof($blocks_category); $i++) {
 $_outputHtml .= '
<li class="elements-accordion-item" data-type="'.strtolower( $blocks_category[$i]['name']) .'">
    <a class="elements-accordion-item-title">
        '. $blocks_category[$i]['name'] .'
    </a>
    ';

            $_outputHtml .= '
    <div class="elements-accordion-item-content">
        <ul class="elements-list">
            ';

            $_items = $blocks= get_template_builder_block_category($blocks_category[$i]['id']);

             for ($j = 0; $j< sizeof($_items); $j++) {
                $_outputHtml .= '
            <li>
                '.
                    '
                <div class="elements-list-item">
                    '.
                    '
                    <div class="preview">
                        '.
                    '
                        <div class="elements-item-icon">
                            '.
                    '
                            <i class="'.$_items[$j]['icon'].'">
                            </i>
                            '.
                    '
                        </div>
                        '.
                    '
                        <div class="elements-item-name">
                            '.
                    $_items[$j]['name'].
                    '
                        </div>
                        '.
                    '
                    </div>
                    ' .
                    '
                    <div class="view">
                        ' .
                    '
                        <div class="sortable-row">
                            '.
                    '
                            <div class="sortable-row-container">
                                ' .
                    '
                                <div class="sortable-row-actions">
                                    ';

                    $_outputHtml .= '
                                    <div class="row-move row-action">
                                        '.
                        '
                                        <i class="fa fa-arrows-alt">
                                        </i>
                                        ' .
                        '
                                    </div>
                                    ';


                    $_outputHtml .= '
                                    <div class="row-remove row-action">
                                        '.
                        '
                                        <i class="fa fa-remove">
                                        </i>
                                        ' .
                        '
                                    </div>
                                    ';


                    $_outputHtml .= '
                                    <div class="row-duplicate row-action">
                                        '.
                        '
                                        <i class="fa fa-files-o">
                                        </i>
                                        ' .
                        '
                                    </div>
                                    ';


                    $_outputHtml .= '
                                    <div class="row-code row-action">
                                        '.
                        '
                                        <i class="fa fa-code">
                                        </i>
                                        '.
                        '
                                    </div>
                                    ';

                $_outputHtml .= '
                                </div>
                                ' .
                    '
                                <div class="sortable-row-content" data-id="'.$_items[$j]['id'].'" data-last-type="'.explode(',',$_items[$j]['property'])[0].'" data-types="'.$_items[$j]['property'].'">
                                    '
                                        .str_replace('[site-url]',$actual_link,$_items[$j]['html']).
                    '
                                </div>
                                ' .
                    '
                            </div>
                            '.
                    '
                        </div>
                        '.
                    '
                    </div>
                    '.
                    '
                </div>
                '.
                    '
            </li>
            ';
            }


            $_outputHtml .= '
        </ul>
    </div>
    ';
            $_outputHtml .= '
</li>
';
     }



@endphp
<link href="{{url('css/demo.css?v=3')}}" rel="stylesheet"/>
<link href="{{url('css/email-editor.css?'.rand(10,1000))}}" rel="stylesheet"/>
<link href="{{url('css/colorpicker.css')}}" rel="stylesheet"/>
<link href="{{url('css/editor-color.css')}}" rel="stylesheet"/>
<link href="{{url('vendor/sweetalert2/dist/sweetalert2.min.css')}}" rel="stylesheet"/>
<div id="email_builder_master">
    <div class="elements-db clear-fix" style="display:none">
        <div class="tab-elements element-tab active">
            <ul class="elements-accordion">
                <?php echo $_outputHtml ?>
            </ul>
        </div>
    </div>
    <div class="editor clear-fix">
    </div>
</div>
<div class="modal fade" id="previewModal" role="dialog">
    <div class="modal-dialog modal-lg">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button class="close" data-dismiss="modal" type="button">
                    ×
                </button>
                <h4 class="modal-title">
                    Preview
                </h4>
            </div>
            <div class="modal-body">
                <div class="">
                    <label for="">
                        URL :
                    </label>
                    <span class="preview_url">
                    </span>
                </div>
                <iframe height="400px" id="previewModalFrame" width="100%">
                </iframe>
            </div>
            <div class="modal-footer">
                <button class="btn btn-default" data-dismiss="modal" type="button">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>
<div id="demp">
</div>
<script src="{{url('vendor/jquery-nicescroll/dist/jquery.nicescroll.min.js')}}">
</script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js">
</script>
<!--for ace editor  -->
<script src="http://cdnjs.cloudflare.com/ajax/libs/ace/1.1.01/ace.js" type="text/javascript">
</script>
<!--for tinymce  -->
<script src="http://cdn.tinymce.com/4/tinymce.min.js">
</script>
<script src="{{url('vendor/sweetalert2/dist/sweetalert2.min.js')}}">
</script>
<script src="{{url('js/colorpicker.js')}}">
</script>
<script src="{{url('js/email-editor-plugin.js')}}">
</script>
<!--for bootstrap-tour  -->
<script src="{{url('vendor/bootstrap-tour/build/js/bootstrap-tour.min.js')}}">
</script>
<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js">
</script>
@include('v1.auth_pages.campaigns.scripts.email_builder-js')
<!-- Modal Dialog -->
<div aria-hidden="true" aria-labelledby="popup_edit_template" class="modal fade" id="popup_edit_template" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button aria-hidden="true" class="close" data-dismiss="modal" type="button">
                    {{ trans('ticketit::lang.flash-x') }}
                </button>
                <h4 class="modal-title">
                    {{ trans('messages.email_builder_popup.edit_success.title') }}
                </h4>
            </div>
            <div class="modal-body">
                <p>
                    {{ trans('messages.email_builder_popup.edit_success.body') }}
                </p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-default" data-dismiss="modal" type="button">
                    {{ trans('messages.email_builder_popup.edit_success.btn_ok') }}
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
