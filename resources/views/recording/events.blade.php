<script>
    function keepStreamActive(stream) {
        var video = document.createElement("video");
        video.muted = true;
        video.srcObject = stream;
        video.style.display = "none";
        (document.body || document.documentElement).appendChild(video);
    }

    function addStreamStopListener(stream, callback) {
        stream.addEventListener(
            "ended",
            function() {
                callback();
                callback = function() {};
            },
            false
        );
        stream.addEventListener(
            "inactive",
            function() {
                callback();
                callback = function() {};
            },
            false
        );
        stream.getTracks().forEach(function(track) {
            track.addEventListener(
                "ended",
                function() {
                    callback();
                    callback = function() {};
                },
                false
            );
            track.addEventListener(
                "inactive",
                function() {
                    callback();
                    callback = function() {};
                },
                false
            );
        });
    }


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

    function downloadRecord(blob) {
        const url = URL.createObjectURL(blob);
        let a = document.createElement("a");
        a.style.display = "none";
        a.href = url;
        a.download = "video.webm";
        a.click();
        a.remove();
        URL.revokeObjectURL(url);
    }

    $('#video-select').change(function(e) {
        e.preventDefault();
        let src = $(this).val() ?? '';

        setSrc(src, 1);

    });

    function setSrc(src, type = 1) {
        let pxt = player.record();
        if (player_face && recordWithFace) {

            if (type == 2) {
                try {
                    let pxt2 = player_face.record();
                    if (src == '') {
                        pxt2.loadOptions({
                            audio: false
                        })
                    } else {
                        pxt2.setAudioInput(src);
                    }

                } catch (error) {

                }
            } else {
                pxt = player_face.record();
            }
        }
        if (type == 1) {
            if (src == '') {
                pxt.record().loadOptions({
                    video: false
                })
            } else {
                pxt.setVideoInput(src);
            }

        } else {
            if (currentInstance == 'video') {
                if (src == '') {
                    pxt.record().loadOptions({
                        audio: false
                    })
                } else {
                    pxt.setAudioInput(src);
                }

            }

        }
    }

    $('#audio-select').change(function(e) {
        e.preventDefault();
        startRecord($(this).val() ?? null);
        let src = $(this).val() ?? '';
        setSrc(src, 2);


    });

    $('.start_recording').click(function(e) {
        e.preventDefault();

        if (currentInstance == 'screen' && !player.record()._deviceActive) {
            player.record().getDevice();
        } else {
            hideControls(true);
            $('.restart_recording').show();
            // $('#video-select, #audio-select').prop('disabled',
            // 'disabled'); // Disable the video select dropdown
            player.record().start();
        }

    });

    $('.pause_recording').click(function(e) {
        e.preventDefault();
        hideControls(true);
        $('.stop_recording, .resume_recording').show();
        player.record().pause();
        if (recorder) {
            recorder.pauseRecording();
        }
    });

    $('.resume_recording').click(function(e) {
        e.preventDefault();
        hideControls(true);
        $('.stop_recording, .pause_recording').show();
        player.record().resume();
        if (recorder) {
            recorder.resumeRecording();
        }
    });

    function stopRecord() {
        hideControls(true);
        // $('.start_recording').show();
        $('.save_recording_btn').addClass('d-flex');
        setTimeout(function() {
            $('.restart_recording').hide();
        }, 500);
    }
    $('.stop_recording').click(function(e) {
        e.preventDefault();


        player.record().stop();
    });

    let isRestart = false;

    $('.restart_recording').click(function(e) {
        e.preventDefault();
        isRestart = true;
        player.record().stop();
        hideControls(true);
        //$('.start_recording').show();
        $('.save_video,  .save_recording_btn').hide();
        setTimeout(function() {
            player.record().start();
        }, 2000);

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

    // function mergeVideos(videoElement1, videoElement2) {

    //     const merger = new VideoStreamMerger();

    //     const canvas = document.createElement('canvas');
    //     const ctx = canvas.getContext('2d');
    //     canvas.width = videoElement1.videoWidth + videoElement2.videoWidth;
    //     canvas.height = Math.max(videoElement1.videoHeight, videoElement2.videoHeight);

    //     const mergedVideoElement = document.createElement('video');
    //     mergedVideoElement.setAttribute('playsinline', '');
    //     mergedVideoElement.setAttribute('class', 'video-js vjs-default-skin mt-4 h-full w-100');

    //     merger.result = mergedVideoElement.captureStream();

    //     // Add the first video element to merger
    //     merger.addMediaElement(videoElement1.id, videoElement1, {
    //         x: 0,
    //         y: 0,
    //         width: videoElement1.videoWidth,
    //         height: videoElement1.videoHeight,
    //         mute: false // You can set this to true if needed
    //     });

    //     // Add the second video element to merger
    //     merger.addMediaElement(videoElement2.id, videoElement2, {
    //         x: videoElement1.videoWidth, // Position it next to the first video
    //         y: 0,
    //         width: videoElement2.videoWidth,
    //         height: videoElement2.videoHeight,
    //         mute: false // You can set this to true if needed
    //     });

    //     // Render the merged stream to the canvas
    //     merger.start();
    //     // merger.canvas = canvas;
    //     // merger.ctx = ctx;

    //     // Draw the merged stream to the canvas
    //     function drawCanvas() {
    //         ctx.drawImage(videoElement1, 0, 0, videoElement1.videoWidth, videoElement1.videoHeight);
    //         ctx.drawImage(videoElement2, videoElement1.videoWidth, 0, videoElement2.videoWidth,
    //             videoElement2.videoHeight);
    //         requestAnimationFrame(drawCanvas);
    //     }

    //     // Start drawing the canvas
    //     drawCanvas();

    //     return merger;
    // }

    // function combineImages(imageSrc1, imageSrc2) {
    //     return new Promise((resolve, reject) => {
    //         // Create two image objects
    //         const img1 = new Image();
    //         const img2 = new Image();

    //         // Load the first image
    //         img1.onload = function() {
    //             // Load the second image
    //             img2.onload = function() {
    //                 // Create a canvas element
    //                 const canvas = document.createElement('canvas');
    //                 const ctx = canvas.getContext('2d');

    //                 // Set canvas dimensions to accommodate both images
    //                 canvas.width = Math.max(img1.width, img2.width);
    //                 canvas.height = img1.height + img2.height;

    //                 // Draw the first image onto the canvas
    //                 ctx.drawImage(img1, 0, 0);

    //                 // Draw the second image on top of the first one
    //                 ctx.drawImage(img2, 0, img1.height);

    //                 // Convert canvas content to a blob
    //                 canvas.toBlob(blob => {
    //                     resolve(blob);
    //                 }, 'image/png');
    //             };
    //             img2.src = imageSrc2;
    //         };
    //         img1.src = imageSrc1;
    //     });
    // }

    // function getPoster() {
    //     console.log('getting poster');
    //     captureFirstFrame(document.querySelector(
    //         '#my-video video')).then(t => {
    //         console.log(t);
    //         video_recorder.poster = t;
    //     });
    // }

    // function displayBlob(blob) {
    //     // Create an image element
    //     const img = document.createElement('img');
    //     // Set the src attribute to a data URL representing the Blob
    //     img.src = URL.createObjectURL(blob);
    //     // Append the image element to the document body or any other container element
    //     document.body.appendChild(img);
    // }
</script>