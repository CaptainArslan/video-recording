@extends('layouts.app')
@section('title', 'Edit User')

@section('section')
    <div class="card">
        <div class="card-body">
            <h5 class="card-title fw-semibold mb-4">Update Plan</h5>
            <div class="card shadow-none">
                {{-- <form>
                    Update plan only here
                </form> --}}
                <form method="POST" action="{{ route('users.update', $user->id) }}">
                    @csrf
                    <div class="form-group">
                        <label for="exampleInputEmail1">Select Plan</label>
                        <select name="plan_id" id="" class="form-control" placeholder="Please select plan" required>
                            <option value="">
                                Please select plan
                            </option>
                            @foreach ($plans as $plan)
                                <option value="{{ $plan->id }}" @if ($user->plan_id == $plan->id) selected @endif>
                                    {{ $plan->title }} </option>
                            @endforeach
                        </select>
                        @error('plan_id')
                            <div class="text-danger">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                    <div class="mt-2">
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@section('js')
    <script src="{{ asset('assets/libs/apexcharts/dist/apexcharts.min.js') }}"></script>
    <script src="{{ asset('assets/libs/simplebar/dist/simplebar.js') }}"></script>
@endsection
