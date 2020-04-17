<script>
    var _is_demo = false;
var base_url = '{!! url('/').'/' !!}';
function loadImages() {
    $.ajax({
        url: 'get-files.php',
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            if (data.code == 0) {
                _output = '';
                for (var k in data.files) {
                    if (typeof data.files[k] !== 'function') {
                        _output += "<div class='col-sm-3'>" + "<img class='upload-image-item' src='" + data.directory + data.files[k] + "' alt='" + data.files[k] + "' data-url='" + data.directory + data.files[k] + "'>" + "</div>";
                        // console.log("Key is " + k + ", value is" + data.files[k]);
                    }
                }
                $('.upload-images').html(_output);
            }
        },
        error: function() {}
    });
}
var _templateListItems;
var _emailBuilder = $('.editor').emailBuilder({
    //new features begin
    showMobileView: true,
    onTemplateDeleteButtonClick: function(e, dataId, parent) {
        $.ajax({
            url: 'delete_template.php',
            type: 'POST',
            data: {
                templateId: dataId
            },
            //  dataType: 'json',
            success: function(data) {
                parent.remove();
            },
            error: function() {}
        });
    },
    //new features end
    lang: 'en',
    elementsHTML: $('.elements-db').html(),
    langJsonUrl: 'lang-1.json',
    loading_color1: 'red',
    loading_color2: 'green',
    showLoading: true,
    blankPageHtmlUrl: 'template-blank-page.html',
    loadPageHtmlUrl: 'template-load-page.html',
    //left menu
    showElementsTab: true,
    showPropertyTab: true,
    showCollapseMenu: true,
    showBlankPageButton: true,
    showCollapseMenuinBottom: true,
    //setting items
    showSettingsBar: true,
    showSettingsPreview: true,
    showSettingsExport: false,
    showSettingsImport: false,
    showSettingsSendMail: false,
    showSettingsSave: true,
    showSettingsLoadTemplate: true,
    //show context menu
    showContextMenu: true,
    showContextMenu_FontFamily: true,
    showContextMenu_FontSize: true,
    showContextMenu_Bold: true,
    showContextMenu_Italic: true,
    showContextMenu_Underline: true,
    showContextMenu_Strikethrough: true,
    showContextMenu_Hyperlink: true,
    //show or hide elements actions
    showRowMoveButton: true,
    showRowRemoveButton: true,
    showRowDuplicateButton: true,
    showRowCodeEditorButton: true,
    onSettingsImportClick: function() {
        $('#popupimport').modal('show');
    },
    onBeforePopupBtnImportClick: function() {
        console.log('onBeforePopupBtnImportClick html');
        var file_data = $('.input-import-file').prop('files')[0];
        var form_data = new FormData();
        form_data.append('importfile', file_data);
        $.ajax({
            url: 'template_import.php',
            dataType: 'json',
            cache: false,
            contentType: false,
            processData: false,
            data: form_data,
            type: 'post',
            success: function(response) {
                _data = response;
                //  _data = JSON.parse(response);
                $('.content-wrapper .email-editor-elements-sortable').html('');
                $('#demp').html(_data.content);
                _content = '';
                $('#demp .main').each(function(index, item) {
                    _content += '<div class="sortable-row">' + '<div class="sortable-row-container">' + ' <div class="sortable-row-actions">';
                    _content += '<div class="row-move row-action">' + '<i class="fa fa-arrows-alt"></i>' + '</div>';
                    _content += '<div class="row-remove row-action">' + '<i class="fa fa-remove"></i>' + '</div>';
                    _content += '<div class="row-duplicate row-action">' + '<i class="fa fa-files-o"></i>' + '</div>';
                    _content += '<div class="row-code row-action">' + '<i class="fa fa-code"></i>' + '</div>';
                    _content += '</div>' + '<div class="sortable-row-content" >' + '</div></div></div>';
                    $('.content-wrapper .email-editor-elements-sortable').append(_content);
                    $('.content-wrapper .email-editor-elements-sortable .sortable-row').eq(index).find('.sortable-row-content').append(item);
                });
            }
        });
    },
    onElementDragStart: function(e) {},
    onElementDragFinished: function(e, contentHtml, dataId) {
        console.log('here1');
        //not required
        // $.ajax({
        //         url: 'update_block_info.php',
        //         type: 'POST',
        //         data: {
        //                 block_id: dataId
        //         },
        //         dataType: 'json',
        //         success: function(data) {
        //         },
        //         error: function() {}
        // });
    },
    onBeforeRowRemoveButtonClick: function(e) {
        console.log('onBeforeRemoveButtonClick html');
        /*
          if you want do not work code in plugin ,
          you must use e.preventDefault();
        */
        //e.preventDefault();
    },
    onAfterRowRemoveButtonClick: function(e) {
        console.log('onAfterRemoveButtonClick html');
    },
    onBeforeRowDuplicateButtonClick: function(e) {
        console.log('onBeforeRowDuplicateButtonClick html');
        //e.preventDefault();
    },
    onAfterRowDuplicateButtonClick: function(e) {
        console.log('onAfterRowDuplicateButtonClick html');
    },
    onBeforeRowEditorButtonClick: function(e) {
        console.log('onBeforeRowEditorButtonClick html');
        //e.preventDefault();
    },
    onAfterRowEditorButtonClick: function(e) {
        console.log('onAfterRowDuplicateButtonClick html');
    },
    onBeforeShowingEditorPopup: function(e) {
        console.log('onBeforeShowingEditorPopup html');
        //e.preventDefault();
    },
    onBeforeSettingsSaveButtonClick: function(e) {
        console.log('onBeforeSaveButtonClick html');
        @if(!empty($campaign->campaignID))
            e.preventDefault();
            var arr = [];
            $('.content-main .sortable-row-content').each(function(i, item) {
                _dataId = $(this).attr('data-id');
                _html = $(this).html();
                arr[i] = {
                    id: _dataId,
                    content: _html
                };
            });
            var name = $("#edit_campaign input[name=name]").val();
            var from_name = $("#edit_campaign input[name=from_name]").val();
            var from_email = $("#edit_campaign input[name=from_email]").val();
            var subject = $("#edit_campaign input[name=subject]").val();
            var preheader = $("#edit_campaign input[name=preheader]").val();
            $.ajax({
                url: '{{ url('updateEmailTemplate') }}',
                type: 'POST',
                dataType: 'json',
                data: {
                    name: name,
                    contentArr: arr,
                    id: {{$campaign->campaignID}},
                    from_name: from_name,
                    from_email: from_email,
                    subject: subject,
                    preheader: preheader
                },
                success: function(data) {
                    //  console.log(data);
                    if (data.success === true) {
                        $('#popup_edit_template').modal('show');
                        _isDirty = false;
                    } else {
                        $('.input-error').text(data.message);
                    }
                },
                error: function(error) {
                    $('.input-error').text('Internal error');
                }
            });
        @else
            //value not setting working on it
            var name = $('.template-name').val('{{$campaign_name}}');
            console.log('here','{{$campaign_name}}',$.trim(name).length);
            if($.trim(name).length == 0 ){
                console.log('indse');
                $('.template-name').html('dfdfd');
            }
        @endif

        //  if (_is_demo) {
        //      $('#popup_demo').modal('show');
        //      e.preventDefault();//return false
        //  }
    },
    onPopupUploadImageButtonClick: function() {
        console.log('onPopupUploadImageButtonClick html');
        var file_data = $('.input-file').prop('files')[0];
        var form_data = new FormData();
        form_data.append('file', file_data);
        $.ajax({
            url: 'upload.php', // point to server-side PHP script
            dataType: 'text', // what to expect back from the PHP script, if anything
            cache: false,
            contentType: false,
            processData: false,
            data: form_data,
            type: 'post',
            success: function(php_script_response) {
                loadImages();
            }
        });
    },
    onSettingsPreviewButtonClick: function(e, getHtml) {
        console.log('onPreviewButtonClick html');
        $.ajax({
            url: base_url + 'storeEmailTemplateForPreview',
            type: 'POST',
            data: {
                html: getHtml
            },
            dataType: 'json',
            success: function(data) {
                // if (data.success == true) {
                //     $('#popup_demo').modal('show');
                //     return;
                // } 
                if (data.success == true) {
                    $('#previewModalFrame').attr('src', data.preview_url);
                    $('.preview_url').html('<a href="' + data.preview_url + '" target="_blank">' + data.preview_url + '</a>');
                    $('#previewModal').modal('show');
                    // var win = window.open(data.preview_url, '_blank');
                    // if (win) {
                    //     //Browser has allowed it to be opened
                    //     win.focus();
                    // } else {
                    //     //Browser has blocked it
                    //     alert('Please allow popups for this website');
                    // }
                }
            },
            error: function() {}
        });
        //e.preventDefault();
    },
    onSettingsExportButtonClick: function(e, getHtml) {
        console.log('onSettingsExportButtonClick html');
        $.ajax({
            url: 'export.php',
            type: 'POST',
            data: {
                html: getHtml
            },
            dataType: 'json',
            success: function(data) {
                if (data.code == -5) {
                    $('#popup_demo').modal('show');
                } else if (data.code == 0) {
                    window.location.href = data.url;
                }
            },
            error: function() {}
        });
        //e.preventDefault();
    },
    onBeforeSettingsLoadTemplateButtonClick: function(e) {
        console.log('onBeforeSettingsLoadTemplateButtonClick');
        $('.template-list').html('<div style="text-align:center">Loading...</div>');
        load_template_data();
    },
    onSettingsSendMailButtonClick: function(e) {
        console.log('onSettingsSendMailButtonClick html');
        //e.preventDefault();
    },
    onPopupSendMailButtonClick: function(e, _html) {
        console.log('onPopupSendMailButtonClick html');
        _email = $('.recipient-email').val();
        _element = $('.btn-send-email-template');
        output = $('.popup_send_email_output');
        var file_data = $('#send_attachments').prop('files');
        var form_data = new FormData();
        //form_data.append('attachments', file_data);
        $.each(file_data, function(i, file) {
            form_data.append('attachments[' + i + ']', file);
        });
        form_data.append('html', _html);
        form_data.append('mail', _email);
        $.ajax({
            url: 'send.php', // point to server-side PHP script
            dataType: 'json', // what to expect back from the PHP script, if anything
            cache: false,
            contentType: false,
            processData: false,
            data: form_data,
            type: 'post',
            success: function(data) {
                if (data.code == 0) {
                    output.css('color', 'green');
                } else {
                    output.css('color', 'red');
                }
                _element.removeClass('has-loading');
                _element.text('Send Email');
                output.text(data.message);
            }
        });
    },
    onBeforeChangeImageClick: function(e) {
        console.log('onBeforeChangeImageClick html');
        loadImages();
    },
    onBeforePopupSelectTemplateButtonClick: function(dataId) {
        console.log('onBeforePopupSelectTemplateButtonClick');
        $.ajax({
            url: base_url+'getEmailTemplateBlocks',
            type: 'POST',
            dataType: 'json',
            data: {
                id: dataId
            },
            success: function(data) {
                // data = JSON.parse(data);
                $('.content-wrapper .email-editor-elements-sortable').html('');
                for (var i = 0; i < data.blocks.length; i++) {
                    _content = '';
                    _content += '<div class="sortable-row">' + '<div class="sortable-row-container">' + ' <div class="sortable-row-actions">';
                    _content += '<div class="row-move row-action">' + '<i class="fa fa-arrows-alt"></i>' + '</div>';
                    _content += '<div class="row-remove row-action">' + '<i class="fa fa-remove"></i>' + '</div>';
                    _content += '<div class="row-duplicate row-action">' + '<i class="fa fa-files-o"></i>' + '</div>';
                    _content += '<div class="row-code row-action">' + '<i class="fa fa-code"></i>' + '</div>';
                    _content += '</div>' + '<div class="sortable-row-content" data-id=' + data.blocks[i].block_id + ' data-types=' + data.blocks[i].property + '  data-last-type=' + data.blocks[i].property.split(',')[0] + '  >' + data.blocks[i].content + '</div></div></div>';
                    $('.content-wrapper .email-editor-elements-sortable').append(_content);
                }
            },
            error: function(error) {
                $('.input-error').text('Internal error');
            }
        });
        _emailBuilder.makeSortable();
        return;
    },
    onBeforePopupSelectImageButtonClick: function(e) {
        console.log('onBeforePopupSelectImageButtonClick html');
    },
    onPopupSaveButtonClick: function() {
        var arr = [];
        $('.content-main .sortable-row-content').each(function(i, item) {
            _dataId = $(this).attr('data-id');
            _html = $(this).html();
            arr[i] = {
                id: _dataId,
                content: _html
            };
        });
        var from_name = $("#save_campaign input[name=from_name]").val();
        var from_email = $("#save_campaign input[name=from_email]").val();
        var subject = $("#save_campaign input[name=subject]").val();
        var preheader = $("#save_campaign input[name=preheader]").val();
        $.ajax({
            url: '{{ url('storeEmailTemplate') }}',
            type: 'POST',
            dataType: 'json',
            data: {
                name: $('.template-name').val(),
                contentArr: arr,
                from_name: from_name,
                from_email: from_email,
                subject: subject,
                preheader: preheader
            },
            success: function(data) {
                if (data.success === true) {
                    $('#popup_save_template').modal('hide');
                    window.location.href = data.redirect_url;
                } else {
                    $.each(data.errors,function(key,value) {
                        $('.input-error').append(value+'</br>');
                    });
                }
            },
            error: function(error) {
                $('.input-error').text('Internal error');
            }
        });
    },
    onUpdateButtonClick: function() {
        var arr = [];
        $('.content-main .sortable-row-content').each(function(i, item) {
            _dataId = $(this).attr('data-id');
            _html = $(this).html();
            arr[i] = {
                id: _dataId,
                content: _html
            };
        });
        $.ajax({
            url: 'upload_template.php',
            type: 'POST',
            //dataType: 'json',
            data: {
                name: $('.project-name').text(),
                contentArr: arr,
                id: $('.project-name').attr('data-id')
            },
            success: function(data) {
                //  console.log(data);
                // if (data === 'ok') {
                //      $('#popup_save_template').modal('hide');
                // } else {
                //      $('.input-error').text('Problem in server');
                // }
            },
            error: function(error) {
                $('.input-error').text('Internal error');
            }
        });
    }
});
var _loaded = false;
_emailBuilder.setAfterLoad(function(e) {
    _emailBuilder.makeSortable();
    $('.elements-db').remove();
    _loaded = true;
});

$(document).on('click', '.template-list-pagination .pagination a', function(event){
  event.preventDefault(); 
  var page = $(this).attr('href').split('page=')[1];
  load_template_data(page);
 });

function load_template_data(page){
        var url = base_url + 'getEmailTemplates';
        if(!isNaN(page)) {
            url = url + '?page='+page;
        }
        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                if (data.success == true) {
                    _templateItems = '';
                    var list = data.list.data;
                    _templateListItems = data.list;
                    for (var i = 0; i < list.length; i++) {
                        _templateItems += '<div class="template-item" data-id="' + list[i].campaignID + '">' + '<div class="template-item-delete" data-id="' + list[i].campaignID + '">' + '<i class="fa fa-trash-o"></i>' + '</div>' + '<div class="template-item-icon">' + '<i class="fa fa-file-text-o fa-3x"></i>' + '</div>' + '<div class="template-item-name">' + list[i].title + '</div>' + '</div>';
                    }
                    $('.template-list').html(_templateItems);
                    $('.template-list-pagination').html(data.pages);
                } else {
                    $('.template-list').html('<div style="text-align:center">No items</div>');
                }
            },
            error: function() {}
        });
}
var _isDirty = false;
$("body").on('DOMSubtreeModified', ".content-wrapper .email-editor-elements-sortable", function() {
    if(_loaded){
        _isDirty = true;
    }
});

@if(isset($campaign->template_blocks))
var data = {!! json_encode($campaign->template_blocks,JSON_HEX_APOS) !!};
    setTimeout(function() {
        $('.content-wrapper .email-editor-elements-sortable').html('');
        _emailBuilder.makeSortable();
    },2000);
    if(data.length > 0) {
        _content = '';
        for (var i = 0; i < data.length; i++) {
            console.log(data[i].property);
            _content += '<div class="sortable-row">' + '<div class="sortable-row-container">' + ' <div class="sortable-row-actions">';
            _content += '<div class="row-move row-action">' + '<i class="fa fa-arrows-alt"></i>' + '</div>';
            _content += '<div class="row-remove row-action">' + '<i class="fa fa-remove"></i>' + '</div>';
            _content += '<div class="row-duplicate row-action">' + '<i class="fa fa-files-o"></i>' + '</div>';
            _content += '<div class="row-code row-action">' + '<i class="fa fa-code"></i>' + '</div>';
            var property = '';
            if(data[i].property){
                property = data[i].property.split(',')[0];
            }
            _content += '</div>' + '<div class="sortable-row-content" data-id=' + data[i].block_id + ' data-types=' + data[i].property + '  data-last-type=' + property + '  >' + data[i].content + '</div></div></div>';
            // _emailBuilder.makeSortable();
        }
        setTimeout(function() {
            $('.content-wrapper .email-editor-elements-sortable').append(_content);
                    _emailBuilder.makeSortable();
        }, 2000);
    }
@endif

    var _popup_exit_btn_yes = false;
    var _popup_exit_btn_no = false;
    var _popup_exit_btn_cancel = false;
    $('#hide-etb').on('click', function() {
      if(_isDirty){
        $('#popup_save_before_exit').modal('show');
      }
    });
    function closeEmailBuilderPopup(){
      $('.etb-wrapper').removeClass('is-active')
      $('.etb-wrapper').parent().parent().removeClass('in')
      $('#show-etb').parent().removeClass('collapsed');
      $('#show-etb').parent().attr('aria-expanded', true);
      $('body').removeClass('no-scroll');
    }
    function setExitPopButtonValue(btn_press){
        switch(btn_press) {
          case 'yes':
            $('#popup_save_before_exit').modal('hide');
            $('.setting-item.save-template').trigger('click');
            _popup_exit_btn_yes = false;
            break;
          case 'no':
            $('#popup_save_before_exit').modal('hide');
            closeEmailBuilderPopup();     
            _popup_exit_btn_no = false;
            break;
        }
    }
</script>
