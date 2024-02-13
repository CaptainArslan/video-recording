@foreach ($recordings as $recording)
    <div class="col-lg-4 col-md-6 col-sm-6 mb-4 d-flex align-items-stretch">
        <div class="card">
            <div class="header" style="max-height: 200px; overflow: hidden;">
                <a href="{{ route('recordings.show', encrypt($recording->id)) }}">
                    <img src="{{ $recording->poster_url ?? 'https://via.placeholder.com/600x400' }}"
                        alt="{{ $recording->title }}" class="card-img-top" style="width: 100%; height: auto;">
                </a>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title">{{ formatTimestamp($recording->title, 'M d, Y') }}</h5>
                    <div class="dropdown">
                        <a href="javascript:void(0)" role="button" id="action-buttons" data-toggle="dropdown"
                            aria-expanded="false" data-bs-toggle="actions">
                            <i class="fas fa-ellipsis-h"></i>
                        </a>
                        <div class="dropdown-menu" aria-labelledby="action-buttons">
                            <a class="dropdown-item" href="javascript:void(0)" data-toggle="modal"
                                data-target="#edit-recording-modal" data-title="{{ $recording->title }}"
                                data-description="{{ $recording->description }}"
                                data-url="{{ route('recordings.update', $recording->id) }}">Edit</a>

                            <a class="dropdown-item copy-link" data-link="{{ $recording->file_url }}"
                                href="javascript:void(0)" data-bs-toggle="tooltip"
                                data-link="{{ $recording->file_url }}" title="Copy link">Copy</a>
                        </div>
                    </div>
                </div>
                <p class="card-text">{{ $recording->description }}</p>
                <div class="d-flex justify-content-between">
                    <button class="btn btn-danger share" data-bs-toggle="tooltip" data-value="{{ $recording->id }}"
                        data-toggle="modal" data-target="#share-modal" data-title="{{ $recording->title }}"
                        data-text="{{ $recording->file_url }}"
                        data-short="{{ route('recordings.show', encrypt($recording->id)) }}">
                        <i class="fa fa-share"></i> Share
                    </button>
                    {{-- <button class="btn btn-info copy-iframe" data-bs-toggle="tooltip"
                        data-link="{{ $recording->file_url }}" title="Copy iframe">
                        <i class="fa fa-code" aria-hidden="true"></i> Copy Iframe
                    </button>
                    <button class="btn btn-secondary copy-link" data-bs-toggle="tooltip"
                        data-link="{{ $recording->file_url }}" title="Copy link">
                        <i class="fa fa-clone" aria-hidden="true"></i> Copy Link
                    </button> --}}
                </div>
            </div>
        </div>
    </div>
@endforeach
