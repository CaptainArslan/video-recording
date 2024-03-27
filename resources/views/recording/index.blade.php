@extends('layouts.app')
@section('title', 'Recordings')
@section('css').
    {{-- Video Js --}}
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
    <style>
        [aria-label="Video"],
        [aria-label="Help"] {
            display: none !important;
        }

        .iframe #main-wrapper[data-layout=vertical][data-sidebartype=full] .body-wrapper {
            margin-left: 0px !important;
        }

        .iframe .body-wrapper>.container-fluid {
            max-width: 100% !important;
        }

        .iframe #main-wrapper[data-layout=vertical][data-header-position=fixed] .body-wrapper>.container-fluid {
            padding-top: 0px !important;
        }

        .ifrmae .dropdown-toggle::after {
            display: none !important;
        }

        .custom_vd_btn::before {
            font-size: 2em;
            line-height: 1.4em;
        }

        .main_recorder {
            display: grid;
            grid-template-columns: 70% 30%;
            grid-column-gap: 10px;
        }

        .main_recorder.full {
            grid-template-columns: 100%;
        }

        #my-video {
            min-height: 400px !important;
        }

        .save_recording_btn button,
        .control-buttons button {
            margin: 2px;
        }

        div#my-video-face :not(video) {
            display: none;
        }

        div#my-video-face video,
        div#my-video-face {
            width: 200px !important;
            height: 200px !important;
            position: absolute;
            z-index: 9999999;
            right: 2%;
            bottom: 6%;
        }

        canvas#audioCanvas {
            height: 30px;
        }

        div#my-video-face.hide {
            display: none;
        }
    </style>
@endsection
@section('section')
    <div class="card shadow-none">
        <div class="card-body d-flex align-items-center justify-content-between">

            @php
                $limit = $user->plan->limit ?? 0;
                $recordMore = true;
                $recCount = $user->recordings->count();
                if ($limit == 0) {
                    $limit = 'Unlimited';
                } elseif ($recCount >= $limit) {
                    $recordMore = false;
                }

            @endphp
            <h3 class="card-title fw-semibold">{{ $title }} (<span id="user_rec">{{ $recCount }}</span> / <span
                    id="user_limit">{{ $limit }}</span>)
            </h3>
            <!-- <a href="javascript:void(0)" class="btn btn-primary"> New Video </a> -->
            <div class="dropdown">
                @if ($recordMore)
                    <button class="btn btn-primary " type="button" id="upload" data-toggle="modal"
                        data-target="#upload-video-modal">
                        Upload Video
                    </button>
                    <button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenuButton"
                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        New Video
                    </button>

                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                        <button class="dropdown-item text-dark" href="javascrip:void(0)" type="button" data-value="video"
                            data-toggle="modal" data-target="#recording-modal">
                            <i class="fa fa-video-camera" aria-hidden="true"></i> &nbsp;&nbsp; Record a Video</button>

                        <button class="dropdown-item share_recording text-dark" href="javascrip:void(0)" type="button"
                            data-value="screen" data-toggle="modal" data-target="#recording-modal">
                            <i class="fa fa-video-camera" aria-hidden="true"></i> &nbsp;&nbsp; Record Screen</button>

                        <a class="share_outside_recording dropdown-item text-dark" target="_blank"
                            href="{{ route('recordings.index') }}?action=share"><i class="fa fa-video-camera"
                                aria-hidden="true"></i> &nbsp;&nbsp; Record a Screen
                        </a>

                    </div>
                @endif
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
            <li class="nav-item">
                <a class="nav-link" id="history-tab" data-toggle="tab" href="#history" role="tab"
                    aria-controls="history" aria-selected="false">History</a>
            </li>
        </ul>
        <div class="tab-content mt-4" id="nav-tabContent">
            <div class="tab-pane fade show active" id="videos" role="tabpanel" aria-labelledby="videos-tab">
                <h2 class="font-weight-bold">Videos</h2>
                <div class=" mt-4">
                    <div id="recordings-container" class="row"> </div>
                    <div id="pagination-container" class="row"> </div>
                </div>
            </div>
            <div class="tab-pane fade" id="history" role="tabpanel" aria-labelledby="history-tab">
                @include('recording.history')
            </div>
            {{-- <div class="tab-pane fade" id="screenshots" role="tabpanel" aria-labelledby="screenshots-tab">
                <h2 class="font-weight-bold">Screenshots</h2>
            </div> --}}
        </div>
    </div>

    <!-- Upload Video Modal -->
    @include('recording.modal.upload')
    <!-- New Record Modal -->
    @include('recording.modal.create')
    <!-- edit Record Modal -->
    @include('recording.modal.edit')
    <!-- share Modal -->
    @include('recording.modal.share')
@endsection
@section('js')
    {{-- <script src="{{ asset('js/summernote.js') }}"></script> --}}
    {{-- <script src="https://cdn.jsdelivr.net/npm/video-stream-merger@4.0.1/dist/video-stream-merger.min.js"></script> --}}




    <script src="https://www.webrtc-experiment.com/RecordRTC.js"></script>
    <script src="https://www.webrtc-experiment.com/MultiStreamsMixer.js"></script>
    <script src="https://www.webrtc-experiment.com/common.js"></script>
    <script src="https://www.webrtc-experiment.com/EBML.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>

    @include('partials.datatable-js')
    @include('recording.summernote')

    <script src="{{ asset('js/video-stream-merger.js') }}"></script>

    {{-- video js options --}}
    <script src="{{ asset('js/video-js.js') }}"></script>

    {{-- Gif Js --}}
    {{-- <script src="https://cdn.jsdelivr.net/npm/gif.js@0.2.0/dist/gif.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/gif.js@0.2.0/dist/gif.worker.js"></script> --}}


    <script src="https://cdn.jsdelivr.net/npm/@ffmpeg/ffmpeg/dist/ffmpeg.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@ffmpeg/ffmpeg/dist/ffmpeg.wasm.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/gif.js/dist/gif.js"></script>


    {{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/gif.js/0.2.0/gif.js"
        integrity="sha512-nNOFtIS+H0lwgdUDaPn/g1ssw3uN9AkEM7zy2wLaTQeLQNeNiitUcLpEpDIh3Z4T22MdeTNru/SQbNM4ig2rng=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/gif.js/0.2.0/gif.worker.js"
        integrity="sha512-3piO8GKVGn3D+eEWnTquDnlxM00ESMZpYNAnjmOMswHrGihZvdlsRjSW1bHLqahzIoyL9YWlLWVYRV4J8AHwtg=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script> --}}


    <script>
        var player = null;
        let player_face = null;
        let is_company = `{{ is_company() }}` == '0';
        var pipEnabled = false;
        var pipStatusMsg;
        let maxLength = "{{ $user->plan->recording_minutes_limit }}" * 60;
        let recordWithFace = true;
        let finalRecording = null;
        let recorder = null;
        let audioRecord = null;
        let currentInstance = false;
        let localUpload = true;
        let uploadBtn = $('.upload_btn');
        var durationMinutes = 0;
        var allowSaveBtn = false;
        var limit = `{{ $limit }}`;
        var recCount = `{{ $recCount }}`;

        let blobs = {
            screen: null,
            face: null
        };

        var video_recorder = {
            poster: "",
            posterUrl: null,
            video: "",
            videoUrl: null,
            face: "",
            faceUrl: null,
            status: 'draft'
        };

        if (window.self == window.parent && is_company) {
            document.querySelector('body').classList.remove('iframe');
        }

        $(document).ready(function() {

            if (window.parent != window.self) {
                $('.share_recording').hide();
            } else {
                $('.share_outside_recording').hide();
            }

            setTimeout(function() {
                if (location.href.includes('action=share')) {
                    $('.share_recording').trigger('click');
                }
            }, 2000);

            $.ajaxSetup({
                headers: {
                    'token-id': localStorage.getItem('token-id'),
                }
            });

            $('.save_video, .restart_recording, .save_recording_btn').hide();
            hideControls();

            let videoRtc = {
                width: 1920,
                height: 1080
            };

            // let videoRtc = {
            //     width: 1080,
            //     height: 720
            // };

            let video_setting_rtc = {
                width: {
                    // min: 640,
                    // ideal: 640,
                    max: 1280
                },
                height: {
                    // min: 480,
                    // ideal: 480,
                    max: 720
                },
            };

            let audio_rtc = {
                echoCancellation: true
            };

            //userinactive
            let video = {
                controls: true,
                // autoMuteDevice: true,
                controlBar: {
                    fullscreenToggle: false,
                    volumePanel: false,
                    customControlSpacer: true
                },
                // aspectRatio: '16:9',
                plugins: {
                    record: {
                        // pip: pipEnabled,
                        audio: audio_rtc,
                        video: video_setting_rtc,
                        maxLength: maxLength,
                        displayMilliseconds: false,
                        frameWidth: videoRtc.width,
                        frameHeight: videoRtc.height,
                        muted: false
                        // debug: true
                    }
                }
            };

            let video_screen = {
                controls: false,
                controlBar: {
                    fullscreenToggle: false,
                    volumePanel: false,
                    customControlSpacer: false
                },
                plugins: {
                    record: {
                        // pip: pipEnabled,
                        audio: false,
                        video: video_setting_rtc,
                        maxLength: maxLength,
                        displayMilliseconds: false,
                        frameRate: 30,
                        mimeType: 'video/webm;codecs=h264', // Set the desired video codec and MIME type
                        videoBitsPerSecond: 10 * 1024 * 1024
                        // debug: true
                    }
                }
            };

            let screen_only = {
                controls: true,
                controlBar: {
                    fullscreenToggle: false,
                    // volumePanel: true,
                    // customControlSpacer: true
                },
                plugins: {
                    record: {
                        audio: audio_rtc,
                        screen: true,
                        recordScreen: true,
                        maxLength: maxLength,
                        displayMilliseconds: false,
                        muted: false,
                        frameRate: 30,
                        mimeType: 'video/webm;codecs=h264', // Set the desired video codec and MIME type
                        videoBitsPerSecond: 10 * 1024 * 1024
                        // debug: true
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
                        if (instance == 'screen') {
                            hideControls();
                            getUserMediaDictionary(instance);

                        } else {
                            setTimeout(function() {
                                $('#recording-modal .close-modal').trigger('click');
                            }, 500);
                            show_error('no video device available ');
                        }
                    });
            }

            function captureFirstFrame(videoElement) {
                return new Promise((resolve, reject) => {
                    const canvas = document.createElement('canvas');
                    const ctx = canvas.getContext('2d');
                    const firstFrameWidth = videoElement.videoWidth;
                    const firstFrameHeight = videoElement.videoHeight;
                    // Set canvas dimensions to match video
                    canvas.width = firstFrameWidth;
                    canvas.height = firstFrameHeight;
                    // Draw the first frame onto the canvas
                    ctx.drawImage(videoElement, 0, 0, firstFrameWidth, firstFrameHeight);
                    // Convert the canvas content to a Blob
                    canvas.toBlob(blob => {
                        if (!blob) {
                            reject(new Error('Failed to capture frame as Blob'));
                            return;
                        }
                        resolve(blob);
                    }, 'image/png');
                });
            }

            function uploadPoster(poster) {
                if (poster) {
                    return new Promise((resolve, reject) => {
                        let formData = new FormData();
                        formData.append('poster', poster);
                        formData.append('_token', "{{ csrf_token() }}");

                        $.ajax({
                            type: "POST",
                            url: "{{ route('upload.poster') }}",
                            data: formData,
                            processData: false,
                            contentType: false,
                            success: function(response) {
                                if (response.success == true) {
                                    resolve(response.data);
                                } else {
                                    reject(null);
                                }
                            },
                            error: function(error) {
                                reject(null);
                            }
                        });
                    });
                } else {
                    console.error('No poster to upload', poster);
                    return null;
                }
            }

            async function uploadVideoChunks(videoBlob, callback) {
                const chunkSize = 1024 * 1024;
                let offset = 0;
                let lastChunkIndex = Math.ceil(videoBlob.size / chunkSize);
                let err = [];

                // Generate a random folder name
                const randomFolder = Math.random().toString(36).substring(2);

                while (offset <= videoBlob.size) {
                    let offsetSize = offset + chunkSize;
                    let chunk = videoBlob.slice(offset, offsetSize);

                    let formData = new FormData();

                    formData.append('videoChunk', chunk);
                    formData.append('chunkIndex', Math.ceil(offset / chunkSize) + 1); // Calculate chunk index
                    formData.append('lastChunkIndex', lastChunkIndex); // Send last chunk index
                    formData.append('randomFolder', randomFolder); // Send random folder name
                    formData.append('_token', "{{ csrf_token() }}"); // Send csrf token

                    // Send chunk to server
                    let response = await fetch("/upload-chunks", {
                        method: 'POST',
                        body: formData
                    });
                    // Handle response as needed
                    if (offsetSize >= videoBlob.size) {
                        let responseData = await response.json();
                        callback(responseData);
                    }
                    offset += chunkSize;
                }
            }

            const gifOptions = {
                workers: 4,
                quality: 10,
                // width: 320,
                // height: 240,
                fps: 15,
                duration: 5 // 5 seconds
            };

            function createGifFromVideo(videoElement, options = {}) {
                return new Promise(async (resolve, reject) => {
                    const ffmpeg = createFFmpeg({
                        log: true
                    });
                    await ffmpeg.load();

                    const fps = options.fps || 10; // Frames per second
                    const durationInSeconds = options.duration || 5; // Duration in seconds
                    const totalFrames = durationInSeconds * fps;
                    const frameDuration = 1 / fps;

                    const gif = new GIF({
                        quality: options.quality || 10,
                        width: options.width || videoElement.videoWidth,
                        height: options.height || videoElement.videoHeight
                    });

                    let currentFrame = 0;

                    const captureFrame = async () => {
                        const canvas = document.createElement('canvas');
                        const ctx = canvas.getContext('2d');
                        canvas.width = videoElement.videoWidth;
                        canvas.height = videoElement.videoHeight;
                        ctx.drawImage(videoElement, 0, 0, canvas.width, canvas.height);

                        gif.addFrame(canvas, {
                            delay: frameDuration * 1000
                        });

                        currentFrame++;
                        if (currentFrame < totalFrames) {
                            videoElement.currentTime += frameDuration;
                            requestAnimationFrame(captureFrame);
                        } else {
                            const dataUrl = await new Promise(resolve => gif.on('finished',
                                resolve));
                            const dataBlob = await fetch(dataUrl).then(res => res.blob());

                            // Keep adjusting quality until the file size is within the desired range
                            let gifSizeKB = Math.round(dataBlob.size / 1024);
                            while (gifSizeKB > 20 && gifSizeKB > 10) {
                                const tempGif = new GIF({
                                    quality: gif.quality - 1, // Decrease quality
                                    width: options.width || videoElement.videoWidth,
                                    height: options.height || videoElement.videoHeight
                                });
                                tempGif.addFrame(canvas, {
                                    delay: frameDuration * 1000
                                });
                                const tempDataUrl = await new Promise(resolve => tempGif.on(
                                    'finished', resolve));
                                const tempDataBlob = await fetch(tempDataUrl).then(res => res
                                    .blob());
                                gifSizeKB = Math.round(tempDataBlob.size / 1024);
                                if (gifSizeKB <= 20 || gifSizeKB <= 10) {
                                    resolve(tempDataBlob);
                                } else {
                                    gif.quality--; // Decrease quality further
                                }
                            }
                        }
                    };

                    videoElement.currentTime = 0; // Start at the beginning
                    captureFrame();
                });
            }


            function recordUserFace() {
                delete blobs.camera;
                if (recorder) {
                    recorder = null;
                }
                if (recordWithFace) {

                    try {
                        if (player_face) {
                            player_face.dispose();
                            player_face = null;
                        }
                    } catch (error) {
                        console.error(error);
                    }

                    $('.self_checkbox').after(
                        `<video id="my-video-face" hidden src="" playsinline class="video-js hide vjs-default-skin mt-4 h-100 w-100"></video>`
                    );

                    video_screen = applyAudioWorkaround(video_screen);
                    video_screen = applyVideoWorkaround(video_screen);

                    setTimeout(function() {
                        player_face = videojs('my-video-face', video_screen,
                            function() {});
                        player_face.ready(function() {
                            setTimeout(function() {
                                var videoDeviceId = $('#video-select').val();
                                if (videoDeviceId != '') {
                                    setSrc(videoDeviceId, 1);
                                }
                                var audio = $('#audio-select').val();
                                if (audio != '') {
                                    setSrc(audio, 2);
                                }
                                $('.vjs-icon-video-perm')
                                    .trigger('click');
                                let videoface = document.querySelector('#my-video-face');
                                videoface.setAttribute(
                                    'class', '');
                                videoface.removeAttribute('hidden');
                                videoface.querySelector('video').removeAttribute('hidden');
                            }, 500);

                        });
                        // error handling
                        player_face.on('deviceError', function() {});

                        // user clicked the record button and started recording
                        player_face.on('error', function(element, error) {});

                        player_face.on('startRecord', function(element, error) {
                            blobs.camera = player_face.record().stream;

                            setTimeout(function() {
                                let screen = blobs.screen;
                                let camera = blobs.camera;

                                // Setting up screen dimensions
                                screen.width = videoRtc.width;
                                screen.height = videoRtc.height;
                                screen.fullcanvas = true;

                                // Adjusting camera dimensions and position
                                camera.width = 320;
                                camera.height = 240;
                                camera.top = screen.height - camera.height;
                                camera.left = screen.width - camera.width;

                                let allStreams = [screen, camera];

                                recorder = RecordRTC(allStreams, {
                                    type: "video",
                                    mimeType: "video/webm",
                                    canvas: {
                                        width: videoRtc
                                            .width, // Set the canvas width to match the video width
                                        height: videoRtc
                                            .height // Set the canvas height to match the video height
                                    },
                                    previewStream: function(s) {
                                        var final_video = document
                                            .querySelector('#my_final_video');
                                        if (final_video) {
                                            final_video.muted = true;
                                            final_video.srcObject = s;
                                        }
                                    },
                                });

                                // Start recording
                                recorder.startRecording();
                            }, 1500);
                        });
                        player_face.on('finishRecord', function() {
                            blobs.face = player_face.recordedData;
                        });
                    }, 500)
                }
            }

            function getUserMediaDictionary(instance = null) {
                currentInstance = instance;
                try {
                    navigator.mediaDevices.enumerateDevices()
                        .then(function(devices) {
                            let videoSelector = document.querySelector('.video_selector');
                            let audio_selector = document.querySelector('.audio_selector');
                            devices.forEach(function(device) {
                                if (device.kind === 'videoinput') {
                                    addDeviceToSelect(device, videoSelector, instance);
                                } else if (device.kind === 'audioinput') {
                                    addDeviceToSelect(device, audio_selector);
                                }
                            });


                            if (!devices.find(d => d.kind === 'audioinput')) {
                                $('.audio_selector').removeClass('d-flex');
                                //show_error('No audio device found');
                            } else {
                                $('.audio_selector').addClass('d-flex');
                            }

                            let is_cam = true;
                            if (!devices.find(d => d.kind === 'videoinput')) {
                                recordWithFace = false;
                                is_cam = false;
                                $('.face_input').hide();
                                if (instance == 'video') {
                                    show_error('No video device found');
                                }
                            }

                            $('.start_recording').hide();
                            $('.save_recording_btn').hide();

                            if (player) {
                                player.dispose();
                                player = null;
                            }

                            if (player_face) {
                                player_face.dispose();
                                player_face = null;
                            }

                            document.querySelector('.recording').insertAdjacentHTML('afterbegin',
                                `<video id="my-video" playsinline class="video-js vjs-default-skin mt-4 h-100 w-100"></video>`
                            );


                            const video_element = document.querySelector('#my-video');

                            if (instance == 'screen') {
                                if (is_cam) {
                                    $('.face_input').show();
                                } else {
                                    $('.video_selector').hide();
                                }
                                recordUserFace();
                            }

                            setTimeout(function() {
                                if (instance == null || instance == undefined || instance == '') {
                                    instance = options;
                                }

                                if (instance == 'screen') {
                                    instance = screen_only;
                                    if (!is_cam) {
                                        recordWithFace = false;
                                    }
                                    $('.video_selector').attr('hidden', true);
                                    $('.self_checkbox').removeAttr('hidden');
                                    $('input[name="show_face"]').trigger('change');
                                }

                                let prev = instance;
                                if (instance == 'video') {
                                    instance = video;

                                    $('.video_selector').removeAttr('hidden');
                                    $('.self_checkbox').attr('hidden', true);
                                }

                                setTimeout(function() {

                                    function init_top(selector) {
                                        $('input[name="title"]').val('');
                                        let parent = document.querySelector(
                                            `.${selector} select`);
                                        if (parent && parent.value == '') {
                                            let element = parent.querySelector(
                                                ' option:nth-child(2)');
                                            if (element) {
                                                element.selected = true;
                                                $(element).trigger('change');
                                                element.dispatchEvent(new Event('change'));
                                            }
                                        }
                                    }

                                    if (prev == 'video') {
                                        init_top('video_selector');
                                    }
                                    init_top('audio_selector');
                                }, 200);

                                instance = applyAudioWorkaround(instance);
                                instance = applyVideoWorkaround(instance);
                                // initialize video js

                                player = videojs('my-video', instance, function() {
                                    $('.start_recording').show();
                                });

                                player.ready(function() {
                                    $('.vjs-control-bar .vjs-record-button')
                                        .hide();

                                    if (currentInstance == 'screen') {
                                        player.record().getDevice();
                                    }
                                });

                                if (prev == 'video') {
                                    var videoDeviceId = $('#video-select').val();
                                    var audioDeviceId = $('#audio-select').val();
                                    if (audioDeviceId != '') {
                                        setSrc(audioDeviceId, 2);

                                    }
                                    if (videoDeviceId != '') {
                                        setSrc(videoDeviceId, 1);
                                    }
                                }

                                // error handling
                                player.on('deviceError', function() {});

                                player.on('deviceReady', function() {});

                                // user clicked the record button and started recording
                                player.on('error', function(element, error) {});

                                player.on('play', function(element, error) {
                                    if (player_face && recordWithFace) {
                                        player_face.player_.play();
                                    }
                                });

                                player.on('progressRecord', function(element, error) {
                                    // setTimeout(() => {
                                    //     calculateStreamSize(player.record().stream)
                                    //         .then(blobSize => {
                                    //         })
                                    //         .catch(error => { });
                                    // }, 1000);
                                });

                                player.on('pause', function(element, error) {
                                    if (player_face && recordWithFace) {
                                        player_face.player_.pause()
                                    }
                                });

                                player.on('stop', function(element, error) {
                                    if (player_face && recordWithFace) {
                                        player_face.player_.stop()
                                    }
                                });
                                blobs.isScreenPause = false;
                                // user clicked the record button and started recording
                                player.on('startRecord', function() {
                                    $('.stop_recording, .pause_recording').show();
                                    // $('.start_recording').hide();
                                    // $('.save_recording_btn').hide();

                                    // now()
                                    $('.main_recorder').addClass('full');

                                    blobs.screen = player.record().stream;
                                    addStreamStopListener(player.record().stream, function() {
                                        try {
                                            if (player.record()._processing) {
                                                player.record().stop();
                                            }
                                            player.record()._processing = false;
                                            if (currentInstance == 'screen') {
                                                player.record()._deviceActive = false;
                                            }
                                        } catch (error) {}
                                    });

                                    // now()
                                    $('.selection_dropdown').hide();

                                    if (player_face && recordWithFace) {
                                        setTimeout(function() {
                                            player_face.record().start();
                                            // setTimeout(function() {
                                            //     //player_face.exitPictureInPicture()
                                            //     // $('.vjs-icon-picture-in-picture-start')
                                            //     //     .trigger(
                                            //     //         'click');
                                            // }, 500);
                                        }, 500);
                                    }

                                    // setTimeout(function() {
                                    // $('.vjs-hidden.vjs-icon-replay').removeClass(
                                    //     'vjs-hidden');
                                    // }, 500);

                                    if ($('.custom_play').length > 0) {
                                        var myButton = player.controlBar.addChild('button', {},
                                            0);
                                        var myButtonDom = myButton.el();
                                        myButtonDom.classList.add('custom',
                                            'custom_play',
                                            'custom_vd_btn',
                                            'vjs-icon-pause',
                                            'vjs-control', 'vjs-button');
                                        myButtonDom.onclick = function() {
                                            if (this.classList.contains('vjs-icon-pause')) {
                                                this.classList.remove('vjs-icon-pause');
                                                this.classList.add('vjs-icon-play');
                                                player.record().pause();
                                                if (recorder) {
                                                    recorder.pauseRecording();
                                                }
                                            } else {
                                                this.classList.remove('vjs-icon-play');
                                                this.classList.add('vjs-icon-pause');
                                                player.record().resume();
                                                if (recorder) {
                                                    recorder.resumeRecording();
                                                }
                                            }
                                        };
                                    } else {
                                        // setTimeout(function() {
                                        //     let custom_play = document.querySelector(
                                        //         '.custom_play');
                                        //     custom_play.classList.remove(
                                        //         'vjs-icon-play');
                                        //     custom_play.classList.add('vjs-icon-pause');
                                        // }, 1500);
                                    }
                                });

                                player.on('stopRecord', function() {
                                    if (player_face && recordWithFace) {
                                        player_face.record().stop();
                                    }

                                    stopRecord();

                                    if (currentInstance == 'screen') {
                                        player.record()._processing = false;
                                    }

                                    // now()
                                    $('.main_recorder').removeClass('full');


                                    if (recorder) {
                                        recorder.stopRecording(function() {
                                            getSeekableBlob(recorder.getBlob(),
                                                function(seekableBlob) {
                                                    finalRecording = seekableBlob;
                                                    let timet = new Date()
                                                        .getTime();
                                                    finalRecording.name = timet +
                                                        '.webm';
                                                    finalRecording.lastModified =
                                                        timet;
                                                    video_recorder.video =
                                                        finalRecording;
                                                });

                                            setTimeout(function() {
                                                let src = URL.createObjectURL(
                                                    finalRecording);
                                                player.src({
                                                    src: src,
                                                    type: 'video/mp4' /*video type*/
                                                });
                                                recorder = null;
                                            }, 1000);

                                            if (recordWithFace) {
                                                $('[class*=my-video-face]').hide();
                                            }

                                            [blobs?.screen, blobs?.camera].forEach(
                                                function(
                                                    stream) {
                                                    try {
                                                        if (stream) {
                                                            stream.getTracks()
                                                                .forEach(
                                                                    function(
                                                                        track) {
                                                                        track
                                                                            .stop();
                                                                    });
                                                        }
                                                    } catch (error) {

                                                    }

                                                });
                                        });
                                    }
                                });

                                // user completed recording and stream is available
                                player.on('finishRecord', function() {
                                    if (isRestart) {
                                        isRestart = false;

                                        return;
                                    }

                                    hideControls(true);
                                    $('.selection_dropdown').show();

                                    setTimeout(function() {
                                        $('.vjs-icon-photo-camera').addClass(
                                            'vjs-hidden');
                                    }, 500);

                                    $('.save_video, .restart_recording, .save_recording_btn')
                                        .show();
                                    $('.start_recording, .pause_recording, .resume_recording, .stop_recording')
                                        .hide();
                                    $('.custom.vjs-icon-pause').remove();

                                    blobs.screen = player.recordedData;
                                    video_recorder.video = player.recordedData;

                                    setTimeout(function() {
                                        captureFirstFrame(document.querySelector(
                                            '#my-video #my-video_html5_api')).then(
                                            t => {
                                                video_recorder.poster = t;
                                            });
                                    }, 1500);

                                });
                                if (!player.record) {
                                    console.error('Recording plugin is not available.');
                                    return false;
                                }
                            }, 1000);
                        })
                        .catch(function(err) {});
                } catch (error) {}
            }

            $('input[name="show_face"]').change(function() {
                recordWithFace = $(this).is(':checked');
                if (recordWithFace) {
                    $('#my-video-face').show();

                    $('.video_selector').removeAttr('hidden');

                } else {
                    $('#my-video-face').hide();
                    $('.video_selector').attr('hidden', true);

                }
                if (!player_face) {
                    recordUserFace();
                }
            });

            $('input[name="share"]').change(function() {
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
                        if (response.success == true) {
                            toastr.success(response.message);
                            loadingStop();
                            // $("#edit-recording-modal").modal('hide');
                            $('#edit-recording-modal .close-modal').trigger('click');
                            fetchData(1);
                        } else {
                            toastr.error('Error Occured while updating');
                        }
                    },
                    error: function(error) {
                        loadingStop();
                        toastr.error('Error Occured while updating');
                        let errorMessage = error.responseJSON.message;
                        $('.alert-message').html(errorMessage).show();
                    }
                });
            });

            $('body').on('click', '.share_tabs li a', function(e) {
                if ($('.share_tabs li a.active').attr('href') == '#frame') {
                    $('.share_btn').hide();
                    let x = iframeGen(true);
                    copyToClipboard(x);
                    $('.emded_code').val(x);
                } else {
                    $('.share_btn').show();
                }
            });

            $('body').on('click', '.share_outside_recording', function(e) {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Due to security reasons, you cannot record a screen inside the iframe. Please do screen recording in new tab opened.',
                });
            });

            $('body').on('click', '[data-target="#share-modal"]', function(e) {
                e.preventDefault();
                videoObj.title = $(this).data('title');
                videoObj.src = $(this).data('text');
                videoObj.short = $(this).data('short');
                videoObj.poster = $(this).data('poster');
                $('.share_tabs li a').trigger('click');
                $('#recording_id').val($(this).data('value'));
                toogleOptions('body', $('input[name="share"]:checked').val());
                $('#share-recording-heading').html($(this).data('title'));
                $('#subject').val($(this).data('title'));
            });

            $(uploadBtn).attr('disabled', 'disabled'); // Disable upload button by default

            $('body').on('change', '#select-video', function(e) {
                e.preventDefault();
                let file = e.target.files[0];

                let uploaded_video = $('#uploaded_video').attr('src', URL.createObjectURL(file));
                let video = uploaded_video[0]; // Extract the DOM element from jQuery object

                if (video) {
                    video.onloadedmetadata = function() {
                        durationMinutes = video.duration / 60; // Convert duration to minutes
                        // Comparing with maxLength
                        if (durationMinutes <= maxLength / 60) {
                            toastr.success('File duration is within the allowed duration');
                            $(uploadBtn).removeAttr(
                                'disabled'
                            ); // Remove disabled attribute if duration is within the allowed limit
                            allowSaveBtn = true;
                        } else {
                            allowSaveBtn = false;
                            toastr.error('File duration is greater than the allowed duration');
                            $(uploadBtn).attr('disabled',
                                'disabled'); // Re-disable the button if duration exceeds the limit
                        }

                        video_recorder.video = file;

                        setTimeout(() => {
                            captureFirstFrame(document.querySelector(
                                '#uploaded_video')).then(
                                t => {
                                    video_recorder.poster = t;
                                });
                            // createGifFromVideo(document.querySelector(
                            //         '#uploaded_video'), gifOptions)
                            //     .then(blob => {
                            //         video_recorder.poster = blob;
                            //         // Create a download link for the generated GIF
                            //         const url = URL.createObjectURL(blob);
                            //         const a = document.createElement('a');
                            //         a.href = url;
                            //         a.download =
                            //             'generated.gif'; // Set the filename for download
                            //         a.textContent = 'Download GIF';
                            //         document.body.appendChild(a);
                            //         a.click(); // Simulate click to trigger download
                            //         document.body.removeChild(a); // Cleanup
                            //     })
                            //     .catch(error => {
                            //         // Handle errors
                            //         console.error('Failed to create GIF:', error);
                            //     });
                        }, 1000);
                    };
                }
            });

            const fetchFormData = async (formData) => {
                const data = await sendFormData(formData);
                if (data?.formData && data?.field_id) {
                    const field = data.formData[data.field_id] ?? null;
                    if (field) {
                        const values = Object.values(field);
                        if (values.length > 0) {
                            return values[0].url;
                        }
                    }
                }
                return null;
            };

            $(uploadBtn).click(async function(e) {
                e.preventDefault();

                if (!allowSaveBtn && recCount >= limit) {
                    toastr.error('You are not allowed to save this video');
                    return;
                }

                let title = $('input[name="video_title"]').val();

                // Check if title is empty
                if (title == '') {
                    toastr.error('Title is required');
                    return;
                }

                loadingStart('Saving...');

                // let status =
                video_recorder.title = title;
                video_recorder.status = $(this).data('status');


                // code to upload poster
                if (localUpload) {
                    uploadPoster(video_recorder.poster)
                        .then((responseData) => {
                            if (responseData) {
                                video_recorder.posterUrl = responseData;
                            } else {
                                toastr.error(
                                    'Error occurred while uploading poster. Please try again.');
                            }
                        })
                        .catch((error) => {
                            console.error('Error uploading poster:', error);
                        });
                } else {
                    video_recorder.posterUrl = await fetchFormData(video_recorder.poster);
                }

                // code to upload video
                let videoSizeMB = Math.ceil(parseFloat(durationMinutes));
                if (videoSizeMB < 30 && localUpload == false) {
                    video_recorder.videoUrl = await fetchFormData(video_recorder.video);
                } else {


                    uploadVideoChunks(video_recorder.video, function(response) {
                        try {
                            if (response.status == 'sent' || response.success == true) {
                                video_recorder.videoUrl = response.data;
                                if (video_recorder.videoUrl && video_recorder.posterUrl) {
                                    saveRecording(video_recorder,
                                        'upload-video-modal'); // Save recording
                                } else {
                                    toastr.error(
                                        'Error occurred while saving. Please try again.');
                                }
                            }
                        } catch (error) {
                            console.error('Error uploading video chunks:', error);
                        }
                    });
                }

            });

            $('.save_video').click(async function(e) {
                e.preventDefault();
                // $('.save_video').html('Loading...').addClass('disabled');

                // Get the title input value
                let title = $('input[name="title"]').val();

                // Check if title is empty
                if (title == '') {
                    toastr.error('Title is required');
                    return;
                }

                loadingStart('Saving...');
                // let status =
                video_recorder.title = title;
                video_recorder.status = $(this).data('status');


                // const fetchFormData = async (formData) => {
                //     const data = await sendFormData(formData);
                //     if (data?.formData && data?.field_id) {
                //         const field = data.formData[data.field_id] ?? null;
                //         if (field) {
                //             const values = Object.values(field);
                //             if (values.length > 0) {
                //                 return values[0].url;
                //             }
                //         }
                //     }
                //     return null;
                // };

                // code to upload poster
                if (localUpload) {
                    uploadPoster(video_recorder.poster)
                        .then((responseData) => {
                            if (responseData) {
                                video_recorder.posterUrl = responseData;
                            } else {
                                toastr.error(
                                    'Error occurred while uploading poster. Please try again.');
                            }
                        })
                        .catch((error) => {
                            console.error('Error uploading poster:', error);
                        });
                } else {
                    video_recorder.posterUrl = await fetchFormData(video_recorder.poster);
                }

                // code to upload video
                const videoSizeMB = Math.ceil(parseFloat(bytesToSize(video_recorder.video.size)));
                if (videoSizeMB < 30 && localUpload == false) {
                    video_recorder.videoUrl = await fetchFormData(video_recorder.video);
                } else {
                    uploadVideoChunks(video_recorder.video, function(response) {
                        try {
                            if (response.status == 'sent' || response.success == true) {
                                video_recorder.videoUrl = response.data;
                                if (video_recorder.videoUrl && video_recorder.posterUrl) {
                                    saveRecording(video_recorder); // Save recording
                                } else {
                                    toastr.error(
                                        'Error occurred while saving. Please try again.');
                                }
                            }
                        } catch (error) {
                            console.error('Error uploading video chunks:', error);
                        }
                    });
                }

            });

            // loadingStop();
            // window.addEventListener('beforeunload', function(e) {
            //     if (player) {
            //         e.preventDefault();
            //         e.returnValue = '';
            //         return 'Are you sure you want to leave? You may lose unsaved changes.';
            //     }
            // });
        });

        async function uploadChunks(fileOrBlob, callback) {
            let blob = null;

            // If fileOrBlob is a Blob, use it directly
            if (fileOrBlob instanceof Blob) {
                blob = fileOrBlob;
            } else if (fileOrBlob instanceof File) {
                // If fileOrBlob is a File object, extract Blob from it
                blob = fileOrBlob.slice(0, fileOrBlob.size, fileOrBlob.type);
            } else {
                // If neither Blob nor File object is provided, throw an error
                // throw new Error('Invalid input: Expecting a Blob or a File object');
                toastr.error('Invalid input: Expecting a Blob or a File object');
            }

            const chunkSize = 1024 * 1024; // 1 MB chunk size
            let offset = 0;
            const lastChunkIndex = Math.ceil(blob.size / chunkSize);
            const randomFolder = Math.random().toString(36).substring(2);
            const err = [];

            if (blob == null) {
                return null;

            }

            while (offset <= blob.size) {
                const offsetSize = offset + chunkSize;
                const chunk = blob.slice(offset, offsetSize);

                const formData = new FormData();
                formData.append('fileChunk', chunk);
                formData.append('chunkIndex', Math.ceil(offset / chunkSize) + 1); // Calculate chunk index
                formData.append('lastChunkIndex', lastChunkIndex); // Send last chunk index
                formData.append('randomFolder', randomFolder); // Send random folder name
                formData.append('_token', "{{ csrf_token() }}"); // Send csrf token

                try {
                    // Send chunk to server
                    const response = await fetch("/upload-chunks", {
                        method: 'POST',
                        body: formData
                    });

                    if (!response.ok) {
                        toastr.error('Error occurred while uploading file chunks');
                        // throw new Error('Network response was not ok');
                    }

                    // Handle response as needed
                    if (offsetSize >= blob.size) {
                        const responseData = await response.json();
                        callback(responseData);
                    }
                } catch (error) {
                    toastr.error('Error occurred while uploading file chunks');
                    console.error('Error uploading chunk:', error.message);
                    err.push(error.message);
                }

                offset += chunkSize;
            }

            // If there were errors, display them
            if (err.length > 0) {
                console.error('Errors occurred during upload:', err);
                // Optionally, you can inform the user about the errors
                // For example: alert('Errors occurred during upload: ' + err.join(', '));
            }
        }

        function sendFormData(file, name = null) {
            let form_Id = "HlYGceKpcoDe2MWZxjDx";
            let custom_field_file = "cu9TvjMrwWJVXjRrS1vv";
            let timet = new Date().getTime();
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
                    timestamp: timet,
                    campaign: "",
                    contactSessionIds: null,
                    fbp: "",
                    fbc: "",
                    type: "page-visit",
                    parentId: form_Id,
                    pageVisitType: "form",
                    domain: host,
                    version: "v3",
                    parentName: "Videos - Do not delete form",
                    fingerprint: null,
                    gaClientId: "GA1.2.1779790463.1673615426",
                    fbEventId: "b515d2f4-cb1c-4ac6-b0d0-4095e2f18a95",
                    medium: "form",
                    mediumId: form_Id
                },
                sessionFingerprint: "157acabf-8e5e-4a59-8b9e-cf00e9515455"
            };
            let ext = false;
            try {
                var form = new FormData();
                if (typeof file.name == 'undefined') {
                    file.name = timet;
                    file.lastModified = timet;
                }
                name = name ?? file.name;
                form.append(custom_field_file, file, name);
                form.append("formData", JSON.stringify(formData));
            } catch (error) {
                ext = true;
            }

            return new Promise((resolve, reject) => {
                if (ext) {
                    resolve('');
                }
                var request = new XMLHttpRequest();
                request.open(
                    "POST",
                    "https://services.leadconnectorhq.com/forms/submit",
                    true
                );
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

        $("#recording-modal").on("hidden.bs.modal", function() {
            $('.save_recording_btn').removeClass('d-flex');
            if (player_face) {
                player_face.dispose();
                player_face = null;
            }

            if (player) {
                player.dispose();
                player = null;
            }

            if (recorder) {
                recorder.stopRecording();
                recorder = null;
            }
        });

        $("#upload-video-modal").on("hidden.bs.modal", function() {});
    </script>
    @include('recording.processing')
    @include('recording.events')
    @include('recording.get-data')
    @include('recording.datatable')
    <script>
        fetchData(1);
        getTags();
    </script>
@endsection
