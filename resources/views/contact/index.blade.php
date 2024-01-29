@extends('layouts.app')
@section('title', 'Settings')
@section('section')
<div class="card shadow-none">
    <div class="card-body d-flex align-items-center justify-content-between">
        <h5 class="card-title fw-semibold"> {{ $title }} </h5>
        <div class="buttons">
            <a href="{{ route('contacts.create') }}" class="btn btn-primary"> Add </a>
            <a href="#" class="btn btn-danger sync-contact" id="sync-contact"> Sync with Gohighlevel </a>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-lg-12 d-flex align-items-stretch">
        <div class="card w-100">
            <div class="card-body p-4">
                <h5 class="card-title fw-semibold mb-4">All Contacts</h5>
                <div class="table-responsive">
                    <table class="table text-nowrap mb-0 align-middle" id="table">
                        <thead class="text-dark fs-4">
                            <tr>
                                <th class="border-bottom-0">
                                    <h6 class="fw-semibold mb-0">Id</h6>
                                </th>
                                @foreach($tableFields as $key => $fields)
                                <th class="border-bottom-0">
                                    <h6 class="fw-semibold mb-0">{{ $fields }}</h6>
                                </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            <!-- table set by the yajra datatables -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('js')
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
    var table = $('#table');
    var sync = $('#sync-contact');
    var del = $('.confirm-delete');

    $(document).ready(function() {
        loadData(); // Initial load

        sync.click(function(e) {
            e.preventDefault();
            syncContacts();
        });

        $('.confirm-delete').on('click', function(e) {
            e.preventDefault(); // Prevent the default anchor tag behavior

            var url = $(this).attr('href');
            var dataId = $(this).data('id');

            // Show SweetAlert confirmation dialog
            Swal.fire({
                title: 'Are you sure?',
                text: 'You won\'t be able to revert this!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Redirect to the specified URL
                    setTimeout(function() {
                        window.location.href = url;
                    }, 300);
                }
            });
        });

    });

    function syncContacts() {
        loadingStart();
        $.ajax({
            type: "GET",
            url: "{{ route('contacts.sync') }}",
            success: function(response) {
                if (response.success == true) {
                    toastr.success(response.message);
                    loadData(); // Reload data after syncing
                } else {
                    toastr.error(response.message);
                }
            },
            error: function(data) {
                loadingStop();
                console.log("Error in ajax call: " + data);
                toastr.error("Some Error Occurred!");
            },
            complete: function() {
                loadingStop();
            }
        });
    }

    function loadData() {
        loadingStart();

        var dataTable = table.DataTable();
        dataTable.clear().draw();
        dataTable.destroy(); // Destroy the existing DataTable to remove previous settings

        // Use an array to dynamically set the columns
        var dynamicColumns = [{
            data: 'DT_RowIndex',
            name: 'id'
        }];

        @foreach($tableFields as $key => $value)
        dynamicColumns.push({
            data: '{!! $key !!}',
            name: '{{ $key }}',
            @if($key === 'action' || $key === 'Action')
            searchable: false,
            orderable: false
            @endif
        });
        @endforeach

        table.DataTable({
            processing: true,
            serverSide: true,
            "order": [],
            ajax: "{{ route('contacts.index') }}",
            columns: dynamicColumns
        });

        loadingStop();
    }
</script>


@endsection