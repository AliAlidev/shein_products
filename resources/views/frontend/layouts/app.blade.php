<!DOCTYPE html>
<html>
@include('frontend.partials._head')
<body class="@yield('body-css')">
    <!-- Wrapper -->
    <div id="main-wrapper">

        @yield('main-content')

        @if (!request()->is('search'))
            @include('frontend.partials._footer')
        @endif
    </div>
    <!-- Wrapper / End -->
    @include('frontend.partials._scripts')
</body>
</html>
