<script>
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

    function fetchData(page = 1) {
        loadingStart();
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
                    $('#user_rec').html(response.data.total);
                    renderData(response.data.data);
                    renderPagination(response.data);
                }
                loadingStop();
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
            let statusurl = `{{ route('recording.status', 'link') }}`.replaceAll('link', recording.enc_id);
            // // console.log(deleteurl);
            // if (recording.file_url != null && recording.file_url != '') {
            html +=
                `
                <div class="col-lg-3 col-md-4 col-sm-6 mb-4 d-flex align-items-stretch">
                    <div class="card">
                        <div class="p-3 d-flex justify-content-between align-items-center">
                            <div class="heading ">
                                <h5 class="card-title">${title ?? 'Untitled'} </h5>
                                <span class="text-muted" style="font-size: smaller;">${formatDate(recording.created_at) ?? ''}  ( ${recording.status} )</span>
                            </div>
                            <div class="dropdown">
                                <a href="javascript:void(0)" role="button" id="action-buttons" data-toggle="dropdown" aria-expanded="false"
                                    data-bs-toggle="actions">
                                    <i class="fas fa-ellipsis-h"></i>
                                </a>
                                <div class="dropdown-menu" aria-labelledby="action-buttons">
                                    <a class="dropdown-item" href="javascript:void(0)" data-toggle="modal" data-target="#edit-recording-modal"
                                        data-title="${title}" data-description="${recording.description}" data-url="${linkurl}">Edit</a>
                                    <a class="dropdown-item copy-link" href="javascript:void(0)" data-bs-toggle="tooltip"
                                        data-link="${recording.short_url ??  recording.file_url}" title="Copy link"
                                        onclick="copyLink(this)">Copy</a>
                                    <a class="dropdown-item" href="javascript:void(0)" data-bs-toggle="tooltip" title="Delete Record"
                                        onclick='deleteRecordAjax("${deleteurl}")'>Delete</a>
                                        `;
            if (recording.status == 'draft') {
                html += `<a class="dropdown-item" href="javascript:void(0)" data-bs-toggle="tooltip" title="Delete Record" data-status="publish"
                                        onclick='statusRecordAjax(this, "${statusurl}")'>Publish</a>`
            } else {
                html += `<a class="dropdown-item" href="javascript:void(0)" data-bs-toggle="tooltip" title="Delete Record" data-status="draft"
                                        onclick='statusRecordAjax(this, "${statusurl}")'>Draft</a>`
            }
            html += `</div>
                            </div>
                        </div>
                        <div class="header" style="max-height: 250px; overflow: hidden;">
                            <a href="${recording.short_url}">
                                <img src="${recording.poster_url ?? 'https://via.placeholder.com/600x400'}" alt="${title}" class="card-img-top" style="width: 100%; height: 100%; object-fit: cover;">
                            </a>
                        </div>
                        <div class="card-body">
                            <p class="card-text">${limitDescription(recording.description, 50) ?? ''}</p>
                            <div class="d-flex justify-content-between">
                                <button class="btn btn-danger share" data-bs-toggle="tooltip" data-value="${recording.id}" data-toggle="modal" data-target="#share-modal" data-title="${title}" data-text="${recording.short_url ?? recording.file_url}" data-short="${recording.short_url ?? linkurl}" data-poster="${recording.poster_url ?? 'https://via.placeholder.com/600x400'}">
                                    <i class="fa fa-share"></i> Share
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                `;
            // }
        });
        $('#recordings-container').html(html);
    }

    function formatDate(dateString) {
        const date = new Date(dateString);
        const options = {
            year: 'numeric',
            month: 'short',
            day: '2-digit'
        };
        return date.toLocaleDateString('en-US', options);
    }


    function limitDescription(description, maxLength) {
        // Check if description is null or undefined
        if (!description) {
            return ''; // Return an empty string if description is null or undefined
        }

        // Check if the description length exceeds the maximum length
        if (description.length > maxLength) {
            // Return the first maxLength characters
            return description.substring(0, maxLength) + '...';
        } else {
            // Otherwise, return the original description
            return description;
        }
    }

    function addDeviceToSelect(device, selectId, hideit = '') {
        var parent = selectId;
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

        }
    }

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
</script>
