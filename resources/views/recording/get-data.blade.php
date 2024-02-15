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
