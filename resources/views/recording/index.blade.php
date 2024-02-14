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
            <h3 class="card-title fw-semibold">{{ $title }} ({{ $recCount }} / {{ $limit }})
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
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
    {{-- <script src="{{ asset('js/summernote.js') }}"></script> --}}
    @include('recording.summernote')
    {{-- <script src="https://cdn.jsdelivr.net/npm/video-stream-merger@4.0.1/dist/video-stream-merger.min.js"></script> --}}
    <script src="{{ asset('js/video-stream-merger.js') }}"></script>

    @include('partials.datatable-js')

    <script>
        var player = null;
        let player_face = null;

        let blobs = {
            screen: null,
            face: null
        };

        let is_company = `{{ is_company() }}` == '0';

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

            loadingStart();
            $('.save_video, .restart_recording, .save_recording_btn').hide();
            hideControls();

            var video_recorder = {
                poster: "",
                video: null,
                status: 'draft',
                posterUrl: null,
                videoUrl: null,
            };


            // let sr = document.createElement('script');
            // sr.src = 'https://cdn.jsdelivr.net/npm/video-stream-merger@4.0.1/dist/video-stream-merger.min.js';
            // document.head.append(sr);


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

            let maxLength = "{{ $user->plan->recording_minutes_limit }}" * 60;

            var pipEnabled = false;
            var pipStatusMsg;

            if (!('pictureInPictureEnabled' in document)) {
                pipStatusMsg = 'The Picture-in-Picture API is not available.';
            } else if (!document.pictureInPictureEnabled) {
                pipStatusMsg = 'The Picture-in-Picture API is disabled.';
            } else {
                pipEnabled = true;
            }

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
                        pip: pipEnabled,
                        video: true,
                        maxLength: maxLength,
                        displayMilliseconds: false,
                        // debug: true,
                        muted: false
                    }
                }
            };

            let video_screen = {
                controls: true,
                controlBar: {
                    fullscreenToggle: true,
                    volumePanel: true,
                    customControlSpacer: true
                },
                plugins: {
                    record: {
                        pip: pipEnabled,
                        video: true,
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

            function hideControls(param = true) {
                var elements = $('.start_recording, .pause_recording, .resume_recording, .stop_recording');
                if (param) {
                    elements.hide();
                } else {
                    elements.show();
                }
            }
            // to get user permission
            function init_perm(instance = null) {
                navigator.mediaDevices.getUserMedia({
                        video: true,
                        audio: true
                    })
                    .then(stream => {
                        hideControls();
                        console.log('getUserMediaDictionary');
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
                                    `<video id="my-video-face" hidden playsinline class="video-js vjs-default-skin mt-4 h-full w-100" ></video>`
                                );

                                setTimeout(function() {
                                    player_face = videojs('my-video-face', video_screen, function() {
                                        // // console.log('videojs-record initialized!');
                                    });

                                    player_face.ready(function() {});

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

                                }, 1000);
                            }

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

                                applyAudioWorkaround(instance);
                                applyVideoWorkaround(instance);
                                // initialize video js

                                player = videojs('my-video', instance, function() {
                                    $('.start_recording').show();
                                });

                                // hide record button
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

                                // user clicked the record button and started recording

                                player.on('startRecord', function() {
                                    $('.stop_recording, .pause_recording').show();
                                    $('.start_recording').hide();
                                    $('.save_recording_btn').hide();
                                    $('.selection_dropdown').hide();

                                    if (player_face) {
                                        var videoDeviceId = $('#video-select').val();

                                        $('.vjs-icon-video-perm')
                                            .trigger('click');
                                        // // console.log(videoDeviceId);
                                        if (videoDeviceId != '') {
                                            player_face.record().setVideoInput(videoDeviceId);

                                        }
                                        setTimeout(function() {
                                            player_face.record().start();
                                            setTimeout(function() {
                                                //player_face.exitPictureInPicture()
                                                player_face
                                                    .requestPictureInPicture();
                                                // $('.vjs-icon-picture-in-picture-start')
                                                //     .trigger(
                                                //         'click');
                                            }, 500);
                                        }, 1000);
                                    }

                                    setTimeout(function() {
                                        $('.vjs-hidden.vjs-icon-replay').removeClass(
                                            'vjs-hidden');
                                    }, 500);

                                    if ($('.custom_play').length == 0) {
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

                                    if (player_face) {
                                        if (player_face.pictureInPictureElement !== null) {
                                            player_face.exitPictureInPicture();
                                        }
                                        player_face.record().stop();
                                    }

                                    $('.save_video, .restart_recording, .save_recording_btn')
                                        .show();
                                    $('.start_recording, .pause_recording, .resume_recording, .stop_recording')
                                        .hide();
                                    $('.custom.vjs-icon-pause').remove();

                                    // capturing the first frame of the stream
                                    captureFirstFrame(document.querySelector(
                                        '#my-video #my-video_html5_api')).then(t => {
                                        video_recorder.poster = t;
                                    });

                                    blobs.screen = player.recordedData;
                                    // if (instance == 'screen') {
                                    blobs.face = player_face.recordedData;
                                    setTimeout(function() {
                                        blobs.face = player_face.recordedData;

                                        if (blobs.face != '' || blobs.face != null) {
                                            loadingStart('Merging...');
                                            console.log(blobs);

                                            var player_video_el =
                                                createVideoElementFromBlob(player
                                                    .recordedData,
                                                    'player');
                                            var player_face_video_el =
                                                createVideoElementFromBlob(player_face
                                                    .recordedData,
                                                    'player_face');


                                            console.log(player_video_el,
                                                player_face_video_el,
                                                player_video_el.id,
                                                player_face_video_el.id);

                                            // mergeing 2 elemtns media
                                            var merge_data = mergeVideos(
                                                player_video_el,
                                                player_face_video_el);

                                            console.log(merge_data.result);
                                            downloadMediaStream(merge_data.result)
                                            // saving blob to database using this
                                            loadingStop();
                                        }

                                        // set the video blob to the video recorder4
                                        video_recorder.video = player.recordedData;

                                        // creating video elements from blobs
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

            function createVideoElementFromBlob(blobData, id, display = false) {
                const blob = new Blob([blobData], {
                    type: 'video/mp4'
                });
                const videoElement = document.createElement('video');
                videoElement.src = URL.createObjectURL(blob);
                videoElement.setAttribute('id', id);
                videoElement.setAttribute('autoplay', false);
                videoElement.setAttribute('controls', false);
                if (!display) {
                    videoElement.style.display = 'block';
                } else {
                    videoElement.style.display = 'none';
                }
                document.body.appendChild(videoElement); // Append to the document body or another container
                return videoElement;
            }

            function downloadBlob(blob) {
                // Create a blob URL from the blob object
                var blobUrl = URL.createObjectURL(blob);

                // Create a temporary link element
                var link = document.createElement('a');
                link.href = blobUrl;

                // Generate a unique filename based on the current timestamp
                var fileName = 'merged_' + Date.now() + '.mp4';
                link.download = fileName; // Set the filename for download

                // Append the link to the body
                document.body.appendChild(link);

                // Programmatically click the link to trigger the download
                link.click();

                // Cleanup
                URL.revokeObjectURL(blobUrl);
                document.body.removeChild(link);
            }

            function downloadMediaStream(mediaStream) {
                // Create a new MediaRecorder
                const mediaRecorder = new MediaRecorder(mediaStream);
                const recordedChunks = [];

                // Listen to dataavailable event
                mediaRecorder.addEventListener('dataavailable', function(event) {
                    recordedChunks.push(event.data);
                });

                // When recording is stopped, create a Blob and initiate download
                mediaRecorder.addEventListener('stop', function() {
                    const blob = new Blob(recordedChunks, {
                        type: 'video/webm'
                    });
                    downloadBlob(blob);
                });

                // Start recording
                mediaRecorder.start();

                // Stop recording after a certain duration (adjust the duration as needed)
                setTimeout(() => {
                    mediaRecorder.stop();
                }, 5000); // Stop after 5 seconds, for example
            }



            function mergeVideos(videoElement1, videoElement2) {

                const merger = new VideoStreamMerger();

                const canvas = document.createElement('canvas');
                const ctx = canvas.getContext('2d');
                canvas.width = videoElement1.videoWidth + videoElement2.videoWidth;
                canvas.height = Math.max(videoElement1.videoHeight, videoElement2.videoHeight);

                const mergedVideoElement = document.createElement('video');
                mergedVideoElement.setAttribute('playsinline', '');
                mergedVideoElement.setAttribute('class', 'video-js vjs-default-skin mt-4 h-full w-100');

                merger.result = mergedVideoElement.captureStream();

                // Add the first video element to merger
                merger.addMediaElement(videoElement1.id, videoElement1, {
                    x: 0,
                    y: 0,
                    width: videoElement1.videoWidth,
                    height: videoElement1.videoHeight,
                    mute: false // You can set this to true if needed
                });

                // Add the second video element to merger
                merger.addMediaElement(videoElement2.id, videoElement2, {
                    x: videoElement1.videoWidth, // Position it next to the first video
                    y: 0,
                    width: videoElement2.videoWidth,
                    height: videoElement2.videoHeight,
                    mute: false // You can set this to true if needed
                });

                // Render the merged stream to the canvas
                merger.start();
                merger.canvas = canvas;
                merger.ctx = ctx;

                // Draw the merged stream to the canvas
                function drawCanvas() {
                    ctx.drawImage(videoElement1, 0, 0, videoElement1.videoWidth, videoElement1.videoHeight);
                    ctx.drawImage(videoElement2, videoElement1.videoWidth, 0, videoElement2.videoWidth,
                        videoElement2.videoHeight);
                    requestAnimationFrame(drawCanvas);
                }

                // Start drawing the canvas
                drawCanvas();

                return merger;
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

            function saveRecording(video_recorder) {
                let formData = new FormData();
                formData.append('status', video_recorder.status);
                formData.append('poster', video_recorder.poster);
                formData.append('video', video_recorder.video);
                formData.append('posterUrl', video_recorder.posterUrl);
                formData.append('videoUrl', video_recorder.videoUrl);
                formData.append('_token', "{{ csrf_token() }}");

                fetch("{{ route('recordings.store') }}", {
                        method: "POST",
                        body: formData,
                    })
                    .then(response => response.json())
                    .then(data => {
                        // // console.log(data);
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: data.message,
                                showConfirmButton: false,
                                timer: 2000
                            });
                            location.reload();
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: data.message,
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.close();
                    });
            }

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

                saveRecording(video_recorder);
                loadingStop();
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
                $('.save_video, .restart_recording, .save_recording_btn').hide();
                player.record().start();
            });

            $('#submitData').click(async function(e) {
                e.preventDefault();

                // $(this).attr('disabled', 'disabled');

                try {
                    let tabactive = $('#sms-tab').hasClass('active') ? 'SMS' : 'Email';
                    let body = '';
                    let bodykey = '.sms-summernote';
                    if (tabactive == 'Email') {
                        bodykey = '.email-summernote';
                        body = $(bodykey).summernote('code');
                    } else {
                        body = $($(bodykey).summernote('code')).text();
                    }

                    let defChunks = 'chunks';
                    let action = $('[name="process1"]:checked').val();
                    // $(this).data('action') ?? 'direct';
                    // // console.log(action);
                    let share = $('[name="share"]:checked').val();
                    let contacts = $('#contact-select').val();
                    let tags = $('#tag-select').val();
                    if (contacts.length == 0 && share == 'contacts') {
                        show_error('Please select at least one contact');
                        return;
                    } else if (tags.length == 0 && share == 'tags') {
                        show_error('Please select at least one tag');
                        return;
                    }

                    if (body == '' || body == '<p></p>' || body == '<p><br></p>') {
                        show_error('Please enter content to send');
                        return;
                    }

                    loadingStart('Processing...');

                    let contactChunks = [];
                    let formData = new FormData();
                    if (action == defChunks) {
                        contactChunks = chunkArray(contacts, 50);
                    } else {
                        formData.append('contacts', contacts);
                    }

                    formData.append('tags', tags);
                    formData.append('body', body);
                    formData.append('subject', $('#subject').val());
                    formData.append('share', share);
                    formData.append('type', tabactive);
                    formData.append('recording_id', $('#recording_id').val());
                    formData.append('_token', "{{ csrf_token() }}");

                    const formDataObject = {};
                    formData.forEach((value, key) => {
                        formDataObject[key] = value;
                    });
                    // // console.log(contactChunks);
                    console.time();
                    if (action == defChunks) {

                        let worker = new Worker('./js/chunksWorker.js');

                        worker.postMessage({
                            chunks: contactChunks,
                            data: {
                                start: 0,
                                end: contactChunks.length,
                                // callback: processData,
                                formDataObject: formDataObject,
                                url: "{{ route('ghl.sendData') }}",
                                contacts: "{{ route('ghl.contacts') }}"
                            }
                        });

                        worker.onmessage = function(e) {
                            let data = e.data;
                            if (data.status == 'progress') {
                                data.contact = (data.start * 50) + (data.index + 1);
                                let message = `Sharing with - Contact ${data.contact}`;
                                if (data.tag != '') {
                                    message += `- Tag ${data.tag}`;
                                }
                                loadingStart(
                                    message
                                );
                            } else {
                                $('#contact-select').val('').trigger('change');
                                $('#tag-select').val('').trigger('change');
                                $('#subject').val('');
                                $(bodykey).summernote('code', '');
                                $('#share-modal .close.btn.btn-danger').trigger('click');
                                console.timeEnd();
                                loadingStop();
                            }
                        };
                        loadingStop();
                    } else {
                        processDataShare(formData).then(x => {
                            console.timeEnd();
                            loadingStop();
                            // // console.log(x);
                        }).catch(t => {
                            // // console.log(t);
                            loadingStop();
                        });
                    }
                } catch (error) {
                    // // console.log(error);
                    loadingStop();
                }
            });

            $("#contact-select").select2({
                ajax: {
                    url: "{{ route('ghl.contacts') }}",
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            q: params.term,
                            page: params.page
                        };
                    },
                    processResults: function(data, params) {
                        params.page = params.page || 1;
                        return data;
                    },
                    cache: true
                },
                placeholder: $("#contact-select").attr('placeholder'),
                allowClear: true,
                dropdownParent: $('#share-modal'),
                closeOnSelect: false,
                width: "100%",

            });

            $("#recording-modal").on("hidden.bs.modal", function() {
                // console.log(player);
                // if (player) {
                //     player.dispose();
                // }
                // console.log('recording modal closed');
            });
            loadingStop();
        });

        fetchData(1);
        getTags();

        function getTags() {
            $.ajax({
                type: "GET",
                url: "{{ route('ghl.tags') }}",
                success: function(response) {
                    if (response.success == true) {
                        // // console.log(response.data);
                        $('.tag-select').empty();
                        let html = '';;
                        $('.tag-select').html(
                            response?.data.map(element => {
                                if (element.name.trim() == '') {
                                    return '';
                                }
                                return `<option value="${element.name}">${element.name}</option>`;
                            }).join(''));

                        $('#tag-select').trigger('change');
                    }
                    initializeSelect2('#tag-select', '.tag_selector');
                }
            });
        }

        function processDataShare(formData) {

            return new Promise((resolve, reject) => {
                //$('#contact-select').val()
                $.ajax({
                    type: "POST",
                    url: "{{ route('ghl.sendData') }}",
                    data: formData,
                    // dataType: "dataType",
                    processData: false,
                    contentType: false,
                    success: function(response) {

                        resolve(response);
                    },
                    error: function(error) {

                        reject(error);
                    }
                });
            });
        }

        function fetchData(page = 1) {
            $.ajax({
                url: "{{ route('recording.data') }}",
                method: 'GET',
                data: {
                    page: page
                },
                success: function(response) {
                    if (response.data.data.length === 0 && page == 1) {
                        $('#recordings-container').html(
                            `<h3 class="card-title fw-semibold text-center no_recording">No recording available!</h3>`
                        );
                    } else {
                        renderData(response.data.data);
                        renderPagination(response.data);
                    }
                }
            });
        }

        function renderData(recordings) {
            var html = '';
            $.each(recordings, function(index, recording) {
                let title = recording?.title_dt; //${formatTimestamp(recording.title, 'M d, Y')};
                let enc_id = recording.enc_id;
                let linkurl = `{{ route('recordings.show', 'link') }}`.replaceAll('link', enc_id);
                let deleteurl = `{{ route('recordings.destroy', 'link') }}`.replaceAll('link', recording.enc_id);
                // // console.log(deleteurl);
                html +=
                    `
                <div class="col-lg-3 col-md-4 col-sm-6 mb-4 d-flex align-items-stretch">
                        <div class="card">
                            <div class="header" style="max-height: 200px; overflow: hidden;">
                                <a href="${recording.short_url}" target="_blank">
                                    <img src="${recording.poster_url || 'https://via.placeholder.com/600x400'}" alt="${title}" class="card-img-top" style="width: 100%; height: auto;">
                                </a>
                            </div>
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="card-title">${title}</h5>
                                    <div class="dropdown">
                                        <a href="javascript:void(0)" role="button" id="action-buttons" data-toggle="dropdown" aria-expanded="false" data-bs-toggle="actions">
                                            <i class="fas fa-ellipsis-h"></i>
                                        </a>
                                        <div class="dropdown-menu" aria-labelledby="action-buttons">
                                            <a class="dropdown-item" href="javascript:void(0)" data-toggle="modal" data-target="#edit-recording-modal" data-title="${title}" data-description="${recording.description}" data-url="${linkurl}">Edit</a>
                                            <a class="dropdown-item copy-link" href="javascript:void(0)" data-bs-toggle="tooltip" data-link="${recording.short_url ??  recording.file_url}" title="Copy link" onclick="copyLink(this)">Copy</a>
                                            <a class="dropdown-item" href="javascript:void(0)" data-bs-toggle="tooltip" title="Delete Record" onclick='deleteRecordAjax("${deleteurl}")'>Delete</a>
                                        </div>
                                    </div>
                                </div>
                                <p class="card-text">${recording.description}</p>
                                <div class="d-flex justify-content-between">
                                    <button class="btn btn-danger share" data-bs-toggle="tooltip" data-value="${recording.id}" data-toggle="modal" data-target="#share-modal" data-title="${title}" data-text="${recording.short_url ?? recording.file_url}" data-short="${recording.short_url ?? linkurl}">
                                        <i class="fa fa-share"></i> Share
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;

            });
            $('#recordings-container').html(html);
        }

        function processChunks(chunks, data) {
            return new Promise((resolve, reject) => {
                let processNextChunk = (index) => {
                    if (index >= chunks.length) {

                        resolve(chunks);
                        return;
                    }

                    let chunk = chunks[index];
                    let promises = chunk.map((element, ind) => {
                        data.formData.set('contacts', element);
                        return data.callback(data.formData, ind).then(x => {
                            chunks[index][ind].response = x;
                        }).catch(error => {
                            let errorMessage = error.responseJSON.message;
                            $('.alert-message').html(errorMessage).show();
                        });
                    });

                    Promise.all(promises).then(() => {
                        setTimeout(() => {
                            processNextChunk(index + 1);
                        }, 2000);
                    });
                };

                processNextChunk(data.start ?? 0);
            });
        }

        function processChunks1(chunks, data) {
            let start = data.start;
            // console.log(chunks.length, start);
            if (start >= chunks.length) {

                if (typeof data.finalize == 'function') {
                    //data.finalize(chunks);
                }
                // console.log('done', chunks);
                loadingStop();
                return chunks;
            }
            let chunk = chunks[start] ?? [];
            chunk.forEach((element, ind) => {
                // console.log(element);
                data.formData.set('contacts', element);
                // console.log(data.formData);
                try {
                    $.ajax({
                        type: "POST",
                        url: "{{ route('ghl.sendData') }}",
                        data: data.formData,
                        // dataType: "dataType",
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            // console.log(response);
                            chunks[start][ind].response = response;
                            // resolve(response);
                        },
                        error: function(error) {
                            //reject(error);
                        }
                    });
                } catch (error) {
                    // console.log(error);
                }

                if (ind == chunk.length - 1) {
                    data.start = data.start + 1;
                    processChunks1(chunks, data);
                }
            });
        }

        function chunkArray(arr, chunkSize) {
            const chunkedArrays = [];
            for (let i = 0; i < arr.length; i += chunkSize) {
                chunkedArrays.push(arr.slice(i, i + chunkSize));
            }
            return chunkedArrays;
        }

        function addDeviceToSelect(device, selectId, hideit = '') {
            var parent = document.querySelector('.' + selectId);
            var select = parent.querySelector('select');
            var option = document.createElement('option');
            option.value = device.deviceId;

            let already = select.querySelector('option[value="' + device.deviceId + '"]');
            if (!already) {
                option.text = device.label || 'Device ' + (select.options.length + 1);
                select.add(option);
            }
            try {
                if (hideit == 'video') {
                    select.querySelector('option[value=""]').setAttribute('hidden', "1")
                } else {
                    select.querySelector('option[value=""]').removeAttribute('hidden')
                }
            } catch (error) {

            }
            if (select.options.length > 1) {
                parent.removeAttribute('hidden');
            } else if (hideit == 'video') {
                //close modal
                show_error('No video device found');
            }
        }

        var table = $('#table').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            "order": [],
            ajax: "{{ route('sharelog.data') }}",
            columns: [{
                    data: 'DT_RowIndex',
                    // data: 'id',
                    name: 'id'
                },
                @foreach ($tableFields as $key => $value)
                    {
                        data: '{{ $key }}',
                        name: '{{ $key }}',
                        @if ($key === 'action' || $key === 'Action')
                            searchable: false,
                            orderable: false
                        @endif
                    },
                @endforeach
            ]
        });

        $('body').on('click', '#history-tab', function(e) {
            e.preventDefault();
            table.ajax.reload();
        });

        function deleteRecordAjax(url) {
            return new swal({
                title: 'Are you sure?',
                text: 'You will not be able to recover this record!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        type: 'DELETE',
                        url: url,
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(data) {
                            if (data.success == true) {
                                toastr.success(data.message);
                                setTimeout(() => {
                                    location.reload();
                                }, 1000);
                            } else {
                                toastr.error('Error Occured while deleteing record!');
                            }
                        },
                        error: function(error) {
                            let message = 'Network error';
                            if (error.responseJSON) {
                                message = error.responseJSON.message
                            }
                            // console.log(message);
                            toastr.error('Error Occured while deleteing record!');
                        }
                    });
                }
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
                    parentName: "Videos - Do not delete form",
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
    </script>
@endsection
