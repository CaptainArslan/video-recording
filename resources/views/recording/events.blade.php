<script>
    $('#video-select').change(function(e) {
        e.preventDefault();
        player.record().setVideoInput($(this).val() ?? '');
    });

    $('#audio-select').change(function(e) {
        e.preventDefault();
        startRecord($(this).val() ?? null);
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


    function hideControls(param = true) {
        var elements = $('.start_recording, .pause_recording, .resume_recording, .stop_recording');
        if (param) {
            elements.hide();
        } else {
            elements.show();
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
        // merger.canvas = canvas;
        // merger.ctx = ctx;

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
</script>
