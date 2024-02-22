@extends('layouts.app')
@section('title', 'Recordings')
@section('css')
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

    <script>
        var player = null;
        let player_face = null;
        let is_company = `{{ is_company() }}` == '0';
        var pipEnabled = false;
        var pipStatusMsg;
        let maxLength = "{{ $user->plan->recording_minutes_limit }}" * 60;
        let recordWithFace = true;

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

        let finalRecording = null;

        let recorder = null;

        if (window.self == window.parent && is_company) {
            document.querySelector('body').classList.remove('iframe');
        }

        let audioRecord = null;

        let currentInstance = false;

        $(document).ready(function() {

            if (window.parent != window.self) {
                $('.share_recording').hide();
            } else {
                $('.share_outside_recording').hide();
            }

            setTimeout(function() {
                if (location.href.includes('action=share')) {
                    // console.log('share');
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

            // let videoRtc = {
            //     width: 1920,
            //     height: 1080
            // };

            let videoRtc = {
                width: 1080,
                height: 720
            };

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
                        audio: audio_rtc,
                        video: video_setting_rtc,
                        maxLength: maxLength,
                        displayMilliseconds: false
                        // frameRate: 30,
                        // mimeType: 'video/webm;codecs=h264', // Set the desired video codec and MIME type
                        // videoBitsPerSecond: 10 * 1024 * 1024
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
                        muted: false
                        // frameRate: 30,
                        // mimeType: 'video/webm;codecs=h264', // Set the desired video codec and MIME type
                        // videoBitsPerSecond: 10 * 1024 * 1024
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

            // applyScreenWorkaround();

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
                    } catch (error) {}
                    // document.querySelector('.self_checkbox').insertAdjacentHTML('beforeEnd',
                    //     `<video id="my-video-face" hidden src="" playsinline class="video-js hide vjs-default-skin mt-4 h-full w-100" ></video>`
                    // );

                    $('.self_checkbox').after(
                        `<video id="my-video-face" hidden src="" playsinline class="video-js hide vjs-default-skin mt-4 h-100 w-100"></video>`
                    );

                    video_screen = applyAudioWorkaround(video_screen);
                    video_screen = applyVideoWorkaround(video_screen);

                    setTimeout(function() {
                        player_face = videojs('my-video-face', video_screen,
                            function() {
                                // console.log('videojs-record initialized!');
                            });
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
                        player_face.on('deviceError', function() {
                            console.warn('device error:', player_face
                                .deviceErrorCode);
                        });

                        // user clicked the record button and started recording
                        player_face.on('error', function(element, error) {
                            // console.error(error);
                        });

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
                                console.log(allStreams);

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
                            // // console.log(devices);
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

                            // console.log(player, player_face);
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
                                player.on('deviceError', function() {
                                    console.warn('device error:', player.deviceErrorCode);
                                });

                                player.on('deviceReady', function() {
                                    // console.log('player device is ready');
                                });

                                // user clicked the record button and started recording
                                player.on('error', function(element, error) {
                                    console.error(error);
                                });

                                player.on('play', function(element, error) {
                                    if (player_face && recordWithFace) {
                                        player_face.player_.play();
                                    }
                                });

                                player.on('progressRecord', function(element, error) {
                                    // console.log(player.duration());
                                    // setTimeout(() => {
                                    //     calculateStreamSize(player.record().stream)
                                    //         .then(blobSize => {
                                    //             console.log('Stream size:',
                                    //                 blobSize, 'bytes');
                                    //         })
                                    //         .catch(error => {
                                    //             console.error(
                                    //                 'Error calculating stream size:',
                                    //                 error);
                                    //         });
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
                                        // console.log(myButton);
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

                                            // = recorder.getBlob();
                                            // downloadRecord(finalRecording);
                                            // console.log(recorder.getBlob());
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
                                    // // console.log('stopped recording');
                                });

                                // user completed recording and stream is available
                                player.on('finishRecord', function() {
                                    //l
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

                                    // getting the size of the video
                                    console.log(Math.floor(bytesToMB(video_recorder.video
                                        .size)));

                                    if (recordWithFace && player_face) {
                                        video_recorder.video_orig = player.recordedData;
                                    }

                                    setTimeout(function() {
                                        captureFirstFrame(document.querySelector(
                                            '#my-video #my-video_html5_api')).then(
                                            t => {
                                                // console.log(
                                                //     'caturing first frame of the video of the main video tag'
                                                // );
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
                        .catch(function(err) {
                            // console.log('Error enumerating devices: ' + err);
                        });
                } catch (error) {
                    // console.log('Error enumerating devices: ' + error);
                }
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

            $('.save_video').click(async function(e) {
                e.preventDefault();
                $('.save_video').html('Loading...').addClass('disabled');

                // Get the title input value
                let title = $('input[name="title"]').val();

                // Check if title is empty
                if (title == '') {
                    toastr.error('Title is required');
                    return;
                }

                loadingStart('Saving...');

                let status = $(this).data('status');
                video_recorder.title = title;
                video_recorder.status = status;

                const fetchFormData = async (formData) => {
                    const data = await sendFormData(formData);
                    if (data?.formData && data?.field_id) {
                        const field = data.formData[data.field_id] ?? null;
                        if (field) {
                            const values = Object.values(field);
                            if (values.length > 0) {
                                console.log(values[0].url);
                                return values[0].url;
                            }
                        }
                    }
                    return null;
                };

                // Check if the video size exceeds the limit
                console.log('Video size:', bytesToMB(video_recorder.video.size), 'MB');
                if (Math.floor(bytesToMB(video_recorder.video.size)) > 30) {
                    toastr.error('Video size exceeds the limit allowed by CRM');
                    loadingStop();
                    return;
                }

                try {
                    // Fetch poster and video URLs
                    video_recorder.posterUrl = await fetchFormData(video_recorder.poster);
                    video_recorder.videoUrl = await fetchFormData(video_recorder.video);

                    // Delay before checking if fetch operations are completed
                    setTimeout(function() {
                        loadingStart('Saving...');
                        // Check if both poster and video URLs are fetched successfully
                        if (video_recorder.videoUrl && video_recorder.posterUrl) {
                            console.log('Video size:', bytesToMB(video_recorder.video.size),
                                'MB');
                            console.log('Video recorder:', video_recorder);
                            saveRecording(video_recorder); // Save recording
                        } else {
                            toastr.error('Error occurred while saving. Please try again.');
                        }
                        loadingStop();
                        $('.save_video').html('Share').removeClass('disabled');
                    }, 2500);
                } catch (error) {
                    console.error('Error:', error);
                    toastr.error('Error occurred while fetching data');
                    // loadingStop();
                }

            });



            loadingStop();
            // window.addEventListener('beforeunload', function(e) {
            //     if (player) {
            //         e.preventDefault();
            //         e.returnValue = '';
            //         return 'Are you sure you want to leave? You may lose unsaved changes.';
            //     }
            // });
        });

        // async function calculateStreamSize(stream) {
        //     if (stream) {
        //         // Create a MediaRecorder to record the stream
        //         const mediaRecorder = new MediaRecorder(stream);

        //         // Create a Promise to capture the recorded Blob
        //         const recordedBlobPromise = new Promise((resolve, reject) => {
        //             const recordedBlobs = [];
        //             mediaRecorder.ondataavailable = event => {
        //                 if (event.data && event.data.size > 0) {
        //                     recordedBlobs.push(event.data);
        //                 }
        //             };

        //             mediaRecorder.onstop = () => {
        //                 const combinedBlob = new Blob(recordedBlobs, {
        //                     type: mediaRecorder.mimeType
        //                 });
        //                 resolve(combinedBlob);
        //             };

        //             mediaRecorder.onerror = error => {
        //                 reject(error);
        //             };
        //         });

        //         // Start recording
        //         mediaRecorder.start();

        //         // Wait for recording to finish
        //         await new Promise(resolve => setTimeout(resolve, 1000)); // Adjust timeout as needed

        //         // Stop recording
        //         mediaRecorder.stop();

        //         // Get the recorded Blob
        //         const recordedBlob = await recordedBlobPromise;

        //         // Calculate and return the size of the Blob
        //         return recordedBlob.size;
        //     }

        //     return 0;
        // }

        function bytesToMB(bytes) {
            return bytes / (1024 * 1024);
        }

        $("#recording-modal").on("hidden.bs.modal", function() {
            $('.save_recording_btn').removeClass('d-flex');
            if (player_face) {
                player_face.dispose();
                player_face = null;
            }
        });

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
