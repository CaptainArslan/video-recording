@extends('layouts.app')
@section('title', 'Settings')
@section('section')
    <div class="card shadow-none">
        <div class="card-body d-flex align-items-center justify-content-between">
            <h3 class="card-title fw-semibold">{{ $title }} (10 / 25) </h3>
            <!-- <a href="#" class="btn btn-primary"> New Video </a> -->
            <div class="dropdown">
                <button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown"
                    aria-haspopup="true" aria-expanded="false">
                    New Video
                </button>
                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                    <button class="dropdown-item text-dark" href="#" type="button" class="btn btn-primary"
                        data-value="video" data-toggle="modal" data-target="#recording-modal"> <i class="fa fa-video-camera"
                            aria-hidden="true"></i> &nbsp;&nbsp; Record a Video</button>
                    <button class="dropdown-item text-dark" href="#" type="button" class="btn btn-primary"
                        data-value="screen" data-toggle="modal" data-target="#recording-modal"> <i
                            class="fa fa-video-camera" aria-hidden="true"></i> &nbsp;&nbsp; Record a Screen</button>
                </div>
            </div>
        </div>
    </div>
    <div class="container-fluid">
        <ul class="nav nav-tabs " id="myTab" role="tablist">
            <li class="nav-item">
                <a class="nav-link" id="videos-tab" data-toggle="tab" href="#videos" role="tab" aria-controls="videos"
                    aria-selected="false">Videos</a>
            </li>
            {{--
        <li class="nav-item">
            <a class="nav-link" id="archieve-tab" data-toggle="tab" href="#archieve" role="tab" aria-controls="archieve" aria-selected="false">Archieve</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="screenshots-tab" data-toggle="tab" href="#screenshots" role="tab" aria-controls="screenshots" aria-selected="false">Screenshots</a>
        </li>
        --}}
        </ul>
        <div class="tab-content mt-4" id="nav-tabContent">
            <div class="tab-pane fade show active" id="videos" role="tabpanel" aria-labelledby="videos-tab">
                <h2 class="font-weight-bold">Videos</h2>
                <div class="row mt-4">
                    @foreach ($recordings as $recording)
                        <div class="col-lg-3 col-md-4 col-sm-6 mb-4 d-flex align-items-stretch">
                            <div class="card">
                                <video loop muted playsinline poster="https://via.placeholder.com/600x400"
                                    class="card-img-top">
                                    <source src="https://picsum.photos/600/400" type="video/mp4">
                                    <!-- Fallback image in case the video fails to load -->
                                    <img src="https://via.placeholder.com/600x400" alt="Fallback Image">
                                </video>
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <h5 class="card-title">{{ $recording->title }}</h5>
                                        <div class="dropdown d-inline-block ml-2">
                                            <a href="#" class="dropdown-toggle" role="button" id="action-buttons"
                                                data-toggle="dropdown" aria-expanded="false" data-bs-toggle="actions">
                                                <i class="fas fa-ellipsis-h"></i>
                                            </a>
                                            <div class="dropdown-menu" aria-labelledby="action-buttons">
                                                <a class="dropdown-item" href="#">Edit</a>
                                            </div>
                                        </div>
                                    </div>
                                    <p class="card-text">{{ $recording->description }}.</p>
                                    {{-- <div class="">
                                <button class="btn btn-primary" data-bs-toggle="tooltip" data-url="{{ $recording->file }}" title="Share"><i class="fa fa-user-plus" aria-hidden="true"></i></button>
                            <button class="btn btn-secondary" data-bs-toggle="tooltip" data-url="{{ $recording->file }}" title="Copy link"><i class="fa fa-clone" aria-hidden="true"></i></button>
                        </div> --}}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="paginator mb-5 d-flex justify-content-end">
                    {{ $recordings->links() }}
                </div>

            </div>
            {{--
        <div class="tab-pane fade" id="archieve" role="tabpanel" aria-labelledby="archieve-tab">
            <h2 class="font-weight-bold">Archieve</h2>
        </div>
        <div class="tab-pane fade" id="screenshots" role="tabpanel" aria-labelledby="screenshots-tab">
            <h2 class="font-weight-bold">Screenshots</h2>
        </div>
        --}}
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="recording-modal" tabindex="-1" role="dialog" aria-labelledby="recording-modal-title"
        aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLongTitle">Recorder</h5>
                    <button type="button" class="btn btn-danger close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 video_selector" hidden>
                            <label for="video-select">Select Video Device:</label>
                            <select id="video-select" class="form-control">
                                <option value="">Select Video device</option>
                            </select>
                        </div>
                        <div class="col-md-6 audio_selector" hidden>
                            <label for="audio-select">Select Audio Device:</label>
                            <select id="audio-select" class="form-control">
                                <option value="">Select audio device</option>
                            </select>
                        </div>
                    </div>

                    <!-- My Video -->
                    <div class="video_preview" style="max-height: 80vh; max-width: 100%; overflow: scroll"></div>

                    <button type="button" class="btn btn-primary mt-4 save_video" data-status="draft">Save
                        Draft</button>
                    <button type="button" class="btn btn-primary mt-4 save_video" data-status="publish">Publish
                        Video</button>

                    <div class="control-buttons">
                        <button type="button" class="btn btn-danger mt-4 pause_video"><i class="fa fa-pause"></i>
                            &nbsp;&nbsp; Pause</button>
                        <button type="button" class="btn btn-info mt-4 resume_video"><i class="fa fa-play"></i>
                            &nbsp;&nbsp; Resume </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('js')

    <script>
        var player = null;
        $(document).ready(function() {

            $('.save_video').hide();
            $('.pause_video').hide();
            $('.resume_video').hide();

            let maxLength = 500;
            let video_recorder = {
                poster: "",
                video: null,
                status: 'draft'
            };

            let video = {
                controls: true,
                plugins: {
                    record: {
                        audio: true,
                        video: true,
                        maxLength: maxLength,
                        displayMilliseconds: false,
                        // debug: true,
                        muted: false
                    }
                }
            };

            let screen_only = {
                controls: true,
                plugins: {
                    record: {
                        //video: true,
                        audio: true,
                        screen: true,
                        recordScreen: true,
                        maxLength: maxLength,
                        displayMilliseconds: false,
                        // debug: true,
                        muted: false
                    }
                }
            };
            // allow="camera *;microphone *"
            // user devices permissions
            function init_perm(instance = null) {
                $('.save_video').hide();
                $('.pause_video').hide();
                $('.resume_video').hide();

                navigator.mediaDevices.getUserMedia({
                        video: true,
                        audio: true
                    })
                    .then(stream => {
                        getUserMediaDictionary(instance);
                    })
                    .catch(err => {
                        console.error('Error accessing user media:', err);
                    });
            }

            function captureFirstFrame(videoElement) {
                const canvas = document.createElement('canvas');
                const ctx = canvas.getContext('2d');
                const firstFrameWidth = videoElement.videoWidth;
                const firstFrameHeight = videoElement.videoHeight;
                canvas.width = firstFrameWidth;
                canvas.height = firstFrameHeight;
                ctx.drawImage(videoElement, 0, 0, firstFrameWidth, firstFrameHeight);
                return new Promise((resolve, reject) => {
                    canvas.toBlob(blob => {
                        const a = document.createElement('a');
                        const url = URL.createObjectURL(blob);
                        URL.revokeObjectURL(url);
                        resolve(blob);
                    }, 'image/png');
                });
            }

            function getUserMediaDictionary(instance = null) {
                try {
                    navigator.mediaDevices.enumerateDevices()
                        .then(function(devices) {
                            // console.log(devices);
                            devices.forEach(function(device) {
                                if (device.kind === 'videoinput') {
                                    addDeviceToSelect(device, 'video_selector');
                                } else if (device.kind === 'audioinput') {
                                    addDeviceToSelect(device, 'audio_selector');
                                }
                            });

                            if (player) {
                                player.dispose();
                            }

                            document.querySelector('.video_preview').insertAdjacentHTML('afterbegin',
                                `<video id="my-video" playsinline class="video-js vjs-default-skin mt-4 w-100"></video>`
                            );

                            setTimeout(function() {
                                if (instance == null || instance == undefined || instance == '') {
                                    instance = options;
                                }

                                if (instance == 'screen') {
                                    instance = screen_only;
                                    $('.video_selector').attr('hidden', true);
                                }
                                let prev = instance;
                                if (instance == 'video') {
                                    instance = video;
                                    $('.video_selector').removeAttr('hidden');
                                }

                                setTimeout(function() {
                                    function init_top(selector) {
                                        let parent = document.querySelector(
                                            `.${selector} select`);
                                        if (parent && parent.value == '') {
                                            let audio = parent.querySelector(
                                                ' option:nth-child(2)');
                                            if (audio) {
                                                audio.selected = true;
                                                $(audio).trigger('change');
                                                audio.dispatchEvent(new Event('change'));
                                            }
                                        }
                                    }

                                    if (prev == 'video') {
                                        init_top('video_selector');
                                    }
                                    init_top('audio_selector');
                                }, 1000);

                                // initialize video js
                                player = videojs('my-video', instance, function() {});

                                var videoDeviceId = $('#video-select').val();
                                var audioDeviceId = $('#audio-select').val();

                                if (prev == 'video') {
                                    if (audioDeviceId != '') {
                                        player.record().setAudioInput(audioDeviceId);
                                    }

                                    if (videoDeviceId != '') {
                                        player.record().setVideoInput(videoDeviceId);
                                    }
                                }

                                player.on('deviceError', function() {
                                    console.warn('device error:', player.deviceErrorCode);
                                });

                                player.on('error', function(element, error) {
                                    console.error(error);
                                });

                                // user clicked the record button and started recording
                                player.on('startRecord', function() {
                                    $('.pause_video').show(); // Show pause button
                                    $('.resume_video').hide(); // Hide start button
                                });

                                player.on('finishRecord', function() {
                                    $('.save_video').show();
                                    $('.pause_video').hide();
                                    $('.resume_video').hide();
                                    captureFirstFrame(document.querySelector(
                                        '#my-video #my-video_html5_api')).then(t => {
                                        video_recorder.poster = t;
                                    });
                                    video_recorder.video = player.recordedData;
                                });

                                if (!player.record) {
                                    console.error('Recording plugin is not available.');
                                    return false;
                                }
                            }, 1000);

                        })
                        .catch(function(err) {
                            console.log('Error enumerating devices: ' + err);
                        });

                } catch (error) {
                    console.log('Error enumerating devices: ' + error);
                }
            }

            function addDeviceToSelect(device, selectId) {
                var parent = document.querySelector('.' + selectId);
                var select = parent.querySelector('select');
                var option = document.createElement('option');
                option.value = device.deviceId;

                let already = select.querySelector('option[value="' + device.deviceId + '"]');
                if (!already) {
                    option.text = device.label || 'Device ' + (select.options.length + 1);
                    select.add(option);
                }
                if (select.options.length > 1) {
                    parent.removeAttribute('hidden');
                }
            }

            loadingStart();

            $('body').on('click', '[data-target="#recording-modal"]', function(e) {
                e.preventDefault();
                let type = $(this).data('value') ?? '';
                init_perm(type);
            });

            // $('#recording-modal').on('hidden.bs.modal', function(e) {
            //     // alert('The modal is now hidden.');
            //     setTimeout(function() {
            //         if (player) {
            //             player.dispose();
            //         }
            //     }, 1000);
            // })

            $('#start-recording').click(function(e) {
                e.preventDefault();
                // apply some workarounds for certain browsers
                applyVideoWorkaround();
                player.record().start();
            });


            $('#video-select').change(function(e) {
                e.preventDefault();
                player.record().setVideoInput($(this).val() ?? '');
            });

            $('#audio-select').change(function(e) {
                e.preventDefault();
                player.record().setAudioInput($(this).val() ?? '');
            });

            $('.pause_video').click(function(e) {
                e.preventDefault();
                $('.pause_video').hide();
                $('.resume_video').show();
                player.record().pause();
            });

            $('.resume_video').click(function(e) {
                e.preventDefault();
                $('.resume_video').hide();
                $('.pause_video').show();
                player.record().resume();
            });

            $('.save_video').click(function(e) {

                startLoading();
                e.preventDefault();
                let status = $(this).data('status');
                video_recorder.status = status;

                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': "{{ csrf_token() }}"
                    }
                });

                $.ajax({
                    type: "POST",
                    url: "{{ route('recordings.store') }}",
                    data: json.stringify(video_recorder),
                    success: function(response) {
                        console.log(response);
                    },
                    error: function(error) {
                        console.log(error);
                        loadingStop();
                    }
                });

            });

            $('.save_video').hide();
            $('.pause_video').hide();
            $('.resume_video').hide();
            loadingStop();
        });
    </script>
@endsection
