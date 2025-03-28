"use strict";
$(document).ready(function () {
    loadProducts();

    function loadProducts() {
        var table = $('#maintable').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 10,
            ajax: {
                url: $('#maintable').attr('data-url')
            },
            columns: [
                { data: 'id', name: 'id', width: '50px' },
                { data: 'main_image', name: 'main_image', orderable: false, searchable: false, visible: false },
                { data: 'ar_name', name: 'ar_name', visible: false },
                { data: 'en_name', name: 'en_name' },
                { data: 'price', name: 'price' },
                { data: 'ar_brand', name: 'ar_brand', orderable: false, visible: false },
                { data: 'en_brand', name: 'en_brand', orderable: false },
                { data: 'ar_description', name: 'ar_description', orderable: false, visible: false, width: '20%' },
                { data: 'en_description', name: 'en_description', orderable: false, width: '20%' },
                { data: 'store', name: 'store' },
                { data: 'barcode', name: 'barcode' },
                { data: 'creation_date', name: 'creation_date' },
                { data: 'in_app_view', name: 'in_app_view', orderable: false, searchable: false },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ],
            ordering: false
        });
        $('#columnToggles').empty();
        table.columns().every(function (index) {
            var column = this;
            var columnName = $(column.header()).text().trim();
            if (columnName !== '') {
                var isChecked = column.visible() ? 'checked' : '';
                $('#columnToggles').append(
                    `<div class="form-check">
                        <input class="form-check-input toggle-column" type="checkbox" data-column="${index}" ${isChecked}>
                        <label class="form-check-label">${columnName}</label>
                    </div>`
                );
            }
        });
        $('.toggle-column').on('change', function () {
            var column = table.column($(this).data('column'));
            column.visible(!column.visible());
        });
    }

    $('.export-current-btn').on('click', function (event) {
        event.preventDefault();
        var $btn = $(this);
        showLoader($btn)
        const table = $('#maintable').DataTable();
        const info = table.page.info();
        const params = {
            page: info.page + 1,
            per_page: info.length,
            search: table.search(),
            _token: '{{ csrf_token() }}'
        };

        $.ajax({
            url: $btn.attr('href'),
            method: 'POST',
            data: params,
            success: function (data) {
                if (data.download_url) {
                    showSuccessMessage('success', data.message);
                    window.location.href = data.download_url;
                }
                hideLoader($btn);
            },
            error: function (xhr, status, error) {
                showSuccessMessage('error', error.responseJSON.message);
                console.error('Export failed:', error);
                hideLoader($btn);
            }
        });
    });

    $('.export-all-btn').on('click', function (e) {
        if ($(this).attr('href').includes('export')) {
            e.preventDefault();
            var $btn = $(this);
            showLoader($btn);

            $.ajax({
                url: $btn.attr('href'),
                method: 'GET',
                success: function (data) {
                    if (data.download_url) {
                        showSuccessMessage('success', data.message);
                        window.location.href = data.download_url;
                    }
                    hideLoader($btn);
                },
                error: function (xhr, status, error) {
                    showSuccessMessage('error', error.responseJSON.message);
                    console.error('Export failed:', error);
                    hideLoader($btn);
                }
            });
        }
    });

    $('.sync-products-btn').on('click', function (e) {
        if ($(this).attr('href').includes('sync')) {
            e.preventDefault();
            var $btn = $(this);
            showLoader($btn);

            $.ajax({
                url: $btn.attr('href'),
                method: 'GET',
                success: function (data) {
                    showSuccessMessage('success', data.message);
                    if (data.download_url) {
                        window.location.href = data.download_url;
                    }
                    hideLoader($btn);
                },
                error: function (xhr, status, error) {
                    showSuccessMessage('error', error.responseJSON.message);
                    console.error('Export failed:', error);
                    hideLoader($btn);
                }
            });
        }
    });

    $(document).on('click', '.show-product', function (e) {
        e.preventDefault();
        $.ajax({
            url: $(this).data('url'),
            type: 'GET',
            success: function (result) {
                var data = result.data;
                $('#productDetailsModal .detail-ar-name').text(data.ar_name);
                $('#productDetailsModal .detail-en-name').text(data.en_name);
                $('#productDetailsModal .detail-price').text(data.price);
                $('#productDetailsModal .detail-ar-brand').text(data.ar_brand);
                $('#productDetailsModal .detail-en-brand').text(data.en_brand);
                $('#productDetailsModal .detail-ar-desc').text(data.ar_description);
                $('#productDetailsModal .detail-en-desc').text(data.en_description);
                $('#productDetailsModal .detail-store').text(data.store);
                $('#productDetailsModal .detail-barcode').text(data.barcode);
                $('#productDetailsModal .detail-creation-date').text(data.creation_date);
                $('#productDetailsModal .detail-in-app').text(data.view_in_app ? 'Yes' : 'No');
                $('#productDetailsModal').modal({
                    backdrop: 'static',
                    keyboard: false
                });

            }
        })
    });

    $(document).on('click', '.edit-product', function (e) {
        e.preventDefault();
        $.ajax({
            url: $(this).data('url'),
            type: 'GET',
            success: function (result) {
                var data = result.data;
                $('#productEditModal .detail-ar-name').val(data.ar_name);
                $('#productEditModal .detail-en-name').val(data.en_name);
                $('#productEditModal .detail-price').val(data.price);
                $('#productEditModal .detail-ar-brand').val(data.ar_brand);
                $('#productEditModal .detail-en-brand').val(data.en_brand);
                $('#productEditModal .detail-ar-desc').text(data.ar_description);
                $('#productEditModal .detail-en-desc').text(data.en_description);
                $('#productEditModal .detail-store').val(data.store);
                $('#productEditModal .detail-barcode').val(data.barcode);
                $('#productEditModal .detail-creation-date').val(data.creation_date);
                $('#productEditModal #product_id').val(data.id);
                var checked = data.view_in_app ? true : false;
                $('#productEditModal .detail-in-app').attr('checked', checked);
                var formAction = $('#update-form').data('updateUrl') +'/' + data.id;
                $('#productEditModal #update-form').attr('action', formAction);
                $('#productEditModal').modal({
                    backdrop: 'static',
                    keyboard: false
                }).on('shown.bs.modal', function() {
                    // Bind events when modal is shown
                    $('.modal-edit-btn').off('click').on('click', function() {
                        $('#update-form').submit();
                    });
                });;

            }
        })
    });

    $(document).on('click', '.in-app-view', function(e){
        var btn = $(this);
        btn.prop('disabled', true).css('pointer-events', 'none');
        var url = btn.data('url');
        $.ajax({
            url: url,
            type: 'GET',
            success: function(result){       
                btn.prop('disabled', false).css('pointer-events', 'auto');        
                showSuccessMessage('success', result.message)
            }
        })
        
    });

    $(document).on('click', '.delete-product',function(e) {
        e.preventDefault();
        const url = $(this).data('url');
        console.log(url);
        
        const token = $(this).data('csrf');
        const name = $(this).data('name');
        
        Swal.fire({
            title: 'Are you sure?',
            text: `You won't be able to revert this! Product: ${name}`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: url,
                    type: 'POST',
                    data: {
                        _token: token
                    },
                    success: function(response) {
                        Swal.fire(
                            'Deleted!',
                            response.message || 'Item has been deleted.',
                            'success'
                        ).then(() => {
                            $('#maintable').DataTable().ajax.reload(null);
                        });
                    },
                    error: function(xhr) {
                        Swal.fire(
                            'Error!',
                            xhr.responseJSON.message || 'Failed to delete item.',
                            'error'
                        );
                    }
                });
            }
        });
    });

    function showLoader($btn) {
        if (!$btn.find('.export-content').hasClass('d-none')) {
            $btn.find('.export-content').addClass('d-none');
            $btn.find('.export-loader').removeClass('d-none');
            $btn.prop('disabled', true).css('pointer-events', 'none');
        }
    }

    function hideLoader($btn) {
        if ($btn.find('.export-content').hasClass('d-none')) {
            $btn.find('.export-content').removeClass('d-none');
            $btn.find('.export-loader').addClass('d-none');
            $btn.prop('disabled', false).css('pointer-events', 'auto');
        }
    }

    function showSuccessMessage(type, message) {
        Swal.fire({
            icon: type,
            title: type === 'success' ? 'Success' : 'Error',
            text: message,
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000
        });
    }
});
