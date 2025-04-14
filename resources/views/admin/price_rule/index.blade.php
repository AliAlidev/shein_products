@extends('admin.layouts.master')

@section('main-content')
    <section class="section">
        <div class="section-header">
            <h1>Price Rules</h1>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="section-body">
                            <div style="display: flex; justify-content: space-between">
                                <div class="dropdown mb-3">
                                    <div class="dropdown-menu" aria-labelledby="columnToggleDropdown">
                                        <div class="px-3">
                                            <div id="columnToggles"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <a href="#" data-toggle="modal" data-target="#priceRuleCreateModal"
                                        class="btn btn-icon icon-left btn-info export-all-btn" style="height: 35px">
                                        <i class="fa fa-plus"></i> Create
                                    </a>
                                </div>
                            </div>
                            <div style="width: 100%!important; overflow-x: auto;">
                                <table class="table table-striped" id="maintable" data-url="{{ route('price_rules') }}"
                                    style="width: auto!important; min-width: 100%;">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Name</th>
                                            <th>Apply Per</th>
                                            {{-- <th>Apply To</th> --}}
                                            <th>Type</th>
                                            <th>Value</th>
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

    <!-- Update Modal -->
    <div class="modal fade" id="priceRuleUpdateModal" tabindex="-1" role="dialog"
        aria-labelledby="productDetailsModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="productDetailsModalLabel">Update Price Rule</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('price_rules.create') }}" method="POST" id="update-form">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Name:</label>
                                    <input type="text" name="name" id="name" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label>Apply Per:</label>
                                    <select name="apply_per" id="apply_per_update" class="form-control" data-categories-url="{{ route('price_rules.get_categories') }}" data-products-url="{{ route('price_rules.get_products') }}">
                                        <option value="Default">Default</option>
                                        <option value="Category">Category</option>
                                        <option value="Product">Product</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row" style="display: none" id="apply_to_div_update" data-categories-url="{{ route('price_rules.get_categories') }}" data-products-url="{{ route('price_rules.get_products') }}">
                            <div class="col-md-6" id="sections_update_div" style="display: none">
                                <label for="sections_update" class="form-label">Sections Filter</label>
                                <select class="select2 form-select w-50" multiple id="sections_update" data-section-types-url="{{ route('products.get_section_types') }}" data-categories-url="{{ route('products.categories') }}">
                                    @foreach ($sections as $section)
                                        <option value="{{ $section }}">{{ $section }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6" id="section_types_update_div" style="display: none">
                                <label for="section_types_update" class="form-label">Section Types Filter</label>
                                <select class="select2 form-select w-50" multiple id="section_types_update" data-sections-url="{{ route('products.categories') }}">
                                    @foreach ($sectionTypes as $section)
                                        <option value="{{ $section }}">{{ $section }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-12 mt-2">
                                <div class="form-group">
                                    <label>Apply To:</label>
                                    <select name="apply_to[]" id="apply_to_update" multiple class="select2 form-control">
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Type:</label>
                                    <select name="type" id="type" class="form-control">
                                        <option value="fixed">fixed</option>
                                        <option value="percentage">Percentage</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Value:</label>
                                    <input type="text" name="value" id="value" class="form-control" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary modal-edit-btn">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Create Modal -->
    <div class="modal fade" id="priceRuleCreateModal" tabindex="-1" role="dialog"
        aria-labelledby="productDetailsModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="productDetailsModalLabel">Create Price Rule</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('price_rules.create') }}" method="POST" id="create-form">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Name:</label>
                                    <input type="text" name="name" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label>Apply Per:</label>
                                    <select name="apply_per" id="apply_per" class="form-control" data-categories-url="{{ route('price_rules.get_categories') }}" data-products-url="{{ route('price_rules.get_products') }}">
                                        <option value="Default">Default</option>
                                        <option value="Category">Category</option>
                                        <option value="Product">Product</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row" style="display: none" id="apply_to_div">
                            <div class="col-md-6" id="sections_div" style="display: none">
                                <label for="sections" class="form-label">Sections Filter</label>
                                <select class="select2 form-select w-50" multiple id="sections" data-section-types-url="{{ route('products.get_section_types') }}" data-categories-url="{{ route('products.categories') }}">
                                    @foreach ($sections as $section)
                                        <option value="{{ $section }}">{{ $section }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6" id="section_types_div" style="display: none">
                                <label for="section_types" class="form-label">Section Types Filter</label>
                                <select class="select2 form-select w-50" multiple id="section_types" data-sections-url="{{ route('products.categories') }}">
                                    @foreach ($sectionTypes as $section)
                                        <option value="{{ $section }}">{{ $section }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-12 mt-2">
                                <div class="form-group">
                                    <label>Apply To:</label>
                                    <select name="apply_to[]" id="apply_to" multiple class="select2 form-control">
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Type:</label>
                                    <select name="type" id="type" class="form-control">
                                        <option value="fixed">fixed</option>
                                        <option value="percentage">Percentage</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Value:</label>
                                    <input type="text" name="value" class="form-control" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary modal-create-btn">Create</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('css')
    <link rel="stylesheet" href="{{ asset('assets/modules/datatables.net-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/modules/datatables.net-select-bs4/css/select.bootstrap4.min.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <style>
        .select2-container {
            width: 100% !important;
        }
    </style>
@endsection

@section('scripts')
    <script src="{{ asset('assets/modules/datatables/media/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/modules/datatables.net-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/modules/datatables.net-select-bs4/js/select.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('js/price_rule/index.js') }}"></script>
    <script src="{{ asset('assets/modules/sweetalert/sweetalert2@11.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
@endsection
