<!DOCTYPE html>
<html>
@include('frontend.partials._head')

<body class="@yield('body-css')">
    <!-- Wrapper -->
    <div id="main-wrapper">
        @include('frontend.partials._nav')

        @yield('main-content')
    </div>
    <!-- Wrapper / End -->
    @include('frontend.partials._scripts')
</body>
</html>
