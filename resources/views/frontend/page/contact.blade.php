@extends('frontend.layouts.app')

@section('main-content')

    <section class="section-content padding-y">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <header class="section-heading">
                        <h2 class="section-title">
                            {{ ucfirst($page->title) }}
                        </h2>
                    </header><!-- sect-heading -->
                </div>
                <div class="col-md-6 pr-5">
                    {!! $page->description !!}
                </div>
                <div class="col-md-6 pl-5">
                    @if(Session::has('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    {!! Form::open(['route'=>'contact.store']) !!}
                    <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                        {!! Form::label(trans('frontend.name')) !!}<span><span class="text-danger">*</span>
                        {!! Form::text('name', old('name'), ['class'=>'form-control'.($errors->has('name') ? ' is-invalid' : ''), 'placeholder'=>trans('frontend.name')]) !!}
                        <span class="text-danger">{{ $errors->first('name') }}</span>
                    </div>
                    <div class="form-group {{ $errors->has('email') ? 'has-error' : '' }}">
                        {!! Form::label(trans('frontend.email')) !!}<span><span class="text-danger">*</span>
                        {!! Form::text('email', old('email'), ['class'=>'form-control'.($errors->has('email') ? ' is-invalid' : ''), 'placeholder'=>trans('frontend.email')]) !!}
                        <span class="text-danger">{{ $errors->first('email') }}</span>
                    </div>
                    <div class="form-group {{ $errors->has('message') ? 'has-error' : '' }}">
                        {!! Form::label(trans('frontend.message')) !!}<span><span class="text-danger">*</span>
                        {!! Form::textarea('message', old('message'), ['class'=>'form-control'.($errors->has('message') ? ' is-invalid' : ''), 'placeholder'=>trans('frontend.message')]) !!}
                        <span class="text-danger">{{ $errors->first('message') }}</span>
                    </div>
                    <div class="form-group">
                        <button class="btn btn-success">{{ __('frontend.contact_us') }}</button>
                    </div>
                    {!! Form::close() !!}

                </div>
            </div>
        </div> <!-- container .//  -->
    </section>
@endsection
