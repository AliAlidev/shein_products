@extends('frontend.layouts.app')
@section('main-content')
<div id="sign-in-dialog" class="customize-sign-in">
    <div class="sign-in-form style-1">
        <ul class="tabs-nav">
            <li class="active"><a href="#tab1">{{__('Register')}}</a></li>
        </ul>
        <div class="tabs-container alt">
            <div class="tab-content" id="tab1">

                <form method="POST" class="register" action="{{ route('register') }}">
                    @csrf
                    <div class="row">
                        <div class="col-md-12">
                            <div class="col-md-4">
                                <div class="payment-tab registration-tab payment-tab-active">
                                    <div class="payment-tab-trigger">
                                        <input id="CustomerRegister" type="radio" name="roles" value="2"
                                            {{ old('roles', 2)== 2 ? 'checked' : 'checked'}}>
                                        <label for="CustomerRegister">{{__('Customer')}}</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="payment-tab registration-tab">
                                    <div class="payment-tab-trigger">
                                        <input id="RestaurantOwnerRegister" type="radio" name="roles" value="3"
                                            {{ old('roles')== 3 ? 'checked' : ''}}>
                                        <label for="RestaurantOwnerRegister">{{__('Restaurant Owner')}}</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="payment-tab registration-tab">
                                    <div class="payment-tab-trigger">
                                        <input id="DeliveryRegister" type="radio" name="roles" value="4"
                                            {{ old('roles')== 4 ? 'checked' : ''}}>
                                        <label for="DeliveryRegister">{{__('Delivery')}}</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="first_name">{{ __('First name') }} <span class="text-danger h2">*</span>
                                        <i class="im im-icon-User"></i>
                                        <input name="first_name" value="{{ old('first_name') }}" type="text"
                                            class="form-control @if($errors->has('first_name')) is-invalid @endif"
                                            placeholder="John">
                                    </label>
                                    @if($errors->has('first_name'))
                                    <div class="invalid-feadback text-danger" role="alert">
                                        {{ $errors->first('first_name') }}
                                    </div>
                                    @endif
                                </div> <!-- form-group end.// -->
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="last_name">{{__('Last Name')}} <span class="text-danger h2">*</span>
                                        <i class="im im-icon-User"></i>
                                        <input name="last_name" value="{{ old('last_name') }}" type="text"
                                            class="form-control @if($errors->has('last_name')) is-invalid @endif"
                                            placeholder="Doe">
                                    </label>
                                    @if($errors->has('last_name'))
                                    <div class="invalid-feadback text-danger" role="alert">
                                        {{ $errors->first('last_name') }}
                                    </div>
                                    @endif
                                </div> <!-- form-group end.// -->
                            </div>
                            <div class="col-md-6">
                                <div class=" form-group">
                                    <label for="register_email">{{__('Email Address')}} <span class="text-danger h2">*</span>
                                        <i class="im im-icon-Mail"></i>
                                        <input name="register_email" value="{{ old('register_email') }}" type="email"
                                            class="form-control @if($errors->has('register_email')) is-invalid @endif"
                                            placeholder="johndoe@example.com">
                                    </label>
                                    @if($errors->has('register_email'))
                                    <span class="is-invalid" role="alert">
                                        <strong class="text-danger">{{ $errors->first('register_email') }}</strong>
                                    </span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email2">{{__('Username')}}
                                        <i class="im im-icon-Male"></i>
                                        <input name="username" value="{{ old('username') }}" type="text"
                                            class="form-control @if($errors->has('username')) is-invalid @endif"
                                            placeholder="john">
                                    </label>
                                    @if($errors->has('username'))
                                    <div class="invalid-feadback text-danger" role="alert">
                                        {{ $errors->first('username') }}
                                    </div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="email2">{{__('Phone')}} <span class="text-danger h2">*</span>
                                        <i class="im im-icon-Old-Telephone"></i>
                                        <input name="phone" value="{{ old('phone') }}" type="text"
                                            class="form-control @if($errors->has('phone')) is-invalid @endif"
                                            placeholder="+18 91 298 882" onkeypress='validate(event)'>
                                    </label>
                                    @if($errors->has('phone'))
                                    <span class="is-invalid" role="alert">
                                        <strong class="text-danger">{{ $errors->first('phone') }}</strong>
                                    </span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="email2">{{__('Address')}}
                                        <i class="im im-icon-Address-Book"></i>
                                        <input name="address" value="{{ old('address') }}" type="text"
                                            class="form-control @if($errors->has('address')) is-invalid @endif"
                                            placeholder="House#10, Section#1, Dhaka 1216, Bangladesh">
                                    </label>
                                    @if($errors->has('address'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong class="text-danger">{{ $errors->first('address') }}</strong>
                                    </span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="password">{{__('Password')}} <span class="text-danger h2">*</span>
                                        <i class="im im-icon-Lock-2"></i>
                                        <input name="password"
                                            class="form-control @if($errors->has('password')) is-invalid @endif"
                                            type="password" placeholder="Create password">
                                    </label>
                                    @if($errors->has('password'))
                                    <span class="is-invalid" role="alert">
                                        <strong class="text-danger">{{ $errors->first('password') }}</strong>
                                    </span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="password2">{{__('Repeat Password')}} <span class="text-danger h2">*</span>
                                        <i class="im im-icon-Lock-2"></i>
                                        <input name="password_confirmation"
                                            class="form-control @if($errors->has('password_confirmation')) is-invalid @endif"
                                            type="password" placeholder="repeat password">
                                    </label>
                                    @if($errors->has('password_confirmation'))
                                    <span class="is-invalid" role="alert">
                                        <strong
                                            class="text-danger">{{ $errors->first('password_confirmation') }}</strong>
                                    </span>
                                    @endif
                                </div>
                            </div>

                            <div class="col-md-12">
                                <input type="submit" class="button border fw margin-top-10" name="register"
                                    value="Register" />
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
