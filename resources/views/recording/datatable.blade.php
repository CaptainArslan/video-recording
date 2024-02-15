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
        ]
    });

    $('body').on('click', '#history-tab', function(e) {
        e.preventDefault();
        table.ajax.reload();
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
                            setTimeout(() => {
                                location.reload();
                            }, 1000);
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
</script>
