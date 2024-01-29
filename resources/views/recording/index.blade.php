@extends('layouts.app')
@section('title', 'Settings')
@section('section')
<div class="card shadow-none">
    <div class="card-body d-flex align-items-center justify-content-between">
        <h3 class="card-title fw-semibold">{{ $title }} (10 / 25) </h3>
        <!-- <a href="#" class="btn btn-primary"> New Video </a> -->
        <div class="dropdown">
            <button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                New Video
            </button>
            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                <button class="dropdown-item text-dark" href="#" type="button" class="btn btn-primary" data-toggle="modal" data-target="#recording-modal"> <i class="fa fa-video-camera" aria-hidden="true"></i> &nbsp;&nbsp; Record a Video</button>
            </div>
        </div>
    </div>
</div>
<div class="container-fluid">
    <ul class="nav nav-tabs " id="myTab" role="tablist">
        <li class="nav-item">
            <a class="nav-link" id="videos-tab" data-toggle="tab" href="#videos" role="tab" aria-controls="videos" aria-selected="false">Videos</a>
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
                @foreach($recordings as $recording)
                <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                    <div class="card">
                        <video loop muted playsinline poster="https://via.placeholder.com/600x400" class="card-img-top">
                            <source src="https://picsum.photos/600/400" type="video/mp4">
                            <!-- Fallback image in case the video fails to load -->
                            <img src="https://via.placeholder.com/600x400" alt="Fallback Image">
                        </video>
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <h5 class="card-title">{{ $recording->title }}</h5>
                                <div class="dropdown d-inline-block ml-2">
                                    <a href="#" class="dropdown-toggle" role="button" id="action-buttons" data-toggle="dropdown" aria-expanded="false" data-bs-toggle="actions">
                                        <i class="fas fa-ellipsis-h"></i>
                                    </a>
                                    <div class="dropdown-menu" aria-labelledby="action-buttons">
                                        <a class="dropdown-item" href="#">Action 1</a>
                                        <a class="dropdown-item" href="#">Action 2</a>
                                        <a class="dropdown-item" href="#">Action 3</a>
                                    </div>
                                </div>
                            </div>
                            <p class="card-text">Some quick example text to build on the card title and make up the bulk of the card's content.</p>
                            <div class="">
                                <button class="btn btn-primary" data-bs-toggle="tooltip" title="Share"><i class="fa fa-user-plus" aria-hidden="true"></i></button>
                                <button class="btn btn-secondary" data-bs-toggle="tooltip" title="Copy link"><i class="fa fa-clone" aria-hidden="true"></i></button>
                            </div>
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
<div class="modal fade" id="recording-modal" tabindex="-1" role="dialog" aria-labelledby="recording-modal-title" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLongTitle">Modal title</h5>
                <button type="button" class="btn btn-danger close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="inputSelector">
                    <label>Select video input: </label>
                    <select id="video"></select>
                </div>
                <div class="inputSelector">
                    <label>Select video input: </label>
                    <select id="audio"></select>
                </div>
                <!-- My Video -->
                <video id="myVideo" playsinline class="video-js vjs-default-skin"></video>

                <button type="button" class="btn btn-primary" data-dismiss="modal">start Recording</button>
                <!--<button type="button" class="btn btn-primary">Screen Recording</button>
                <button type="button" class="btn btn-danger">Screen Sharing with self video</button> -->
            </div>
            <!-- <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary">Save changes</button>
            </div> -->
        </div>
    </div>
</div>
@endsection
@section('js')

<script>
    var devices, deviceId;
    var options = {
        controls: true,
        width: 320,
        height: 240,
        fluid: false,
        bigPlayButton: false,
        controlBar: {
            volumePanel: false
        },
        plugins: {
            record: {
                audio: false,
                video: true,
                screen: true,
                maxLength: 20,
                displayMilliseconds: false,
                debug: true
            }
        }
    };
    var inputSection = document.getElementsByClassName('inputSelector')[0];

    // apply some workarounds for certain browsers
    applyVideoWorkaround();

    var player = videojs('myVideo', options, function() {
        // print version information at startup
        var msg = 'Using video.js ' + videojs.VERSION +
            ' with videojs-record ' + videojs.getPluginVersion('record') +
            ' and recordrtc ' + RecordRTC.version;
        videojs.log(msg);
    });

    // enumerate devices once
    player.one('deviceReady', function() {
        console.log('enumerate devices');
        player.record().enumerateDevices();
    });

    player.on('enumerateReady', function() {
        devices = player.record().devices;
        console.log('available devices', devices);

        // handle selection changes
        var videoSelector = document.getElementById('video');
        var inputSelector = document.getElementById('audio');
        // videoSelector.addEventListener('change', changeVideoInput);
        // inputSelector.addEventListener('change', changeVideoInput);

        // populate select options
        var deviceInfo, option, i;
        for (i = 0; i !== devices.length; ++i) {
            deviceInfo = devices[i];
            option = document.createElement('option');
            option.value = deviceInfo.deviceId;
            if (deviceInfo.kind === 'videoinput') {
                console.info('Found video input device: ', deviceInfo.label);
                option.text = deviceInfo.label || 'input device ' +
                    (inputSelector.length + 1);
                inputSelector.appendChild(option);
            }
        }

        if (inputSelector.length == 0) {
            // no output devices found, disable select
            option = document.createElement('option');
            option.text = 'No video input devices found';
            option.value = undefined;
            inputSelector.appendChild(option);
            inputSelector.disabled = true;
            console.warn(option.text);
        } else {
            console.info('Total video input devices found:', inputSelector.length);
        }

        // show input selector section
        inputSection.style.display = 'block';
    });




    // error handling
    player.on('enumerateError', function() {
        console.warn('enumerate error:', player.enumerateErrorCode);
    });

    player.on('deviceError', function() {
        console.warn('device error:', player.deviceErrorCode);
    });

    player.on('error', function(element, error) {
        console.error(error);
    });

    // user clicked the record button and started recording
    player.on('startRecord', function() {
        console.log('started recording!');
    });

    // user completed recording and stream is available
    player.on('finishRecord', function() {
        // the blob object contains the recorded data that
        // can be downloaded by the user, stored on server etc.
        console.log('finished recording: ', player.recordedData);
    });

    function changeVideoInput(event, type) {
        var label = event.target.options[event.target.selectedIndex].text;
        deviceId = event.target.value;

        try {
            // change video input device
            if (type == 'audio') {
                player.record().setAudioInput(deviceId);
            }

            if (type == 'video') {
                player.record().setVideoInput(deviceId);
            }

            console.log(`Changed ${type} input to '" + label + "' (deviceId: " + deviceId + ")`);
        } catch (error) {
            console.warn(error);
            // jump back to first output device in the list as it's the default
            event.target.selectedIndex = 0;
        }
    }
</script>

@endsection