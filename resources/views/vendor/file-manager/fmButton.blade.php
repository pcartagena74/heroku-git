<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
            <meta content="width=device-width, initial-scale=1, shrink-to-fit=no" name="viewport">
                <!-- CSRF Token -->
                <meta content="{{ csrf_token() }}" name="csrf-token">
                    <title>
                        {{ config('app.name', 'File Manager') }}
                    </title>
                    <!-- Styles -->
                    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" rel="stylesheet">
                        <link href="https://use.fontawesome.com/releases/v5.12.1/css/all.css" rel="stylesheet">
                            <link href="{{ asset('vendor/file-manager/css/file-manager.css') }}" rel="stylesheet">
                            </link>
                        </link>
                    </link>
                </meta>
            </meta>
        </meta>
    </head>
    <body>
        <style type="text/css">
            .fm-content.d-flex.flex-column.col {
                width: 100%;
            }
            .fm-content.d-flex.flex-column.col .fm-disk-list{
                display: none;
            }
            .fm-breadcrumb ol.breadcrumb.active-manager li:nth-child(n+3) {
                /*display:none;*/
            }
            .fm-breadcrumb ol.breadcrumb.active-manager li:nth-child(1) {
                display:none;
            }
        </style>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12" id="fm-main-block">
                    <div id="fm">
                    </div>
                </div>
            </div>
        </div>
        <!-- File manager -->
        <script src="{{ asset('vendor/file-manager/js/file-manager.js') }}">
        </script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
    // set fm height
    document.getElementById('fm-main-block').setAttribute('style', 'height:' + window.innerHeight + 'px');
    // Add callback to file manager
    fm.$store.commit('fm/setFileCallBack', function(fileUrl) {
      window.opener.fmSetLink(fileUrl);
      window.close();
    });
  });
        </script>
    </body>
</html>
