@extends('admin.layouts.master')

@section('main-content')
    <section class="section">
        <div class="section-header">
            <h1>Products</h1>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row mb-3" style="display: flex; gap: 10px; flex-wrap: nowrap">
                                <div class="col-md-4">
                                    <label for="sections" class="form-label">Sections Filter</label>
                                    <select class="select2 form-select w-50" multiple id="sections" data-section-types-url="{{ route('products.get_section_types') }}" data-categories-url="{{ route('products.categories') }}">
                                        @foreach ($sections as $section)
                                            <option value="{{ $section }}">{{ $section }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="section_types" class="form-label">Section Types Filter</label>
                                    <select class="select2 form-select w-50" multiple id="section_types" data-sections-url="{{ route('products.categories') }}">
                                        @foreach ($sectionTypes as $section)
                                            <option value="{{ $section }}">{{ $section }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="category_id" class="form-label">Categories Filter</label>
                                    <select class="select2 form-select w-50" multiple id="category_id">
                                        @foreach ($categories as $key => $item)
                                            <option value="{{ $key }}">{{ $item }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
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
                                    <a href="#" data-toggle="modal" data-target="#productCreateModal"
                                        class="btn btn-icon icon-left btn-info export-all-btn" style="height: 35px">
                                        <i class="fa fa-plus"></i> Create
                                    </a>
                                    <a href="{{ route('products.export') }}"
                                        class="btn btn-icon icon-left btn-success export-all-btn" style="height: 35px">
                                        <span class="export-content">
                                            <i class="fas fa-file-excel"></i> Export All
                                        </span>
                                        <span class="export-loader d-none">
                                            <span class="spinner-border spinner-border-sm" role="status"></span>
                                            Exporting...
                                        </span>
                                    </a>
                                    <a href="{{ route('products.current.export') }}"
                                        class="btn btn-icon icon-left btn-success export-current-btn" style="height: 35px">
                                        <span class="export-content">
                                            <i class="fas fa-file-excel"></i> Export Current
                                        </span>
                                        <span class="export-loader d-none">
                                            <span class="spinner-border spinner-border-sm" role="status"></span>
                                            Exporting...
                                        </span>
                                    </a>
                                    {{-- <a href="{{ route('products.sync') }}"
                                        class="btn btn-icon icon-left btn-primary sync-products-btn" style="height: 35px">
                                        <span class="export-content">
                                            <i class="fas fa-sync"></i> Sync Products
                                        </span>
                                        <span class="export-loader d-none">
                                            <span class="spinner-border spinner-border-sm" role="status"></span>
                                            Syncing...
                                        </span></a> --}}
                                </div>
                            </div>

                            <table class="table table-striped" id="maintable" data-url="{{ route('products') }}"
                                style="width: 180%!important;">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Image</th>
                                        <th>Arabic Name</th>
                                        <th>English Name</th>
                                        <th>Arabic Category</th>
                                        <th>English Category</th>
                                        <th>Price</th>
                                        <th>Arabic Brand</th>
                                        <th>English Brand</th>
                                        <th>Arabic Description</th>
                                        <th>English Description</th>
                                        <th>Store</th>
                                        <th>Barcode</th>
                                        <th>Creation Date</th>
                                        <th>View In App</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Details Modal -->
    <div class="modal fade" id="productDetailsModal" tabindex="-1" role="dialog"
        aria-labelledby="productDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="productDetailsModalLabel">Product Details</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label style="font-weight: bolder; font-size: 14px">Arabic Name:</label>
                                <p class="detail-ar-name form-control-plaintext"></p>
                            </div>
                            <div class="form-group">
                                <label style="font-weight: bolder; font-size: 14px">English Name:</label>
                                <p class="detail-en-name form-control-plaintext"></p>
                            </div>
                            <div class="form-group">
                                <label style="font-weight: bolder; font-size: 14px">Price:</label>
                                <p class="detail-price form-control-plaintext"></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label style="font-weight: bolder; font-size: 14px">Arabic Brand:</label>
                                <p class="detail-ar-brand form-control-plaintext"></p>
                            </div>
                            <div class="form-group">
                                <label style="font-weight: bolder; font-size: 14px">English Brand:</label>
                                <p class="detail-en-brand form-control-plaintext"></p>
                            </div>
                            <div class="form-group">
                                <label style="font-weight: bolder; font-size: 14px">Barcode:</label>
                                <p class="detail-barcode form-control-plaintext"></p>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label style="font-weight: bolder; font-size: 14px">Arabic Description:</label>
                                <p class="detail-ar-desc form-control-plaintext"></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label style="font-weight: bolder; font-size: 14px">English Description:</label>
                                <p class="detail-en-desc form-control-plaintext"></p>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label style="font-weight: bolder; font-size: 14px">Store:</label>
                                <p class="detail-store form-control-plaintext"></p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label style="font-weight: bolder; font-size: 14px">Creation Date:</label>
                                <p class="detail-creation-date form-control-plaintext"></p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label style="font-weight: bolder; font-size: 14px">View In App:</label>
                                <p class="detail-in-app form-control-plaintext"></p>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <label style="font-weight: bolder; font-size: 14px">Images:</label>
                            <div id="view-image-preview" class="mt-2"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="productEditModal" tabindex="-1" role="dialog"
        aria-labelledby="productDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="productDetailsModalLabel">Product Details</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="" method="POST" data-update-url="{{ route('product.edit') }}" id="update-form"
                    enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" id="product_id" name="product_id">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Arabic Name:</label>
                                    <input type="text" name="ar_name" class="detail-ar-name form-control" required>
                                </div>
                                <div class="form-group">
                                    <label>English Name:</label>
                                    <input type="text" name="en_name" class="detail-en-name form-control" required>
                                </div>
                                <div class="form-group">
                                    <label>Price:</label>
                                    <input type="number" name="price" class="detail-price form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Arabic Brand:</label>
                                    <input type="text" name="ar_brand" class="detail-ar-brand form-control" required>
                                </div>
                                <div class="form-group">
                                    <label>English Brand:</label>
                                    <input type="text" name="en_brand" class="detail-en-brand form-control" required>
                                </div>
                                <div class="form-group">
                                    <label>Barcode:</label>
                                    <input type="text" name="barcode" class="detail-barcode form-control">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Arabic Description:</label>
                                    <textarea name="ar_description" class="detail-ar-desc form-control h-25" cols="30" rows="5" required></textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>English Description:</label>
                                    <textarea name="en_description" class="detail-en-desc form-control h-25" cols="30" rows="5" required></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Store:</label>
                                    <input type="text" name="store" class="detail-store form-control"
                                        value="Shein">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Creation Date:</label>
                                    <input type="date" name="creation_date" class="detail-creation-date form-control">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>View In App:</label>
                                    <input type="checkbox" name="view_in_app" class="detail-in-app form-control"
                                        style="width: 20px">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Images:</label>
                                    <input type="file" name="images[]" multiple
                                        class="edit-product-images form-control">
                                </div>
                                <div id="edit-image-preview" class="mt-2"></div>
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
    <div class="modal fade" id="productCreateModal" tabindex="-1" role="dialog"
        aria-labelledby="productDetailsModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="productDetailsModalLabel">Product Details</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('product.create') }}" method="POST" id="create-form"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Arabic Name:</label>
                                    <input type="text" name="ar_name" class="detail-ar-name form-control" required>
                                </div>
                                <div class="form-group">
                                    <label>English Name:</label>
                                    <input type="text" name="en_name" class="detail-en-name form-control" required>
                                </div>
                                <div class="form-group">
                                    <label>Price:</label>
                                    <input type="number" name="price" class="detail-price form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Arabic Brand:</label>
                                    <input type="text" name="ar_brand" class="detail-ar-brand form-control" required>
                                </div>
                                <div class="form-group">
                                    <label>English Brand:</label>
                                    <input type="text" name="en_brand" class="detail-en-brand form-control" required>
                                </div>
                                <div class="form-group">
                                    <label>Barcode:</label>
                                    <input type="text" name="barcode" class="detail-barcode form-control">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Arabic Description:</label>
                                    <textarea name="ar_description" class="detail-ar-desc form-control h-25" cols="30" rows="5" required></textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>English Description:</label>
                                    <textarea name="en_description" class="detail-en-desc form-control h-25" cols="30" rows="5" required></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Store:</label>
                                    <input type="text" name="store" class="detail-store form-control"
                                        value="Shein">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Creation Date:</label>
                                    <input type="date" name="creation_date" class="detail-creation-date form-control">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>View In App:</label>
                                    <input type="checkbox" name="view_in_app" class="detail-in-app form-control"
                                        style="width: 20px">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Images:</label>
                                    <input type="file" name="images[]" multiple class="product-images form-control">
                                </div>
                                <div id="image-preview" class="mt-2"></div>
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
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">

    <style>
        .export-btn {
            position: relative;
        }

        .export-btn.loading .export-content {
            visibility: hidden;
        }

        .export-btn.loading .export-loader {
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        /* Add to your CSS file */
        .modal-body .form-group {
            margin-bottom: 1rem;
            padding: 0.5rem;
            border-bottom: 1px solid #eee;
        }

        .modal-body .form-control-plaintext {
            padding: 0.375rem 0;
            font-weight: 500;
        }

        .modal-body label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 0.25rem;
            display: block;
        }
    </style>
    <link rel="stylesheet" href="{{ asset('assets/modules/datatables.net-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/modules/datatables.net-select-bs4/css/select.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.css" />
@endsection

@section('scripts')
    <script src="{{ asset('assets/modules/datatables/media/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/modules/datatables.net-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/modules/datatables.net-select-bs4/js/select.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('js/product/index.js') }}"></script>
    <script src="{{ asset('assets/modules/sweetalert/sweetalert2@11.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.umd.js"></script>
@endsection
