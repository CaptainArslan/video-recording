<form action="{{ $action }}" method="{{ $method }}" @if($enctype) enctype="multipart/form-data" @endif>
    <x-auth-session-status class="mb-4" :status="session('status')" />
    <x-auth-validation-errors class="mb-4 text-danger" :errors="$errors" />
    <div class="row">
        @if ( $method !== 'GET')
        @csrf
        @endif
        @foreach($fields as $attr)
        @if(is_array($attr))
        <x-form.input :attr="$attr" />
        @endif
        @endforeach
        <div class="row">
            <div class="col-md-12" style="text-align: right !important">
                <button type="submit" class="btn btn-primary">Submit</button>
            </div>
        </div>
    </div>
</form>