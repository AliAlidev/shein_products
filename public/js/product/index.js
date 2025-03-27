"use strict";
$(document).ready(function () {
    loadProducts();

    function loadProducts(user_id = 0) {
        var table = $('#maintable').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 10,
            ajax: {
                url: $('#maintable').attr('data-url'),
                data: { user_id: user_id }
            },
            columns: [
                { data: 'id', name: 'id', width: '50px' },
                { data: 'main_image', name: 'main_image', orderable: false, searchable: false, visible: false },
                { data: 'ar_name', name: 'ar_name', visible: false },
                { data: 'en_name', name: 'en_name' },
                { data: 'price', name: 'price' },
                { data: 'ar_brand', name: 'ar_brand', orderable: false, visible: false },
                { data: 'en_brand', name: 'en_brand', orderable: false },
                { data: 'ar_description', name: 'ar_description', orderable: false, visible: false },
                { data: 'en_description', name: 'en_description', orderable: false },
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

});
