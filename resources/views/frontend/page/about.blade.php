@extends('frontend.layouts.app')

@section('main-content')
    <section class="section-content padding-y">
        <div class="container">
            <header class="section-heading">
                <h2 class="section-title">
                    {{ $page->title }}
                </h2>
            </header><!-- sect-heading -->
            <article>
                {!! $page->description !!}
            </article>
        </div> <!-- container .//  -->
    </section>
@endsection
