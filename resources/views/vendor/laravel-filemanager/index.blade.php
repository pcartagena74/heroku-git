<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8"/>
        <meta content="IE=EDGE" http-equiv="X-UA-Compatible"/>
        <meta content="width=device-width,initial-scale=1" name="viewport"/>
        <!-- Chrome, Firefox OS and Opera -->
        <meta content="#333844" name="theme-color"/>
        <!-- Windows Phone -->
        <meta content="#333844" name="msapplication-navbutton-color"/>
        <!-- iOS Safari -->
        <meta content="#333844" name="apple-mobile-web-app-status-bar-style"/>
        <title>
            {{ trans('laravel-filemanager::lfm.title-page') }}
        </title>
        <link href="{{ asset('vendor/laravel-filemanager/img/72px color.png') }}" rel="shortcut icon" type="image/png"/>
        <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.0/css/bootstrap.min.css" rel="stylesheet"/>
        <link href="https://use.fontawesome.com/releases/v5.5.0/css/all.css" rel="stylesheet"/>
        <link href="//cdnjs.cloudflare.com/ajax/libs/jqueryui/1.11.2/jquery-ui.min.css" rel="stylesheet"/>
        <link href="{{ asset('vendor/laravel-filemanager/css/cropper.min.css') }}" rel="stylesheet"/>
        <link href="{{ asset('vendor/laravel-filemanager/css/dropzone.min.css') }}" rel="stylesheet"/>
        <link href="{{ asset('vendor/laravel-filemanager/css/mime-icons.min.css') }}" rel="stylesheet"/>
        <style>
            {!! \File::get(base_path('vendor/unisharp/laravel-filemanager/public/css/lfm.css')) !!}
        </style>
        {{-- Use the line below instead of the above if you need to cache the css. --}}
  {{--
        <link href="{{ asset('/vendor/laravel-filemanager/css/lfm.css') }}" rel="stylesheet">
        </link>
        --}}
    </head>
</html>
<body>
    <nav class="navbar sticky-top navbar-expand-lg navbar-dark" id="nav">
        <a class="navbar-brand invisible-lg d-none d-lg-inline" id="to-previous">
            <i class="fas fa-arrow-left fa-fw">
            </i>
            <span class="d-none d-lg-inline">
                {{ trans('laravel-filemanager::lfm.nav-back') }}
            </span>
        </a>
        <a class="navbar-brand d-block d-lg-none" id="show_tree">
            <i class="fas fa-bars fa-fw">
            </i>
        </a>
        <a class="navbar-brand d-block d-lg-none" id="current_folder">
        </a>
        <a class="navbar-brand" id="loading">
            <i class="fas fa-spinner fa-spin">
            </i>
        </a>
        <div class="ml-auto px-2">
            <a class="navbar-link d-none" id="multi_selection_toggle">
                <i class="fa fa-check-double fa-fw">
                </i>
                <span class="d-none d-lg-inline">
                    {{ trans('laravel-filemanager::lfm.menu-multiple') }}
                </span>
            </a>
        </div>
        <a class="navbar-toggler collapsed border-0 px-1 py-2 m-0" data-target="#nav-buttons" data-toggle="collapse">
            <i class="fas fa-cog fa-fw">
            </i>
        </a>
        <div class="collapse navbar-collapse flex-grow-0" id="nav-buttons">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-display="grid">
                        <i class="fas fa-th-large fa-fw">
                        </i>
                        <span>
                            {{ trans('laravel-filemanager::lfm.nav-thumbnails') }}
                        </span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-display="list">
                        <i class="fas fa-list-ul fa-fw">
                        </i>
                        <span>
                            {{ trans('laravel-filemanager::lfm.nav-list') }}
                        </span>
                    </a>
                </li>
                <li class="nav-item dropdown">
                    <a aria-expanded="false" aria-haspopup="true" class="nav-link dropdown-toggle" data-toggle="dropdown" role="button">
                        <i class="fas fa-sort fa-fw">
                        </i>
                        {{ trans('laravel-filemanager::lfm.nav-sort') }}
                    </a>
                    <div class="dropdown-menu dropdown-menu-right border-0">
                    </div>
                </li>
            </ul>
        </div>
    </nav>
    <nav class="bg-light fixed-bottom border-top d-none" id="actions">
        <a data-action="open" data-multiple="false">
            <i class="fas fa-folder-open">
            </i>
            {{ trans('laravel-filemanager::lfm.btn-open') }}
        </a>
        <a data-action="preview" data-multiple="true">
            <i class="fas fa-images">
            </i>
            {{ trans('laravel-filemanager::lfm.menu-view') }}
        </a>
        <a data-action="use" data-multiple="true">
            <i class="fas fa-check">
            </i>
            {{ trans('laravel-filemanager::lfm.btn-confirm') }}
        </a>
    </nav>
    <div class="d-flex flex-row">
        <div id="tree">
        </div>
        <div id="main">
            <div id="alerts">
            </div>
            <nav aria-label="breadcrumb" class="d-none d-lg-block" id="breadcrumbs">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item invisible">
                        Home
                    </li>
                </ol>
            </nav>
            <div class="d-none" id="empty">
                <i class="far fa-folder-open">
                </i>
                {{ trans('laravel-filemanager::lfm.message-empty') }}
            </div>
            <div id="content">
            </div>
            <a class="d-none" id="item-template">
                <div class="square">
                </div>
                <div class="info">
                    <div class="item_name text-truncate">
                    </div>
                    <time class="text-muted font-weight-light text-truncate">
                    </time>
                </div>
            </a>
        </div>
        <div id="fab">
        </div>
    </div>
    <div aria-hidden="true" aria-labelledby="myModalLabel" class="modal fade" id="uploadModal" role="dialog" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="myModalLabel">
                        {{ trans('laravel-filemanager::lfm.title-upload') }}
                    </h4>
                    <button aria-label="Close" class="close" data-dismiss="modal" type="button">
                        <span aia-hidden="true">
                            ×
                        </span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('unisharp.lfm.upload') }}" class="dropzone" enctype="multipart/form-data" id="uploadForm" method="post" name="uploadForm" role="form">
                        <div class="form-group" id="attachment">
                            <div class="controls text-center">
                                <div class="input-group w-100">
                                    <a class="btn btn-primary w-100 text-white" id="upload-button">
                                        {{ trans('laravel-filemanager::lfm.message-choose') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                        <input id="working_dir" name="working_dir" type="hidden">
                            <input id="type" name="type" type="hidden" value='{{ request("type") }}'>
                                <input name="_token" type="hidden" value="{{csrf_token()}}">
                                </input>
                            </input>
                        </input>
                    </form>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary w-100" data-dismiss="modal" type="button">
                        {{ trans('laravel-filemanager::lfm.btn-close') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div aria-hidden="true" class="modal fade" id="notify" role="dialog" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-body">
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary w-100" data-dismiss="modal" type="button">
                        {{ trans('laravel-filemanager::lfm.btn-close') }}
                    </button>
                    <button class="btn btn-primary w-100" data-dismiss="modal" type="button">
                        {{ trans('laravel-filemanager::lfm.btn-confirm') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div aria-hidden="true" class="modal fade" id="dialog" role="dialog" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">
                    </h4>
                </div>
                <div class="modal-body">
                    <input class="form-control" type="text">
                    </input>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary w-100" data-dismiss="modal" type="button">
                        {{ trans('laravel-filemanager::lfm.btn-close') }}
                    </button>
                    <button class="btn btn-primary w-100" data-dismiss="modal" type="button">
                        {{ trans('laravel-filemanager::lfm.btn-confirm') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="d-none carousel slide bg-light" data-ride="carousel" id="carouselTemplate">
        <ol class="carousel-indicators">
            <li class="active" data-slide-to="0" data-target="#previewCarousel">
            </li>
        </ol>
        <div class="carousel-inner">
            <div class="carousel-item active">
                <a class="carousel-label">
                </a>
                <div class="carousel-image">
                </div>
            </div>
        </div>
        <a class="carousel-control-prev" data-slide="prev" href="#previewCarousel" role="button">
            <div aria-hidden="true" class="carousel-control-background">
                <i class="fas fa-chevron-left">
                </i>
            </div>
            <span class="sr-only">
                Previous
            </span>
        </a>
        <a class="carousel-control-next" data-slide="next" href="#previewCarousel" role="button">
            <div aria-hidden="true" class="carousel-control-background">
                <i class="fas fa-chevron-right">
                </i>
            </div>
            <span class="sr-only">
                Next
            </span>
        </a>
    </div>
    <script src="https://code.jquery.com/jquery-3.2.1.min.js">
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.3/umd/popper.min.js">
    </script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.0/js/bootstrap.min.js">
    </script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js">
    </script>
    <script src="{{ asset('vendor/laravel-filemanager/js/cropper.min.js') }}">
    </script>
    <script src="{{ asset('vendor/laravel-filemanager/js/dropzone.min.js') }}">
    </script>
    <script>
        var lang = {!! json_encode(trans('laravel-filemanager::lfm')) !!};
    var actions = [
      // {
      //   name: 'use',
      //   icon: 'check',
      //   label: 'Confirm',
      //   multiple: true
      // },
      {
        name: 'rename',
        icon: 'edit',
        label: lang['menu-rename'],
        multiple: false
      },
      {
        name: 'download',
        icon: 'download',
        label: lang['menu-download'],
        multiple: true
      },
      // {
      //   name: 'preview',
      //   icon: 'image',
      //   label: lang['menu-view'],
      //   multiple: true
      // },
      {
        name: 'move',
        icon: 'paste',
        label: lang['menu-move'],
        multiple: true
      },
      {
        name: 'resize',
        icon: 'arrows-alt',
        label: lang['menu-resize'],
        multiple: false
      },
      {
        name: 'crop',
        icon: 'crop',
        label: lang['menu-crop'],
        multiple: false
      },
      {
        name: 'trash',
        icon: 'trash',
        label: lang['menu-delete'],
        multiple: true
      },
    ];

    var sortings = [
      {
        by: 'alphabetic',
        icon: 'sort-alpha-down',
        label: lang['nav-sort-alphabetic']
      },
      {
        by: 'time',
        icon: 'sort-numeric-down',
        label: lang['nav-sort-time']
      }
    ];
    </script>
    <script>
        {!! \File::get(base_path('vendor/unisharp/laravel-filemanager/public/js/script.js')) !!}
    </script>
    {{-- Use the line below instead of the above if you need to cache the script. --}}
  {{--
    <script src="{{ asset('vendor/laravel-filemanager/js/script.js') }}">
    </script>
    --}}
    <script>
        Dropzone.options.uploadForm = {
      paramName: "upload[]", // The name that will be used to transfer the file
      uploadMultiple: false,
      parallelUploads: 5,
      timeout:0,
      clickable: '#upload-button',
      dictDefaultMessage: lang['message-drop'],
      init: function() {
        var _this = this; // For the closure
        this.on('success', function(file, response) {
          if (response == 'OK') {
            loadFolders();
          } else {
            this.defaultOptions.error(file, response.join('\n'));
          }
        });
      },
      headers: {
        'Authorization': 'Bearer ' + getUrlParam('token')
      },
      acceptedFiles: "{{ implode(',', $helper->availableMimeTypes()) }}",
      maxFilesize: ({{ $helper->maxUploadSize() }} / 1000)
    }
    </script>
</body>
