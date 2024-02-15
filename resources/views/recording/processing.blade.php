<script>
    function saveRecording(video_recorder) {
        let formData = new FormData();
        formData.append('status', video_recorder.status);
        formData.append('poster', video_recorder.poster);
        formData.append('video', video_recorder.video);
        formData.append('faceUrl', video_recorder.faceUrl);
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
</script>
