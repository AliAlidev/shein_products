<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

@include('admin.layouts.components.head')

<body class="sidebar-mini">
    <div id="app">
        <div class="main-wrapper">
            @include('admin.layouts.components.navigation')
            @include('admin.layouts.components.sidebar')

            <!-- Main Content -->
            <div class="main-content">
                @yield('main-content')
            </div>
            @include('admin.layouts.components.footer')
        </div>
    </div>

    <div id="custom-width-modal" class="modal fade purchase-model" tabindex="-1" role="dialog"
        aria-labelledby="custom-width-modalLabel" aria-hidden="true">
        <div class="modal-dialog model-dialog-purchase">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                </div>
                <div class="modal-body">
                    <h4 id="notificationTitle"></h4>
                    <p id="notificationBody"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default waves-effect remove-data-from-delete-form"
                        data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    @include('admin.layouts.components.script')

    {{-- <script> --}}
    {{--    $(document).ready(function(){ --}}
    {{--        // $('#sidebar-toggle-button').trigger('click'); --}}
    {{--        $('.main-sidebar').css('width', 'fit-content'); --}}
    {{--    }); --}}
    {{-- </script> --}}
    @yield('footer-js')
</body>

</html>
