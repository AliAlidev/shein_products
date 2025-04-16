"use strict";
$(document).ready(function () {
    loadPriceRules();

    function loadPriceRules() {
        var table = $('#maintable').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 10,
            ajax: {
                url: $('#maintable').attr('data-url'),
                data: function (d) {
                    d.category_id = $('#category_id').val();
                }
            },
            columns: [
                { data: 'id', name: 'id', width: '50px' },
                { data: 'name', name: 'name' },
                { data: 'apply_per', name: 'apply_per' },
                // { data: 'apply_to', name: 'apply_to'},
                { data: 'type', name: 'type' },
                { data: 'value', name: 'value' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ],
            ordering: false
        });
    }

    $('#sections').on('change', function (e) {
        var channel = $(this).val();
        var url = $(this).data('sectionTypesUrl') + '/' + channel;
        fillSectionTypes('section_types', url);
        var categoriesUrl = $(this).data('categoriesUrl');
        fillCategories('apply_to', categoriesUrl);
    });

    $('#section_types').on('change', function () {
        var channel = $('#sections').val();
        var sectionType = $(this).val();
        var sectionsUrl = $(this).data('sectionsUrl') + '/' + channel + "/" + sectionType;
        fillCategories('apply_to', sectionsUrl);
    });

    $(document).on('click', '.edit-price-rule', function (e) {
        e.preventDefault();
        var url = $(this).data('url');
        $.ajax({
            url: url,
            type: 'GET',
            success: function (result) {
                var data = result.data;
                $('#priceRuleUpdateModal #name').val(data.name);
                $('#priceRuleUpdateModal #apply_per_update').val(data.apply_per);
                if (data.apply_per == 'Default') {
                    $('#priceRuleUpdateModal #apply_to_div_update').hide();
                } else if (data.apply_per == 'Category') {
                    $('#priceRuleUpdateModal #apply_to_div_update').show();
                    $('#sections_update_div').show();
                    $('#section_types_update_div').show();
                    $('#priceRuleUpdateModal #apply_to').empty();
                    fillCategories('apply_to_update', $('#apply_to_div_update').data('categoriesUrl'), data.apply_to);
                } else if (data.apply_per == 'Product') {
                    $('#priceRuleUpdateModal #apply_to_div_update').show();
                    $('#sections_update_div').hide();
                    $('#section_types_update_div').hide();
                    $('#priceRuleUpdateModal #apply_to').empty();
                    fillProducts('apply_to_update', $('#apply_to_div_update').data('productsUrl'), data.apply_to);
                }
                $('#priceRuleUpdateModal #apply_to').val(data.apply_to);
                $('#priceRuleUpdateModal #type').val(data.type);
                $('#priceRuleUpdateModal #value').val(data.value);

                $('#priceRuleUpdateModal #update-form').attr('action', url);
                $('#priceRuleUpdateModal').modal({
                    backdrop: 'static',
                    keyboard: false
                }).on('shown.bs.modal', function () {
                    $('.modal-edit-btn').off('click').on('click', function () {
                        e.preventDefault();
                        let $form = $('#update-form');
                        if ($form[0].checkValidity() === false) {
                            e.stopPropagation();
                            $form.addClass('was-validated');
                            return;
                        }
                        const formData = new FormData($form[0]);
                        formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
                        $.ajax({
                            url: $form.attr('action'),
                            method: $form.attr('method'),
                            processData: false,
                            contentType: false,
                            data: formData
                        }).then(function (response) {
                            if (response.success) {
                                showMessage('success', response.message);
                                $('#priceRuleUpdateModal').modal('hide');
                                $('#maintable').DataTable().ajax.reload(null);
                            } else {
                                showMessage('error', response.message);
                            }
                        }).catch(function (xhr, status, error) {
                            showMessage('error', xhr.responseJSON.message);
                        });
                    });
                });

            }
        })
    });

    $(document).on('click', '.delete-price-rule', function (e) {
        e.preventDefault();
        const url = $(this).data('url');
        const token = $(this).data('csrf');
        const name = $(this).data('name');
        Swal.fire({
            title: 'Are you sure?',
            text: `You won't be able to revert this! Price Rule: ${name}`,
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
                    success: function (response) {
                        Swal.fire(
                            'Deleted!',
                            response.message || 'Item has been deleted.',
                            'success'
                        ).then(() => {
                            $('#maintable').DataTable().ajax.reload(null);
                        });
                    },
                    error: function (xhr) {
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

    $('#priceRuleCreateModal').on('shown.bs.modal', function (e) {
        $('.modal-create-btn').off('click').on('click', function (e) {
            e.preventDefault();
            let $form = $('#create-form');
            if ($form[0].checkValidity() === false) {
                e.stopPropagation();
                $form.addClass('was-validated');
                return;
            }
            const formData = new FormData($('#create-form')[0]);
            formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
            $.ajax({
                url: $('#create-form').attr('action'),
                method: $('#create-form').attr('method'),
                processData: false,
                contentType: false,
                data: formData
            }).then(function (response) {
                if (response.success) {
                    showMessage('success', response.message);
                    $('#priceRuleCreateModal').modal('hide');
                    $('#maintable').DataTable().ajax.reload(null);
                } else {
                    showMessage('error', response.message);
                }
            }).catch(function (xhr, status, error) {
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    Object.values(xhr.responseJSON.errors).forEach(function (messages) {
                        messages.forEach(function (msg) {
                            showMessage('error', msg);
                        });
                    });
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    // Fallback for single error message
                    showMessage('error', xhr.responseJSON.message);
                } else {
                    // Generic fallback
                    showMessage('error', 'An unexpected error occurred.');
                }
            });
        });
    });

    $('#priceRuleCreateModal').on('hidden.bs.modal', function (e) {
        $('#create-form')[0].reset();
        $('#create-form').find('.select2').val(null).trigger('change');
        $('#create-form').removeClass('was-validated');
    });

    $('#priceRuleUpdateModal').on('hidden.bs.modal', function (e) {
        $('#update-form')[0].reset();
        $('#update-form').find('.select2').val(null).trigger('change');
        $('#update-form').removeClass('was-validated');
        $('#edit-image-preview').empty();
    });

    function showMessage(type, message) {
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

    $('#apply_to, #apply_to_update').select2({
        multiple: true
    });

    $('#apply_per').on('change', function () {
        if (this.value != 'Default') {
            $('#apply_to_div').show();
            $('#apply_to').empty();
            if (this.value == 'Category') {
                $('#sections_div').show();
                $('#section_types_div').show();
                fillCategories('apply_to', $(this).data('categoriesUrl'));
            }
            else if (this.value == 'Product') {
                $('#sections_div').hide();
                $('#section_types_div').hide();
                fillProducts('apply_to', $(this).data('productsUrl'));
            }
        } else {
            $('#apply_to_div').hide();
        }
    });

    $('#apply_per_update').on('change', function () {
        if (this.value != 'Default') {
            $('#apply_to_div_update').show();
            $('#apply_to_update').empty();
            if (this.value == 'Category') {
                $('#sections_update_div').show();
                $('#section_types_update_div').show();
                fillCategories('apply_to_update', $(this).data('categoriesUrl'));
            }
            else if (this.value == 'Product') {
                $('#sections_update_div').hide();
                $('#section_types_update_div').hide();
                fillProducts('apply_to_update', $(this).data('productsUrl'));
            }
        } else {
            $('#apply_to_div_update').hide();
        }
    });

    function fillCategories(divId, categoriesUrl, selected = []) {
        $('#' + divId).select2({
            placeholder: 'Search for a category'});
        $('#' + divId).empty();
        $.ajax({
            url: categoriesUrl,
            type: "GET",
            success: function (response) {
                Object.entries(response.data).forEach(category => {
                    const option = new Option(category[1], category[0]);
                    if (Array.isArray(selected) && selected.includes(category[0].toString())) {
                        option.selected = true;
                    }
                    $('#' + divId).append(option);
                });
            }
        });
    }

    function fillProducts(divId, productsUrl, selected = []) {
        $('#' + divId).select2({
            placeholder: 'Search for a product',
            minimumInputLength: 2,
            allowClear: true,
            ajax: {
                url: productsUrl,
                type: 'GET',
                delay: 250, // Debounce to reduce server calls
                cache: true,
                dataType: 'json',
                processResults: function (data) {
                    return {
                        results: Object.entries(data.data).map(([id, text]) => ({id,text}))
                    };
                }
            }
        });

        if (selected) {
            Object.entries(selected).forEach(item => {
                const option = new Option(item[1], item[0], true, true);
                $('#' + divId).append(option).trigger('change');
            })
        }
    }

    function fillSectionTypes(divId, categoriesUrl) {
        $.ajax({
            url: categoriesUrl,
            type: "GET",
            success: function (response) {
                const $select = $('#' + divId);
                $select.empty(); // Clear previous options
                Object.entries(response.data).forEach(category => {
                    const option = new Option(category[1], category[1], false, false);
                    $select.append(option);
                });
            }
        });
    }
});
