@extends('admin.layouts.master')

@push('css')
@endpush

@section('main-content')
    <section class="section">
        <div class="section-header">
            <h1>Shein Store Products</h1>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div style="display: flex; justify-content: space-between">
                                <div class="dropdown mb-3">
                                    <button class="btn btn-secondary dropdown-toggle" type="button"
                                        id="columnToggleDropdown" data-toggle="dropdown" aria-haspopup="true"
                                        aria-expanded="false">
                                        Toggle Columns
                                    </button>
                                    <div class="dropdown-menu" aria-labelledby="columnToggleDropdown">
                                        <div class="px-3">
                                            <div id="columnToggles"></div>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <a href="#" class="btn btn-icon icon-left btn-success" style="height: 35px">
                                        <i class="fas fa-file-excel"></i> Export Excel
                                    </a>
                                    <a href="#" class="btn btn-icon icon-left btn-primary" style="height: 35px"><i
                                            class="fas fa-plus"></i> Sync</a>
                                </div>
                            </div>

                            <div style="width: 100%!important; overflow-x: auto;">
                                <table class="table table-striped" id="maintable" data-url="{{ route('products') }}"
                                    style="width: auto!important; min-width: 100%;">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Image</th>
                                            <th>Arabic Name</th>
                                            <th>English Name</th>
                                            <th>Price</th>
                                            <th>Arabic Brand</th>
                                            <th>English Brand</th>
                                            <th>Arabic Description</th>
                                            <th>English Description</th>
                                            <th>Store</th>
                                            <th>Barcode</th>
                                            <th>Creation Date</th>
                                            <th>In App View</th>
                                            <th style="width: 10%">Actions</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('css')
    <link rel="stylesheet" href="{{ asset('assets/modules/datatables.net-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/modules/datatables.net-select-bs4/css/select.bootstrap4.min.css') }}">
@endsection

@section('scripts')
    <script src="{{ asset('assets/modules/datatables/media/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/modules/datatables.net-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/modules/datatables.net-select-bs4/js/select.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('js/product/index.js') }}"></script>
@endsection
