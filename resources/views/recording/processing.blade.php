<script>
    function saveRecording(video_recorder, modal = 'recording-modal') {
        let formData = new FormData();
        let xt = null;
        formData.append('status', video_recorder.status);

        xt = video_recorder?.posterUrl ?? '';
        if (xt == '') {

        }
        formData.append('poster', video_recorder.poster);
        formData.append('posterUrl', xt);
        xt = video_recorder?.videoUrl ?? '';
        if (xt == '') {

        }
        formData.append('video', video_recorder.video);
        formData.append('videoUrl', xt);
        xt = video_recorder?.faceUrl ?? '';
        if (xt == '') {
            formData.append('face', video_recorder.face);
        }
        formData.append('faceUrl', xt);

        xt = video_recorder?.videoOrgUrl ?? '';
        if (xt == '') {
            formData.append('video_orig', video_recorder.video_orig);
        }
        formData.append('videoOrgUrl', xt);
        formData.append('title', video_recorder.title);
        formData.append('_token', "{{ csrf_token() }}");
        fetch("{{ route('recordings.store') }}", {
                method: "POST",
                body: formData,
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: data.message,
                        showConfirmButton: false,
                        timer: 2000
                    });
                    $(`#${modal} .close-modal`).trigger('click');
                    // location.reload();
                    fetchData(1);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: data.message,
                        timer: 2000
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.close();
            });
    }

    // $('#submitData').click(function(e) {
    //     e.preventDefault();
    //     $(this).html('Loading...').addClass('disabled');
    //     loadingStart('Processing...');
    //     try {
    //         let tabactive = $('#sms-tab').hasClass('active') ? 'SMS' : 'Email';
    //         let body = '';
    //         let bodykey = '.sms-summernote';
    //         if (tabactive == 'Email') {
    //             bodykey = '.email-summernote';
    //             body = $(bodykey).summernote('code');
    //         } else {
    //             body = $($(bodykey).summernote('code')).text();
    //         }

    //         let defChunks = 'chunks';
    //         let action = $('[name="process1"]:checked').val();
    //         // $(this).data('action') ?? 'direct';
    //         // // console.log(action);
    //         let share = $('[name="share"]:checked').val();
    //         let contacts = $('#contact-select').val();
    //         let tags = $('#tag-select').val();
    //         if (contacts.length == 0 && share == 'contacts') {
    //             show_error('Please select at least one contact');
    //             return;
    //         } else if (tags.length == 0 && share == 'tags') {
    //             show_error('Please select at least one tag');
    //             return;
    //         }

    //         if (body == '' || body == '<p></p>' || body == '<p><br></p>') {
    //             show_error('Please enter content to send');
    //             return;
    //         }

    //         let contactChunks = [];
    //         let formData = new FormData();

    //         console.log(defChunks);
    //         if (action == defChunks) {
    //             contactChunks = chunkArray(contacts, 50);
    //         } else {
    //             formData.append('contacts', contacts);
    //         }

    //         formData.append('tags', tags);
    //         formData.append('body', body);
    //         formData.append('subject', $('#subject').val());
    //         formData.append('share', share);
    //         formData.append('type', tabactive);
    //         formData.append('recording_id', $('#recording_id').val());
    //         formData.append('_token', "{{ csrf_token() }}");

    //         const formDataObject = {};
    //         formData.forEach((value, key) => {
    //             formDataObject[key] = value;
    //         });
    //         // console.log(contactChunks);
    //         // console.time();

    //         if (action == defChunks) {
    //             loadingStart('Processing...');
    //             let worker = new Worker('./js/chunksWorker.js');
    //             worker.postMessage({
    //                 chunks: contactChunks,
    //                 data: {
    //                     start: 0,
    //                     end: contactChunks.length,
    //                     // callback: processData,
    //                     formDataObject: formDataObject,
    //                     url: "{{ route('ghl.sendData') }}",
    //                     contacts: "{{ route('ghl.contacts') }}"
    //                 }
    //             });

    //             worker.onmessage = function(e) {
    //                 loadingStart('Processing...');
    //                 let data = e.data;
    //                 console.log(data);
    //                 if (data.status == 'progress') {
    //                     data.contact = (data.start * 50) + (data.index + 1);
    //                     let message = `Sharing with - Contact ${data.contact}`;
    //                     if (data.tag != '') {
    //                         message += `- Tag ${data.tag}`;
    //                     }
    //                     // loadingStart(message);
    //                 } else {
    //                     $('#contact-select').val('').trigger('change');
    //                     $('#tag-select').val('').trigger('change');
    //                     $('#subject').val('');
    //                     $(bodykey).summernote('code', '');
    //                     $('#share-modal .close.btn.btn-danger').trigger('click');
    //                     console.timeEnd();
    //                     loadingStop();
    //                 }
    //                 loadingStop();
    //             };
    //         } else {
    //             processDataShare(formData).then(x => {
    //                 // console.timeEnd();
    //                 // loadingStop();
    //                 console.log(x);
    //             }).catch(t => {
    //                 console.log(t);
    //                 // loadingStop();
    //             }).finally(() => {
    //                 console.log('stop loading on function end');
    //                 loadingStop();
    //             });
    //         }
    //     } catch (error) {
    //         console.error(error);
    //         loadingStop();
    //     }
    //     setTimeout(() => {
    //         $(this).html('Share').removeClass('disabled');
    //     }, 2000);
    // });

    $('#submitData').click(function(e) {
        e.preventDefault();
        const $button = $(this);
        $button.html('Loading...').addClass('disabled');

        try {
            const swalWithHtml = Swal.mixin({
                title: "Loading",
                html: '<div id="swal-content">Processing...</div>',
                allowOutsideClick: false,
                showCancelButton: false,
                showConfirmButton: false,
                allowEscapeKey: false,
                didOpen: () => {
                    Swal.showLoading(); // Show the default loading spinner
                }
            });

            swalWithHtml.fire(); // Display the SweetAlert dialog

            let tabactive = $('#sms-tab').hasClass('active') ? 'SMS' : 'Email';
            let bodykey = tabactive == 'SMS' ? '.sms-summernote' : '.email-summernote';
            let body = $(bodykey).summernote('code').trim();

            let defChunks = 'chunks';
            let action = $('[name="process1"]:checked').val();
            let share = $('[name="share"]:checked').val();
            let contacts = $('#contact-select').val();
            let tags = $('#tag-select').val();

            if (contacts.length == 0 && share == 'contacts') {
                toastr.error('Please select at least one contact');
                $button.html('Share').removeClass('disabled');
                swalWithHtml.close(); // Close the SweetAlert dialog
                return;
            } else if (tags.length == 0 && share == 'tags') {
                toastr.error('Please select at least one tag.');
                $button.html('Share').removeClass('disabled');
                swalWithHtml.close();
                return;
            }

            if (body == '' || body == '<p></p>' || body == '<p><br></p>') {
                toastr.error('Please enter content to send.');
                $button.html('Share').removeClass('disabled');
                swalWithHtml.close(); // Close the SweetAlert dialog
                return;
            }


            // if ((contacts.length === 0 && share === 'contacts') || (tags.length === 0 && share ===
            //         'tags') ||
            //     body === '') {
            //     show_error('Please fill all required fields.');
            //     $button.html('Share').removeClass('disabled');
            //     swalWithHtml.close(); // Close the SweetAlert dialog
            //     return;
            // }

            let contactChunks = [];
            let formData = new FormData();
            if (action === defChunks) {
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

            if (action === defChunks) {
                let worker = new Worker('./js/chunksWorker.js');
                worker.postMessage({
                    chunks: contactChunks,
                    data: {
                        start: 0,
                        end: contactChunks.length,
                        formDataObject: formDataObject,
                        url: "{{ route('ghl.sendData') }}",
                        contacts: "{{ route('ghl.contacts') }}"
                    }
                });

                worker.onmessage = function(e) {
                    let data = e.data;
                    // console.log(data);
                    if (data.status == 'progress') {
                        data.contact = (data.start * 50) + (data.index + 1);
                        let message = `Sharing with - Contact ${data.contact}`;
                        if (data.tag != '') {
                            message += `- Tag ${data.tag}`;
                        }
                        // Update the HTML content of SweetAlert to show progress
                        document.getElementById('swal-content').innerHTML = message;
                    } else {
                        // Update the HTML content of SweetAlert to indicate completion
                        // document.getElementById('swal-content').innerHTML = 'Processing completed!';
                        show_success('Processing completed!');
                        // Clear form fields or reset form
                        $button.html('Share').removeClass('disabled');
                        $('#subject').val('');
                        $('#contact-select, #tag-select').val('').trigger('change');
                        $(bodykey).summernote('code', '');
                        $('#share-modal .close-modal').trigger('click');
                        setTimeout(() => {
                            swalWithHtml.close(); // Close the SweetAlert dialog after a delay
                        }, 2000);
                    }
                };

            } else {
                processDataShare(formData)
                    .then(x => {
                        console.log(x);
                        // Clear form fields or reset form
                        $('#subject').val('');
                        $('#contact-select, #tag-select').val('').trigger('change');
                        $(bodykey).summernote('code', '');
                        setTimeout(() => {
                            swalWithHtml.close(); // Close the SweetAlert dialog after a delay
                        }, 2000);
                    })
                    .catch(error => {
                        console.error(error);
                        show_error('An error occurred. Please try again.');
                        swalWithHtml.close(); // Close the SweetAlert dialog
                    });
            }
            setTimeout(() => {
                if (action !== defChunks) {
                    $button.html('Share').removeClass('disabled');
                    swalWithHtml.close(); // Close the SweetAlert dialog after a delay
                }
            }, 5000);
        } catch (error) {
            console.error(error);
            show_error('An error occurred. Please try again.');
            swalWithHtml.close(); // Close the SweetAlert dialog
        }
    });

    function processDataShare(formData) {
        return new Promise((resolve, reject) => {
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
