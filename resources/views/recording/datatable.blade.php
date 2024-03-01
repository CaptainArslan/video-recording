<script>
    var table = $('#table').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        "order": [],
        ajax: "{{ route('sharelog.data') }}",
        columns: [{
                data: 'DT_RowIndex',
                // data: 'id',
                name: 'id'
            },
            @foreach ($tableFields as $key => $value)
                {
                    data: '{{ $key }}',
                    name: '{{ $key }}',
                    @if ($key === 'action' || $key === 'Action')
                        searchable: false,
                        orderable: false
                    @endif
                },
            @endforeach
        ],
        "language": {
            "emptyTable": "No data available in table",
            "loadingRecords": "Loading...",
            "processing": "Processing...",
            "search": "Search:",
            "zeroRecords": "No matching records found",
            "paginate": {
                "first": "First",
                "last": "Last",
                "next": "Next",
                "previous": "Previous"
            },
            "info": "Showing _START_ to _END_ of _TOTAL_ entries",
            "infoEmpty": "Showing 0 to 0 of 0 entries",
            "lengthMenu": "Show _MENU_ entries"
        }
    });

    $('body').on('click', '#history-tab', function(e) {
        e.preventDefault();
        table.ajax.reload();
    });

    $('body').on('click', '.resend', function(e) {
        e.preventDefault();
        let id = $(this).data('id');

        // Show SweetAlert confirmation dialog
        Swal.fire({
            title: 'Confirmation',
            text: 'Are you sure you want to resend this?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, resend it!'
        }).then((result) => {
            if (result.isConfirmed) {
                // If user confirms, start loading and make the AJAX call
                loadingStart('Resending...');
                $.ajax({
                    type: 'POST',
                    url: "{{ url('sharelog/retry/') }}/" + id,
                    data: {
                        _token: "{{ csrf_token() }}",
                        id: id
                    },
                    success: function(data) {
                        if (data.success == true) {
                            toastr.success(data.message);
                            table.ajax.reload();
                        } else {
                            toastr.error('Error Occurred while resending!');
                        }
                        loadingStop();
                    },
                    error: function(error) {
                        let message = 'Network error';
                        if (error.responseJSON) {
                            message = error.responseJSON.message
                        }
                        toastr.error('Error Occurred while resending record!');
                        loadingStop();
                    }
                });
            }
        });
    });

    function deleteRecordAjax(url) {
        return new swal({
            title: 'Are you sure?',
            text: 'You will not be able to recover this record!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: 'DELETE',
                    url: url,
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(data) {
                        if (data.success == true) {
                            toastr.success(data.message);
                            // setTimeout(() => {
                            //     location.reload();
                            // }, 1000);
                            // table.ajax.reload();
                            fetchData(1);
                        } else {
                            toastr.error('Error Occured while deleteing record!');
                        }
                    },
                    error: function(error) {
                        let message = 'Network error';
                        if (error.responseJSON) {
                            message = error.responseJSON.message
                        }
                        // console.log(message);
                        toastr.error('Error Occured while deleteing record!');
                    }
                });
            }
        });
    }

    function statusRecordAjax(param, url) {
        return new swal({
            title: 'Are you sure?',
            text: '',
            icon: 'info',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, confirm!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: 'PUT',
                    url: url,
                    data: {
                        _token: "{{ csrf_token() }}",
                        status: $(param).data('status')
                    },
                    success: function(data) {
                        if (data.success == true) {
                            toastr.success(data.message);
                            // setTimeout(() => {
                            //     location.reload();
                            // }, 1000);
                            // table.ajax.reload();
                            fetchData(1);
                        } else {
                            toastr.error('Error Occured while pulishing record!');
                        }
                    },
                    error: function(error) {
                        let message = 'Network error';
                        if (error.responseJSON) {
                            message = error.responseJSON.message
                        }
                        // console.log(message);
                        toastr.error('Error Occured while pulishing record!');
                    }
                });
            }
        });
    }
</script>
