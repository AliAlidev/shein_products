<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">

    <title> </title>

    <meta name="csrf-token" content="{{ csrf_token() }}" />
    @stack('meta')
    <link href="" rel="shortcut icon" type="image/x-icon">
    <!-- Bootstrap4 files-->

    <link href="{{ asset('frontend/css/bootstrap.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('frontend/css/ui.css') }}" rel="stylesheet" type="text/css" />

    <link rel="stylesheet" href="{{asset('themes/css/style.css')}}">
    <link rel="stylesheet" href="{{asset('themes/css/main-color.css')}}" id="colors">
    <link href="{{ asset('frontend/fonts/fontawesome/css/all.min.css') }}" type="text/css" rel="stylesheet">
    <!-- poppins font-->

    <link href="{{asset('frontend/fonts/poppins/poppins.css')}}" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/ionicons/4.5.6/css/ionicons.min.css">

    @yield('style')
</head>
