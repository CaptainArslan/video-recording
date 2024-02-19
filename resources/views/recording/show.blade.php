{{-- @extends('layouts.app')
@section('title', 'Show Recording')
@section('section')

@endsection
@section('js')

@endsection --}}
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recording Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

    <div class="container p-3">
        <div class="card-body d-flex align-items-center justify-content-between">
            <h3 class="card-title fw-bold">{{ $recording->title }}</h3>
            @auth
                <a class="btn btn-primary" href="{{ route('recordings.index') }}">Back</a>
            @endauth
        </div>
        <div class="card-body mt-2">
            <video class="w-100" controls>
                <source src="{{ $recording->file_url }}" type="video/mp4">
                Your browser does not support the video tag.
            </video>
            <p>{{ $recording->description ?? '' }}</p>
        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous">
    </script>
</body>

</html>
