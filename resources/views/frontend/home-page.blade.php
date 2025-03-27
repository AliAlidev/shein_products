@extends('frontend.layouts.app')
@push('meta')
<meta property="og:url" content="" />
<meta property="og:type" content="FoodBank" />
<meta property="og:title" content="">
<meta property="og:description" content="Explore top-rated attractions, activities and more">
<meta property="og:image" content="">
@endpush
@section('body-css')
{{__('transparent-header')}}
@endsection
@section('main-content')

<div class="main-search-container" data-background-image="{{ asset('frontend/images/default/bg.jpg') }}">
    <div class="color-overlay">
        <div class="main-search-inner">
            <div class="container-fluid custom-container">
                <div class="row no-margin-row">
                    <div class="col-sm-12 col-md-6 col-lg-6">
                        <form method="GET" action="">

                            <h2 class="header_title">


                            </h2>
                            <h2 class="title_responsive"> </h2>
                            <h4 class="subtitle">{{__('frontend.subtitle')}}</h4>

                            <div class="main-search-input">
                                <input type="hidden" id="lat" name="lat" required="" value="">
                                <input type="hidden" id="long" name="long" required="" value="">
                                <input type="hidden" id="expedition" name="expedition" value="{{ __('all') }}">

                                <div class="main-search-input-item location">
                                    <div id="autocomplete-container">
                                        <input id="autocomplete-input" type="text"
                                            placeholder="{{ __('frontend.search') }}">
                                    </div>
                                    <a class="main-search-icon" href="javascript:void(0)">
                                        <i class="im im-icon-Location" id="locationIcon" onclick="getLocation()"></i>
                                    </a>
                                </div>
                                <button class="button" type="submit">{{__('frontend.search')}}</button>
                            </div>
                        </form>
                    </div>
                </div>
                <!-- Features Categories -->
                <div class="row d-flex d-flex text-center">
                    <div class="col-sm-12 col-md-6 col-lg-6 highlighted-categories-w">

                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

@endsection

@section('extra-js')
<script type="text/javascript" src="{{ asset('frontend/js/map-current.js') }}"></script>

@endsection
