@extends('layouts.app')
@section('title', 'Users')
@section('section')
    {{-- <div class="card shadow-none">
        <div class="card-body d-flex align-items-center justify-content-between">
            <h5 class="card-title fw-semibold">{{ $title }} </h5>
            <a href="{{ route('users.create') }}" class="btn btn-primary"> Add </a>
        </div>
    </div> --}}
    <div class="row">
        <div class="col-lg-12 d-flex align-items-stretch">
            <div class="card w-100">
                <div class="card-body p-4">
                    <h5 class="card-title fw-semibold mb-4">{{ $title }}</h5>
                    <div class="table-responsive">
                        <table class="table text-nowrap mb-0 align-middle" id="table">
                            <thead class="text-dark fs-4">
                                <tr>
                                    <th class="border-bottom-0">
                                        <h6 class="fw-semibold mb-0">Id</h6>
                                    </th>
                                    @foreach ($tableFields as $key => $fields)
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
    @include('partials.datatable-js')
    <script>
        loadData();

        function loadData() {
            loadingStart();
            $('#table').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                "order": [],
                ajax: "{{ route('users.index') }}",
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
            loadingStop();
        }
    </script>

@endsection
