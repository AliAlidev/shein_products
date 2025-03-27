@extends('admin.layouts.master')

@section('main-content')
    <section class="section">
        <div class="section-header row">

            <div class="col-lg-8">
                <h1>{{ __('dashboard.dashboard') }}</h1>
            </div>

        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div id="earningGraph"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">

        </div>
        <div class="col-md-4">
            <div class="card">

            </div>
        </div>
        </div>
    </section>
@endsection

@section('scripts')
@endsection
