@extends('layouts.app')
@section('title', 'Settings')
@section('css')
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.css" rel="stylesheet">
@endsection
@section('section')
    <div class="card shadow-none">
        <div class="card-body d-flex align-items-center justify-content-between">
            <h3 class="card-title fw-semibold">{{ $title }} ({{ $user->recordings->count() }} / 25)</h3>
            <!-- <a href="javascript:void(0)" class="btn btn-primary"> New Video </a> -->
            <div class="dropdown">
                <button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown"
                    aria-haspopup="true" aria-expanded="false">
                    New Video
                </button>
                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                    <button class="dropdown-item text-dark" href="javascrip:void(0)" type="button" class="btn btn-primary"
                        data-value="video" data-toggle="modal" data-target="#recording-modal"> <i class="fa fa-video-camera"
                            aria-hidden="true"></i> &nbsp;&nbsp; Record a Video</button>
                    <button class="dropdown-item text-dark" href="javascrip:void(0)" type="button" class="btn btn-primary"
                        data-value="screen" data-toggle="modal" data-target="#recording-modal"> <i
                            class="fa fa-video-camera" aria-hidden="true"></i> &nbsp;&nbsp; Record a Screen</button>
                </div>
            </div>
        </div>
    </div>

    {{-- videos --}}
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
                    @if ($recordings->count() > 0)
                        @foreach ($recordings as $recording)
                            <div class="col-lg-4 col-md-4 col-sm-6 mb-4 d-flex align-items-stretch">
                                <div class="card col-12">
                                    <a href="{{ route('recordings.show', encrypt($recording->id)) }}">
                                        <video playsinline class="card-img-top"
                                            poster="{{ $recording->poster_url ?? 'https://via.placeholder.com/600x400' }}"
                                            src="{{ $recording->video_url ?? 'https://via.placeholder.com/600x400' }}"
                                            type="video/mp4">
                                            <!-- Fallback image in case the video fails to load -->
                                            <img src="{{ $recording->poster_url ?? 'https://via.placeholder.com/600x400' }}"
                                                alt="{{ $recording->title }}">
                                        </video>
                                    </a>
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between">
                                            <h5 class="card-title">{{ formatTimestamp($recording->title, 'M d, Y') }}</h5>
                                            <div class="dropdown d-inline-block ml-2">
                                                <a href="javascript:void(0)" class="dropdown-toggle" role="button"
                                                    id="action-buttons" data-toggle="dropdown" aria-expanded="false"
                                                    data-bs-toggle="actions">
                                                    <i class="fas fa-ellipsis-h"></i>
                                                </a>
                                                <div class="dropdown-menu" aria-labelledby="action-buttons">
                                                    <a class="dropdown-item" href="javascript:void(0)" data-toggle="modal"
                                                        data-target="#edit-recording-modal"
                                                        data-title="{{ $recording->title }}"
                                                        data-description="{{ $recording->description }}"
                                                        data-url="{{ route('recordings.update', $recording->id) }}">Edit</a>
                                                </div>
                                            </div>
                                        </div>
                                        <p class="card-text">{{ $recording->description }}.</p>
                                        <div class="">
                                            <button class="btn btn-danger share" data-bs-toggle="tooltip"
                                                data-value="{{ $recording->id }}" title="Share">
                                                <i class="fa fa-user-plus" data-toggle="modal"
                                                    data-target="#share-modal"></i>
                                            </button>
                                            {{-- <button class="btn btn-info copy-iframe" data-bs-toggle="tooltip"
                                                data-link="{{ $recording->file_url }}" title="Copy iframe ">
                                                <i class="fa fa-code" aria-hidden="true"></i>
                                            </button>
                                            <button class="btn btn-secondary copy-link" data-bs-toggle="tooltip"
                                                data-link="{{ $recording->file_url }}" title="Copy link">
                                                <i class="fa fa-clone" aria-hidden="true"></i>
                                            </button> --}}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <h3 class="card-title fw-semibold text-center">No data found!</h3>
                    @endif
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

    <!-- New Record Modal -->
    <div class="modal fade" id="recording-modal" tabindex="-1" role="dialog" aria-labelledby="recording-modal-title"
        aria-hidden="true" data-backdrop="static">
        <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
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
                    <div class="recording" style="max-height: 60vh; max-width: 100%"></div>
                    <div class="mt-2"></div>
                    <div class="control-buttons mt-2">
                        <button type="button" role="button" class="btn btn-primary mt-4 start_recording"><i
                                class="fa fa-play"></i>
                            &nbsp;&nbsp; Start</button>

                        <button type="button" role="button" class="btn btn-secondary mt-4 pause_recording"><i
                                class="fa fa-pause"></i>
                            &nbsp;&nbsp; Pause</button>

                        <button type="button" role="button" class="btn btn-info mt-4 resume_recording"><i
                                class="fa fa-play"></i>
                            &nbsp;&nbsp; Resume </button>

                        <button type="button" role="button" class="btn btn-danger mt-4 stop_recording"><i
                                class="fa fa-stop"></i>
                            &nbsp;&nbsp; Stop </button>
                    </div>

                    <button type="button" role="button" class="btn btn-primary mt-4 restart_recording"
                        data-status="draft">Restart
                        Recording</button>
                    <button type="button" role="button" class="btn btn-primary mt-4 save_video"
                        data-status="draft">Save
                        Draft</button>
                    <button type="button" role="button" class="btn btn-primary mt-4 save_video"
                        data-status="publish">Publish
                        Video</button>
                </div>
            </div>
        </div>
    </div>

    <!-- edit Record Modal -->
    <div class="modal fade" id="edit-recording-modal" tabindex="-1" role="dialog"
        aria-labelledby="edit-recording-modal-title" aria-hidden="true" data-backdrop="static">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="title">Edit</h5>
                    <button type="button" class="btn btn-danger close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="javascript:void(0)" method="POST" id="update-recording">
                        @csrf
                        @method('PUT')
                        {{-- alert messages --}}
                        <div class="alert alert-danger alert-message d-none" role="alert"> </div>
                        {{-- update form --}}
                        <div class="row">
                            <div class="col-md-12">
                                <label for="video-select">Title</label>
                                <input type="text" class="form-control" name="title" value="" required>
                            </div>
                            <div class="col-md-12 mt-2">
                                <label for="audio-select">Description</label>
                                <textarea name="description" class="form-control" id="" cols="10" rows="3"></textarea>
                            </div>
                        </div>

                        <button type="submit" role="button" class="btn btn-primary mt-4"
                            data-status="publish">update</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- share Modal -->
    <div class="modal fade" id="share-modal" tabindex="-1" role="dialog" aria-labelledby="share-modalTitle"
        aria-hidden="true" data-backdrop="static">
        <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLongTitle">Share with contacts or tags </h5>
                    <button type="button" class="close btn btn-danger" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    {{-- tabs links --}}
                    <ul class="nav nav-tabs" id="myTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <a class="nav-link active" id="email-tab" data-bs-toggle="tab" href="#email"
                                role="tab" aria-controls="email" aria-selected="true">Email</a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" id="sms-tab" data-bs-toggle="tab" href="#sms" role="tab"
                                aria-controls="sms" aria-selected="false">SMS</a>
                        </li>
                    </ul>
                    {{-- tabs --}}
                    <div class="tab-content mt-4">
                        <div class="mt-2">
                            <div class="container">
                                <div class="row share-select mt-3 mb-3">
                                    <div class="col-sm-4 col-md-3 col-lg-3 col-xl-4">
                                        <label for="contact-select">Share with contacts</label>
                                        <input type="radio" name="share" value="contacts" checked>
                                    </div>
                                    <div class="col-sm-4 col-md-3 col-lg-3 col-xl-4">
                                        <label for="tag-select">Share with tags</label>
                                        <input type="radio" name="share" value="tags">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 contact_selector">
                                        <label for="contact-select">Select contacts:</label>
                                        <select id="contact-select" class="form-control contact-select select2">
                                            <option value="">Select contact</option>
                                            <option value="contacts">Contacts</option>
                                            <option value="contacts">Contacts</option>
                                            <option value="contacts">Contacts</option>
                                            <option value="contacts">Contacts</option>
                                            <option value="contacts">Contacts</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 tag_selector" style="display: none;">
                                        <label for="tag-select">Select tags:</label>
                                        <select id="tag-select" class="form-control tag-select">
                                            <option value="">Select tags</option>
                                            <option value="tags">Tags</option>
                                            <option value="tags">Tags</option>
                                            <option value="tags">Tags</option>
                                            <option value="tags">Tags</option>
                                            <option value="tags">Tags</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- email --}}
                        <div class="tab-pane fade show active" id="email" role="tabpanel"
                            aria-labelledby="email-tab">
                            <form action="">
                                <div class="row mt-2">
                                    <div class="col-12">
                                        <label for="video-select">Email</label>
                                        <textarea class="form-control w-100 email-summernote" name="email" rows="3"></textarea>
                                    </div>
                                </div>
                                <div class="col-12 mt-2">
                                    <button type="submit" class="btn btn-primary">Send</button>
                                </div>
                            </form>
                        </div>

                        {{-- sms tab --}}
                        <div class="tab-pane fade" id="sms" role="tabpanel" aria-labelledby="sms-tab">
                            <div class="row">
                                <form action="">
                                    <div class="row mt-2">
                                        <div class="col-12">
                                            <label for="video-select">SMS</label>
                                            <textarea class="form-control w-100 sms-summernote" name="sms" rows="3"></textarea>
                                        </div>
                                    </div>
                                    <div class="col-12 mt-2">
                                        <button type="submit" class="btn btn-primary ">Send</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
@section('js')
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.js"></script>
    <script>
        var player = null;

        $(document).ready(function() {
            var height = 250;
            loadingStart();
            $('.save_video, .restart_recording').hide();
            hideControls();

            $('.email-summernote').summernote({
                height: height,
            });

            $('.sms-summernote').summernote({
                toolbar: [],
                shortcuts: false,
                height: height
            });

            initializeSelect2('#contact-select', '.contact_selector');
            initializeSelect2('#tag-select', '.tag_selector');

            let maxLength = 100;

            let video_recorder = {
                poster: "",
                video: null,
                status: 'draft',
                posterUrl: null,
                videoUrl: null,
            };

            let video = {
                controls: true,
                controlBar: {
                    fullscreenToggle: true,
                    volumePanel: true,
                    customControlSpacer: true
                },
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
                controlBar: {
                    fullscreenToggle: true,
                    volumePanel: true,
                    customControlSpacer: true
                },
                plugins: {
                    record: {
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

            // to get user permission
            function init_perm(instance = null) {
                navigator.mediaDevices.getUserMedia({
                        video: true,
                        audio: true
                    })
                    .then(stream => {
                        hideControls();
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

                            document.querySelector('.recording').insertAdjacentHTML('afterbegin',
                                `<video id="my-video" playsinline class="video-js vjs-default-skin mt-4 w-100" style="max-height: 600px position: relative"></video>`
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
                                }, 200);

                                // initialize video js
                                player = videojs('my-video', instance, function() {
                                    console.log('videojs-record initialized!');
                                    $('.start_recording').show();
                                });

                                // hide record button
                                player.ready(function() {
                                    $('.vjs-control-bar .vjs-record-button')
                                        .hide();
                                });

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

                                // error handling
                                player.on('deviceError', function() {
                                    console.warn('device error:', player.deviceErrorCode);
                                });

                                // user clicked the record button and started recording
                                player.on('error', function(element, error) {
                                    console.error(error);
                                });

                                // user clicked the record button and started recording
                                player.on('startRecord', function() {
                                    $('.stop_recording, .pause_recording').show();
                                    $('.start_recording').hide();
                                });

                                // user completed recording and stream is available
                                player.on('finishRecord', function() {
                                    hideControls(true);
                                    $('.save_video, .restart_recording').show();
                                    $('.start_recording, .pause_recording, .resume_recording, .stop_recording')
                                        .hide();
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
                            }, 300);

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

            function hideControls(param = true) {
                var elements = $('.start_recording, .pause_recording, .resume_recording, .stop_recording');
                if (param) {
                    elements.hide();
                } else {
                    elements.show();
                }
            }

            function saveRecording(video_recorder) {
                let formData = new FormData();
                formData.append('status', video_recorder.status);
                formData.append('poster', video_recorder.poster);
                formData.append('video', video_recorder.video);
                formData.append('posterUrl', video_recorder.posterUrl);
                formData.append('videoUrl', video_recorder.videoUrl);

                let csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                fetch("{{ route('recordings.store') }}", {
                        method: "POST",
                        body: formData,
                        headers: {
                            "X-CSRF-Token": csrfToken // Include CSRF token in headers
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        console.log(data);
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: data.message,
                                showConfirmButton: false,
                                timer: 1000
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: data.message,
                            });
                        }
                        Swal.close();
                        location.reload();
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.close();
                    });
            }

            function sendFormData(file) {
                let form_Id = "HlYGceKpcoDe2MWZxjDx";
                let custom_field_file = "cu9TvjMrwWJVXjRrS1vv";

                let host = 'api.leadconnectorhq.com';
                let page_url = `https://${host}/widget/form/${form_Id}`;
                let formData = {
                    formId: form_Id,
                    location_id: "l1Rz4SuzYvlVt6ZxaFoT",
                    email: "inc_info@gmail.com",
                    sessionId: "51fbffd8-8f71-477f-8989-0ba1e5197434",
                    eventData: {
                        source: "direct",
                        referrer: "",
                        keyword: "",
                        adSource: "",
                        url_params: {},
                        page: {
                            url: page_url,
                            title: ""
                        },
                        timestamp: new Date().getTime(),
                        campaign: "",
                        contactSessionIds: null,
                        fbp: "",
                        fbc: "",
                        type: "page-visit",
                        parentId: form_Id,
                        pageVisitType: "form",
                        domain: host,
                        version: "v3",
                        parentName: "Work ScreenShots - Do not delete form",
                        fingerprint: null,
                        gaClientId: "GA1.2.1779790463.1673615426",
                        fbEventId: "b515d2f4-cb1c-4ac6-b0d0-4095e2f18a95",
                        medium: "form",
                        mediumId: form_Id
                    },
                    sessionFingerprint: "157acabf-8e5e-4a59-8b9e-cf00e9515455"
                };
                var form = new FormData();
                form.append(custom_field_file, file, "map_image.png");
                form.append("formData", JSON.stringify(formData));
                var request = new XMLHttpRequest();
                request.open(
                    "POST",
                    "https://services.leadconnectorhq.com/forms/submit",
                    true
                );
                return new Promise((resolve, reject) => {
                    request.onreadystatechange = function() {
                        if (this.readyState == 4 && [200, 201].includes(this.status)) {
                            let data = request.responseText;
                            try {
                                data = JSON.parse(data);
                            } catch (error) {}
                            data.field_id = custom_field_file;
                            resolve(data);
                        }
                    };
                    request.send(form);
                });
            }

            function copyToClipboard(text) {
                const el = document.createElement('textarea');
                el.value = text;
                document.body.appendChild(el);
                el.select();
                document.execCommand('copy');
                document.body.removeChild(el);
                showSwal();
            }

            function showSwal() {
                Swal.fire({
                    title: 'Copied',
                    text: 'Copied to the clipboard.',
                    icon: 'success',
                    timer: 1000,
                    showConfirmButton: false
                });
            }

            function getContacts() {
                $.ajax({
                    type: "GET",
                    url: "{{ route('ghl.contacts') }}",
                    data: {
                        '_token': "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        if (response.success == true) {
                            $('.contact-select').empty();
                            let html = '<option value="">Select contact</option>';
                            response.data.contacts.forEach(element => {
                                html +=
                                    `<option value="${element.id}">${element.firstName +' '+ element.lastName }</option>`;
                            });
                            $('.contact-select').html(
                                html); // Uncomment this line to append options to the select element
                        }
                    }
                });
            }

            function getTags() {
                $.ajax({
                    type: "GET",
                    url: "{{ route('ghl.tags') }}",
                    data: {
                        '_token': "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        if (response.success == true) {
                            console.log(response.data);
                            $('.tags-select').empty();;
                            let html = '<option value="">Select tags</option>';
                            response.data.contacts.forEach(element => {
                                html +=
                                    `<option value="${element.id}">${element.firstName +' '+ element.lastName}</option>`;
                            });
                            $('.tags-select').html(
                                html); // Uncomment this line to append options to the select element
                        }
                    }
                });
            }

            function toogleOptions(selector, value) {
                if (value == 'contacts') {
                    $(selector).find('.contact_selector').show();
                    $(selector).find('.tag_selector').hide();
                } else {
                    $(selector).find('.contact_selector').hide();
                    $(selector).find('.tag_selector').show();
                }
            }

            function initializeSelect2(select2class, formid) {
                $(select2class).select2({
                    dropdownParent: $(formid), // modal : id modal
                    placeholder: "Select",
                    allowClear: true,
                    width: "100%",
                    height: "40px",
                    multiple: true,
                    ajax({
                        type: "method",
                        url: "url",
                        data: "data",
                        dataType: "dataType",
                        success: function(response) {

                        }
                    });

                });
            }

            // Attach change event listener to radio buttons
            $('input[name="share"]').change(function() {
                // Call toggleOptions with the value of the checked radio button
                toogleOptions('body', $(this).val());
            });

            $('body').on('click', '[data-target="#recording-modal"]', function(e) {
                e.preventDefault();
                let type = $(this).data('value') ?? '';
                init_perm(type);
            });

            $('body').on('click', '[data-target="#edit-recording-modal"]', function(e) {
                e.preventDefault();
                let title = $(this).data('title');
                let description = $(this).data('description');

                $('#title').html(title);
                $('#edit-recording-modal input[name="title"]').val(title);
                $('#edit-recording-modal textarea[name="description"]').val(description);
                $('#edit-recording-modal form').attr('action', $(this).data('url'));
            });

            $('body').on('submit', '#update-recording', function(e) {
                e.preventDefault();
                // alert('update');
                loadingStart('Updating...');
                let formData = new FormData();
                formData.append('title', $('#edit-recording-modal input[name="title"]').val());
                formData.append('description', $('#edit-recording-modal textarea[name="description"]')
                    .val());
                formData.append('_token', "{{ csrf_token() }}");
                formData.append('_method', 'PATCH');
                $.ajax({
                    type: "POST",
                    url: $(this).attr('action'),
                    data: formData,
                    // dataType: "dataType",
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        loadingStop();
                        if (response.success == true) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: response.message,
                                showConfirmButton: false,
                                timer: 1000
                            });
                            location.reload();
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: response.message,
                            });
                        }
                    },
                    error: function(error) {
                        loadingStop();
                        let errorMessage = error.responseJSON.message;
                        $('.alert-message').html(errorMessage).show();
                    }
                });
            });

            $('body').on('click', '[data-target="#share-modal"]', function(e) {
                e.preventDefault();
                toogleOptions('body', $('input[name="share"]:checked').val());
                getContacts();
                getTags();
            });

            $('.share').click(function(e) {
                e.preventDefault();
                let id = $(this).data('value');
                $('.share_id').val(id);
            });

            $('#video-select').change(function(e) {
                e.preventDefault();
                player.record().setVideoInput($(this).val() ?? '');
            });

            $('#audio-select').change(function(e) {
                e.preventDefault();
                player.record().setAudioInput($(this).val() ?? '');
            });

            $('.start_recording').click(function(e) {
                e.preventDefault();
                hideControls(true);
                applyVideoWorkaround();
                $('#video-select, #audio-select').prop('disabled',
                    'disabled'); // Disable the video select dropdown
                player.record().start();
            });

            $('.pause_recording').click(function(e) {
                e.preventDefault();
                hideControls(true);
                $('.stop_recording, .resume_recording').show();
                player.record().pause();
            });

            $('.resume_recording').click(function(e) {
                e.preventDefault();
                hideControls(true);
                $('.stop_recording, .pause_recording').show();
                player.record().resume();
            });

            $('.stop_recording').click(function(e) {
                e.preventDefault();
                hideControls(true);
                $('.start_recording').show();
                player.record().stop();
            });

            $('.restart_recording').click(function(e) {
                e.preventDefault();
                hideControls(true);
                $('.start_recording').show();
                $('.save_video, .restart_recording').hide();
                player.record().start();
            });

            $('.copy-link').click(function(e) {
                e.preventDefault();
                let link = $(this).data('link');
                copyToClipboard(link);
            });

            $('.copy-iframe').click(function(e) {
                e.preventDefault();
                let link = $(this).data('link');
                text =
                    `<iframe src="${link}" width="100%" height="100%" frameborder="0" allowfullscreen></iframe>`;
                copyToClipboard(text);
            });

            $('.save_video').click(async function(e) {
                e.preventDefault();
                loadingStart('Saving Recording...');
                // $(this).attr('disabled', 'disabled');

                let status = $(this).data('status');
                video_recorder.status = status;

                const fetchFormData = async (formData) => {
                    const data = await sendFormData(formData);
                    if (data?.formData && data?.field_id) {
                        const field = data.formData[data.field_id];
                        const values = Object.values(field);
                        if (values.length > 0) {
                            console.log(values[0].url);
                            return values[0].url;
                        }
                    }
                    return null;
                };

                video_recorder.posterUrl = await fetchFormData(video_recorder.poster);
                video_recorder.videoUrl = await fetchFormData(video_recorder.video);

                // $(this).attr('disabled', '');
                // console.log(video_recorder);
                saveRecording(video_recorder);

                loadingStop();
            });

            loadingStop();

        });
    </script>
@endsection
