<!DOCTYPE html>
<html>
@include('frontend.partials._head')
<body class="@yield('body-css')">
    <!-- Wrapper -->
    <div id="main-wrapper">

        @yield('main-content')
    </div>
</body>
</html>
