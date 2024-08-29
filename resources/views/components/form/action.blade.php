@if ($dropdown)
<a href="#" class="btn btn-light btn-active-light-primary btn-sm" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end" data-kt-menu-flip="top-end">
    Actions
    <span class="svg-icon svg-icon-5 m-0">
        <i class="fa fa-angle-down"></i>
    </span>
</a>
<div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-bold fs-7 w-125px py-4" data-kt-menu="true">
    @foreach ($actions as $action)
    <div class="-item px-3">
        <a href="{{ route($action['route'], $id) }}" class="menu-link px-3" data-id="{{ $id }}">
            {{ $action['title'] }}
        </a>
    </div>
    @endforeach
</div>
@else
@foreach ($actions as $action)
<a href="{{ route($action['route'], $id) }}" data-id="{{ $id }}" class="btn btn-sm btn-light btn-active-light-primary {{ $action['extraclass'] ?? '' }}">
    {{ $action['title'] }}
    @if (isset($action['extraclass']) && $action['extraclass'] == 'importreport')
    <span class="badge badge-success ms-1 ml-1">{{ $counter ?? 0 }}</span>
    @endif
</a>
@endforeach
@endif