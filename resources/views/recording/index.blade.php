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


        #my-video {
            min-height: 300px !important;
        }

        div#my-video-face :not(video) {
            display: none;
        }

        div#my-video-face video,
        div#my-video-face {
            width: 200px !important;
            height: 200px !important;
        }

        div#my-video-face {
            position: absolute;
            z-index: 9999999;
            bottom: 11%;
            left: 4%;
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
            video: null,
            status: 'draft',
            posterUrl: null,
            videoUrl: null,
            face: "",
            faceUrl: null
        };

        if (window.self == window.parent && is_company) {
            document.querySelector('body').classList.remove('iframe');
        }

        let audioRecord = null;

        function startRecord(audioSource) {
            let canvas = document.querySelector('#audioCanvas');
            if (audioSource == '' || !audioSource) {
                if (audioRecord) {
                    try {
                        audioRecord.stop();
                    } catch (error) {}
                }
                canvas.setAttribute('hidden', true);
                audioRecord = null;
            }

            audioRecord = navigator.mediaDevices.getUserMedia({
                audio: {
                    deviceId: {
                        exact: audioSource ?? 'default'
                    }
                },
                video: false
            });
            audioRecord.then(function onSuccess(stream) {
                audioContext = window.AudioContext || window.webkitAudioContext || window.mozAudioContext || window
                    .msAudioContext;
                try {
                    context = new audioContext();
                } catch (e) {
                    console.log('not support AudioContext');
                }

                audioInput = context.createMediaStreamSource(stream);
                var binaryData = [];
                binaryData.push(stream);
                microphone.src = window.URL.createObjectURL(new Blob(binaryData, {
                    type: 'application/zip'
                }));
                microphone.onloadedmetadata = function(e) {};
                var analyser = context.createAnalyser();
                audioInput.connect(analyser);

                drawSpectrum(analyser);
            });
            audioRecord.catch(function(e) {
                try {
                    tip.innerHTML = e.name;
                } catch (error) {}
            });

            var drawSpectrum = function(analyser) {
                let cwidth = canvas.width,
                    cheight = canvas.height,
                    meterWidth = 8,
                    gap = 2,
                    meterNum = cwidth / (meterWidth + gap),
                    ctx = canvas.getContext('2d'),
                    gradient = ctx.createLinearGradient(0, 0, 0, cheight);
                gradient.addColorStop(1, '#a467af');
                gradient.addColorStop(0.3, '#ff0');
                gradient.addColorStop(0, '#f00');
                ctx.fillStyle = gradient;
                canvas.removeAttribute('hidden', true);
                var drawMeter = function() {
                    var array = new Uint8Array(analyser.frequencyBinCount);
                    analyser.getByteFrequencyData(array);

                    var step = Math.round(array.length / meterNum);
                    ctx.clearRect(0, 0, cwidth, cheight);
                    for (var i = 0; i < meterNum; i++) {
                        var value = array[i * step];

                        ctx.fillRect(i * (meterWidth + gap), cheight - value, meterWidth, cheight);
                    }
                    requestAnimationFrame(drawMeter);
                }
                requestAnimationFrame(drawMeter);
            }
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

            loadingStart();
            $('.save_video, .restart_recording, .save_recording_btn').hide();
            hideControls();

            // document.querySelector('.recording').insertAdjacentHTML('afterbegin',
            //     `<video id="my-final-source"  class="video-js vjs-default-skin mt-4 h-full w-100" ></video>`
            // );

            // setTimeout(function() {
            //     var composite1 = new VideoStreamMerger()
            //     composite1.addStream(player.record().stream, {
            //         index: 0
            //     })
            //     composite1.addStream(player_face.record().stream, {
            //         x: composite.width - 150,
            //         y: composite.height - 100,
            //         width: 150,
            //         height: 150,
            //         index: 3
            //     });
            //     composite1.start();
            //     document.querySelector('#my-final-source').srcObject = composite1.result;
            // }, 3000);


            if (!('pictureInPictureEnabled' in document)) {
                pipStatusMsg = 'The Picture-in-Picture API is not available.';
            } else if (!document.pictureInPictureEnabled) {
                pipStatusMsg = 'The Picture-in-Picture API is disabled.';
            } else {
                pipEnabled = true;
            }
            let video_setting = {
                width: {
                    min: 640,
                    ideal: 640,
                    max: 1280
                },
                height: {
                    min: 480,
                    ideal: 480,
                    max: 720
                },
            };
            //userinactive
            let video = {
                controls: true,
                // autoMuteDevice: true,
                controlBar: {
                    fullscreenToggle: true,
                    volumePanel: true,
                    customControlSpacer: true
                },
                plugins: {
                    record: {
                        audio: true,
                        // pip: pipEnabled,
                        video: video_setting,

                        maxLength: maxLength,
                        displayMilliseconds: false,
                        frameWidth: 1080,
                        frameHeight: 720,
                        // debug: true,
                        muted: false
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
                        video: video_setting,

                        maxLength: maxLength,
                        displayMilliseconds: false,
                        // debug: true,

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

            // function captureFirstFrame(videoElement) {
            //     const canvas = document.createElement('canvas');
            //     const ctx = canvas.getContext('2d');
            //     const firstFrameWidth = videoElement.videoWidth;
            //     const firstFrameHeight = videoElement.videoHeight;
            //     canvas.width = firstFrameWidth;
            //     canvas.height = firstFrameHeight;
            //     ctx.drawImage(videoElement, 0, 0, firstFrameWidth, firstFrameHeight);
            //     return new Promise((resolve, reject) => {
            //         canvas.toBlob(blob => {
            //             const a = document.createElement('a');
            //             const url = URL.createObjectURL(blob);
            //             URL.revokeObjectURL(url);
            //             resolve(blob);
            //         }, 'image/png');
            //     });
            // }

            async function captureAndCombineFrames(videoElement1, videoElement2) {
                // Capture the first frame of the first video
                const firstFrame1 = await captureFirstFrame(videoElement1);

                // Capture the first frame of the second video
                const firstFrame2 = await captureFirstFrame(videoElement2);

                // Create a canvas to draw the images
                const canvas = document.createElement('canvas');
                const ctx = canvas.getContext('2d');

                // Set canvas dimensions to accommodate both images
                canvas.width = Math.max(videoElement1.videoWidth, videoElement2.videoWidth);
                canvas.height = videoElement1.videoHeight + videoElement2.videoHeight;

                // Draw the first frame of the first video on the canvas
                ctx.drawImage(firstFrame1, 0, 0);

                // Draw the first frame of the second video on the bottom left corner
                ctx.drawImage(firstFrame2, 0, videoElement1.videoHeight);

                // Convert canvas content to a blob
                return new Promise((resolve, reject) => {
                    canvas.toBlob(blob => {
                        resolve(blob);
                    }, 'image/png');
                });
            }

            async function captureFirstFrame(videoElement) {
                return new Promise((resolve, reject) => {
                    const canvas = document.createElement('canvas');
                    const ctx = canvas.getContext('2d');
                    const firstFrameWidth = videoElement.videoWidth;
                    const firstFrameHeight = videoElement.videoHeight;
                    canvas.width = firstFrameWidth;
                    canvas.height = firstFrameHeight;
                    ctx.drawImage(videoElement, 0, 0, firstFrameWidth, firstFrameHeight);
                    canvas.toBlob(blob => {
                        resolve(blob);
                    }, 'image/png');
                });
            }

            function videoMergeSaver(blob1) {

                const blob = new Blob(blob1, {
                    type: 'video/webm'
                });
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = 'recorded-video.webm';
                document.body.appendChild(a);
                a.click();
                setTimeout(() => {
                    document.body.removeChild(a);
                    window.URL.revokeObjectURL(url);
                }, 0);

            }

            function recordUserFace() {
                if (recordWithFace) {
                    player_face = videojs('my-video-face', video_screen,
                        function() {
                            // // console.log('videojs-record initialized!');
                        });
                    player_face.ready(function() {
                        setTimeout(function() {
                            var videoDeviceId = $('#video-select').val();
                            if (videoDeviceId != '') {
                                player_face.record().setVideoInput(videoDeviceId);
                            }
                            $('.vjs-icon-video-perm')
                                .trigger('click');
                            document.querySelector('#my-video-face').setAttribute('class', '');
                        }, 500);

                    });

                    // error handling
                    player_face.on('deviceError', function() {
                        console.warn('device error:', player_face
                            .deviceErrorCode);
                    });

                    // user clicked the record button and started recording
                    player_face.on('error', function(element, error) {
                        console.error(error);
                    });

                    player_face.on('finishRecord', function() {
                        blobs.face = player_face.recordedData;
                    });
                }
            }

            function getUserMediaDictionary(instance = null) {
                try {
                    navigator.mediaDevices.enumerateDevices()
                        .then(function(devices) {
                            // // console.log(devices);
                            devices.forEach(function(device) {
                                if (device.kind === 'videoinput') {
                                    addDeviceToSelect(device, 'video_selector', instance);
                                } else if (device.kind === 'audioinput') {
                                    addDeviceToSelect(device, 'audio_selector');
                                }
                            });
                            // console.log(player, player_face);
                            $('.start_recording').hide();
                            $('.save_recording_btn').hide();

                            if (player) {
                                player.dispose();
                            }

                            if (player_face) {
                                player_face.dispose();
                            }


                            document.querySelector('.recording').insertAdjacentHTML('afterbegin',
                                `<video id="my-video" playsinline class="video-js vjs-default-skin mt-4 h-full w-100" ></video>`
                            );

                            // if (instance == 'screen1') {
                            if (instance == 'screen') {


                                document.querySelector('.recording').insertAdjacentHTML('afterbegin',
                                    `<video id="my-video-face" playsinline class="video-js hide vjs-default-skin mt-4 h-full w-100" ></video>`
                                );

                                setTimeout(recordUserFace, 500);
                            }

                            setTimeout(function() {
                                if (instance == null || instance == undefined || instance == '') {
                                    instance = options;
                                }

                                if (instance == 'screen') {
                                    instance = screen_only;
                                    $('.video_selector').attr('hidden', true);
                                    $('.self_checkbox').removeAttr('hidden');
                                }

                                let prev = instance;
                                if (instance == 'video') {
                                    instance = video;
                                    $('.video_selector').removeAttr('hidden');
                                    $('.self_checkbox').attr('hidden', true);
                                }

                                setTimeout(function() {

                                    function init_top(selector) {
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

                                applyAudioWorkaround(instance);
                                applyVideoWorkaround(instance);
                                // initialize video js

                                player = videojs('my-video', instance, function() {
                                    $('.start_recording').show();
                                });

                                player.ready(function() {
                                    // $('.vjs-control-bar .vjs-record-button')
                                    //     .hide();
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

                                player.on('play', function(element, error) {
                                    if (player_face && recordWithFace) {
                                        player_face.player_.play()
                                    }
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

                                // user clicked the record button and started recording

                                player.on('startRecord', function() {
                                    $('.stop_recording, .pause_recording').show();
                                    // $('.start_recording').hide();
                                    // $('.save_recording_btn').hide();
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

                                    setTimeout(function() {
                                        $('.vjs-hidden.vjs-icon-replay').removeClass(
                                            'vjs-hidden');
                                    }, 500);

                                    if ($('.custom_play').length == 0) {
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
                                            } else {
                                                this.classList.remove('vjs-icon-play');
                                                this.classList.add('vjs-icon-pause');
                                                player.record().resume();
                                            }
                                        };
                                    } else {
                                        setTimeout(function() {
                                            let custom_play = document.querySelector(
                                                '.custom_play');
                                            custom_play.classList.remove(
                                                'vjs-icon-play');
                                            custom_play.classList.add('vjs-icon-pause');
                                        }, 1500);
                                    }
                                    // Setting control text for the button hover effect
                                    //myButton.controlText("Pause Recording");
                                    // Setting the control button click function
                                });

                                player.on('stopRecord', function() {
                                    // // console.log('stopped recording');
                                });

                                // user completed recording and stream is available
                                player.on('finishRecord', function() {
                                    hideControls(true);

                                    $('.selection_dropdown').show();

                                    setTimeout(function() {
                                        $('.vjs-icon-photo-camera').addClass(
                                            'vjs-hidden');
                                    }, 500);

                                    if (player_face && recordWithFace) {
                                        player_face.record().stop();
                                    }

                                    $('.save_video, .restart_recording, .save_recording_btn')
                                        .show();
                                    $('.start_recording, .pause_recording, .resume_recording, .stop_recording')
                                        .hide();
                                    $('.custom.vjs-icon-pause').remove();

                                    let poster = [];

                                    // capturing the first frame of the stream
                                    captureFirstFrame(document.querySelector(
                                        '#my-video #my-video_html5_api')).then(t => {
                                        video_recorder.poster = t;
                                    });

                                    if (recordWithFace && player_face) {
                                        // captureAndCombineFrames('#my-video #my-video_html5_api',
                                        //     '#my-video-face #my-video_html5_api');

                                        // captureAndCombineFrames('#my-video #my-video_html5_api',
                                        //         '#my-video-face #my-video-face_html5_api')
                                        //     .then(combinedBlob => {
                                        //         // Use the combinedBlob as needed (e.g., display or download)
                                        //         const img = document.createElement('img');
                                        //         img.src = URL.createObjectURL(combinedBlob);
                                        //         video_recorder.poster = img;
                                        //         document.body.appendChild(img);
                                        //     })
                                        //     .catch(error => {
                                        //         console.error('Error:', error);
                                        //     });
                                        // captureFirstFrame(document.querySelector(
                                        //         '#my-video-face #my-video-face_html5_api'))
                                        //     .then(t => {
                                        //         poster[1] = t;
                                        //     });

                                        // console.log(" poseter => " + poster);
                                        // let combined = combineImages(poster[0], poster[1]);
                                        // console.log("combined => " + combined)

                                        // combined.then(t => {
                                        //     video_recorder.poster = t;
                                        // });

                                        // captureFirstFrame(document.querySelector(
                                        //         '#my-video-face #my-video-face_html5_api'))
                                        //     .then(t => {
                                        //         video_recorder.face_poster = t;
                                        //     });
                                    }

                                    blobs.screen = player.recordedData;
                                    // if (prev == 'screen') {
                                    //     setTimeout(function() {
                                    //         // blobs.face = player_face.recordedData;

                                    //         // loadingStart('Merging...');
                                    //         // console.log(blobs);

                                    //         // var player_video_el =
                                    //         //     createVideoElementFromBlob(player
                                    //         //         .recordedData,
                                    //         //         'player');

                                    //         // if (recordWithFace) {
                                    //         //     var player_face_video_el =
                                    //         //         createVideoElementFromBlob(
                                    //         //             player_face
                                    //         //             .recordedData,
                                    //         //             'player_face');
                                    //         // }


                                    //         // console.log(player_video_el,
                                    //         //     player_face_video_el,
                                    //         //     player_video_el.id,
                                    //         //     player_face_video_el.id);

                                    //         // mergeing 2 elemtns media
                                    //         // var merge_data = mergeVideos(
                                    //         //     player_video_el,
                                    //         //     player_face_video_el);

                                    //         // console.log(merge_data.result);
                                    //         // downloadMediaStream(merge_data.result)
                                    //         // saving blob to database using this
                                    //         loadingStop();
                                    //         // set the video blob to the video recorder4
                                    //         // creating video elements from blobs
                                    //     }, 1500);
                                    // }
                                    setTimeout(function() {
                                        video_recorder.video = player.recordedData;
                                        if (recordWithFace && player_face) {
                                            video_recorder.face = player_face
                                                .recordedData ?? null;
                                        }
                                    }, 500);
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

            function combineImages(imageSrc1, imageSrc2) {
                return new Promise((resolve, reject) => {
                    // Create two image objects
                    const img1 = new Image();
                    const img2 = new Image();

                    // Load the first image
                    img1.onload = function() {
                        // Load the second image
                        img2.onload = function() {
                            // Create a canvas element
                            const canvas = document.createElement('canvas');
                            const ctx = canvas.getContext('2d');

                            // Set canvas dimensions to accommodate both images
                            canvas.width = Math.max(img1.width, img2.width);
                            canvas.height = img1.height + img2.height;

                            // Draw the first image onto the canvas
                            ctx.drawImage(img1, 0, 0);

                            // Draw the second image on top of the first one
                            ctx.drawImage(img2, 0, img1.height);

                            // Convert canvas content to a blob
                            canvas.toBlob(blob => {
                                resolve(blob);
                            }, 'image/png');
                        };
                        img2.src = imageSrc2;
                    };
                    img1.src = imageSrc1;
                });
            }


            // function mergeScreen() {
            //     loadingStart('Merging...');
            //     setTimeout(function() {
            //         var composite = new VideoStreamMerger()
            //         composite.addStream(player.record().stream, {
            //             index: 0
            //         })
            //         composite.addStream(player_face.record().stream, {
            //             x: composite.width - 700,
            //             y: composite.height - 180,
            //             width: 150,
            //             height: 150,
            //             index: 1
            //         });
            //         composite.start();
            //         // console.log(composite);
            //         document.querySelector('#my-final-source').srcObject = composite.result;
            //         return composite;
            //     }, 3000);
            //     loadingStop();
            // }

            $('input[name="show_face"]').change(function() {
                if ($(this).is(':checked')) {
                    $('#my-video-face').show();
                    recordWithFace = true;
                } else {
                    $('#my-video-face').hide();
                    recordWithFace = false;
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
                            location.reload();
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

                $('.share_tabs li a').trigger('click');

                $('#recording_id').val($(this).data('value'));
                toogleOptions('body', $('input[name="share"]:checked').val());
                $('#share-recording-heading').html($(this).data('title'));
                $('#subject').val($(this).data('title'));
                //getContacts();

            });

            $('.save_video').click(async function(e) {
                e.preventDefault();
                // // console.log('saving video');
                loadingStart('Saving...');

                // // console.log(video_recorder);

                let status = $(this).data('status');
                video_recorder.status = status;

                const fetchFormData = async (formData) => {
                    const data = await sendFormData(formData);
                    if (data?.formData && data?.field_id) {
                        // // console.log(data.formData);
                        const field = data.formData[data.field_id];
                        const values = Object.values(field);
                        if (values.length > 0) {
                            // console.log(values[0].url);
                            return values[0].url;
                        }
                    }
                    return null;
                };

                video_recorder.posterUrl = await fetchFormData(video_recorder.poster);
                video_recorder.videoUrl = await fetchFormData(video_recorder.video);
                if (recordWithFace && player_face) {
                    video_recorder.face = await fetchFormData(video_recorder.face);
                    video_recorder.face_poster = await fetchFormData(video_recorder.face_poster);

                }


                saveRecording(video_recorder);
                loadingStop();
            });
            loadingStop();
        });


        $("#recording-modal").on("hidden.bs.modal", function() {
            // console.log(player);
            // if (player) {
            //     player.dispose();
            // }
            // console.log('recording modal closed');
        });

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
                form.append(custom_field_file, file, "map_image.png");
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
