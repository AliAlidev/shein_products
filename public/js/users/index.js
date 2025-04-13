"use strict";
$(document).ready(function () {
    loadUsers();

    function loadUsers() {
        var table = $('#maintable').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 10,
            ajax: {
                url: $('#maintable').attr('data-url')
            },
            columns: [
                { data: 'id', name: 'id', width: '50px' },
                { data: 'name', name: 'name' },
                { data: 'email', name: 'email' },
                { data: 'number', name: 'number' },
                { data: 'role', name: 'role' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ],
            ordering: false
        });
    }

    $(document).on('click', '.generate-token', function (e) {
        e.preventDefault();
        let url = $(this).data('url');

        $.post(url, {
            _token: $('meta[name="csrf-token"]').attr('content')
        })
            .done(function (response) {
                showMessage(response.data);
            })
            .fail(function (xhr) {
                showAlert(xhr.responseJSON.message || 'Something went wrong');
            });
    });

    function showAlert(message) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: message,
            toast: false,
            position: 'center',
            showConfirmButton: false,
            timer: 3000
        });
    }

    function showMessage(message) {
        Swal.fire({
            title: 'Authentication Token',
            html: `
                <div id="swal-copy-content" style="margin-bottom: 10px;">
                    🔐 Your long term token is: <strong>${message}</strong>
                </div>
                <button id="copy-btn" class="swal2-confirm swal2-styled">Copy</button>
            `,
            showConfirmButton: false,
            didOpen: () => {
                document.getElementById('copy-btn').addEventListener('click', function () {
                    navigator.clipboard.writeText(message).then(() => {
                        Swal.fire({
                            icon: 'success',
                            title: 'Copied!',
                            text: 'The content was copied to your clipboard.',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    }).catch(() => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Failed!',
                            text: 'Could not copy the content.',
                        });
                    });
                });
            }
        });
    }
});
