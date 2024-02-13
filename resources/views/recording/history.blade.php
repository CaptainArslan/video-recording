<h2 class="font-weight-bold">Share History</h2>
<div class="table-responsive">
    <table class="table text-nowrap mb-0 align-middle" id="table">
        <thead class="text-dark fs-4">
            <tr>
                <th class="border-bottom-0">
                    <h6 class="fw-semibold mb-0">Id</h6>
                </th>
                @foreach ($tableFields as $key => $tblfields)
                    <th class="border-bottom-0">
                        <h6 class="fw-semibold mb-0">{{ $tblfields }}</h6>
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            <!-- table set by the yajra datatables -->
        </tbody>
    </table>
</div>
