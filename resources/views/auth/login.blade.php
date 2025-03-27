@extends('frontend.layouts.app')
@section('main-content')
    <div id="sign-in-dialog" class="customize-sign-in">
        <div class="sign-in-form style-1" style="margin-top: 25%">
            <ul class="tabs-nav">
                <li class="@if (old('loginType') != 'register') active @endif"><a href="#tab1">{{ __('Log In') }}</a></li>
            </ul>

            <div class="tabs-container alt">

                <!-- Login -->
                <div class="tab-content @if (old('loginType') == 'register') d-none @endif" id="tab1">
                    <form method="POST" class="login" action="{{ route('login') }}">
                        @csrf
                        @php
                        @endphp
                        <input type="hidden" name="type" value="frontend">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="email">{{ __('Email:') }}
                                    <i class="im im-icon-Male"></i>
                                    <input id="demoemail" type="email"
                                        class="form-control"
                                        name="email" value="{{ old('email') }}" autocomplete="email" autofocus
                                        placeholder="Email">
                                </label>
                            </div>

                            <div class="form-group">
                                <label for="password">{{ __('Password:') }}
                                    <i class="im im-icon-Lock-2"></i>
                                    <input placeholder="Password" id="demopassword" type="password"
                                        class="form-control"
                                        name="password" autocomplete="current-password">
                                </label>
                            </div>

                            <div class="form-row">
                                <input type="submit" class="button border margin-top-5" name="login" value="Login" />
                                <div class="checkboxes margin-top-10">
                                    <input id="remember-me" type="checkbox" name="check">
                                    <label for="remember-me">{{ __('Remember Me') }}</label>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('extra-js')
    <script src="{{ asset('js/phone_validation/index.js') }}"></script>
@endsection
