<div class="modal fade" id="share-modal" tabindex="-1" role="dialog" aria-labelledby="share-modalTitle" aria-hidden="true"
    data-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Share ( <span id="share-recording-heading"></span> ) </h5>
                <button type="button" class="btn btn-danger close-modal" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                {{-- tabs links --}}
                <ul class="nav nav-tabs share_tabs" id="myTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <a class="nav-link active" id="email-tab" data-bs-toggle="tab" href="#email" role="tab"
                            aria-controls="email" aria-selected="true">Email</a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="sms-tab" data-bs-toggle="tab" href="#sms" role="tab"
                            aria-controls="sms" aria-selected="false">SMS</a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="frame-tab" data-bs-toggle="tab" href="#frame" role="tab"
                            aria-controls="frame" aria-selected="false">Embed Code</a>
                    </li>
                </ul>

                <input type="hidden" id="recording_id" value=''>
                {{-- tabs --}}
                <div class="tab-content mt-4">
                    <div class="mt-2">
                        <div class="row share_btn share-select mt-3 mb-3">
                            <div class="btn-group" role="group" aria-label="Basic radio toggle button group">
                                <input type="radio" class="btn-check" value="contacts" name="share"
                                    id="contact-label" autocomplete="off" checked>
                                <label class="btn btn-outline-primary" for="contact-label"
                                    onclick="toogleCheckbox(this)">With Contacts</label>
                                <input type="radio" class="btn-check" value="tags" name="share" id="tag-label"
                                    autocomplete="off">
                                <label class="btn btn-outline-primary" for="tag-label"
                                    onclick="toogleCheckbox(this)">With
                                    Tags</label>
                            </div>

                        </div>
                        <div hidden>
                            <input type="radio" name="process1" value="direct">
                            <input type="radio" name="process1" value="chunks" checked>
                        </div>

                        <div class="row share_btn">
                            <div class="col-md-6 contact_selector">
                                <label for="contact-select">Select contacts:</label>
                                <select id="contact-select" placeholder="Choose Multiple Contacts"
                                    class="form-control contact-select " multiple="true">
                                </select>
                            </div>
                            <div class="col-md-6 tag_selector" style="display: none;">
                                <label for="tag-select">Select tags:</label>
                                <select id="tag-select" placeholder="Choose Multiple Tags"
                                    class="form-control tag-select .selection" multiple="true">
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- email --}}
                    <div class="tab-pane fade show active" id="email" role="tabpanel" aria-labelledby="email-tab">
                        <div class="row mt-2">
                            <div class="col-12">
                                <label for="subject">Subject</label>
                                <input type="text" class="form-control" name="video-subject" id="subject"
                                    placeholder="Default Subject Recording">
                            </div>
                            <div class="col-12">
                                <label for="video-select">Email</label>
                                <textarea class="form-control w-100 email-summernote" name="email" rows="3"></textarea>
                            </div>
                        </div>
                    </div>

                    {{-- sms tab --}}
                    <div class="tab-pane fade" id="sms" role="tabpanel" aria-labelledby="sms-tab">
                        <div class="row mt-2">
                            <div class="col-12">
                                <label for="video-select">SMS</label>
                                <textarea class="form-control w-100 sms-summernote" name="sms" rows="3"></textarea>
                            </div>
                        </div>
                    </div>

                    {{--  Embed code --}}
                    <div class="tab-pane fade" id="frame" role="tabpanel" aria-labelledby="frame-tab">
                        <div class="row mt-2">
                            <div class="col-12">
                                <label for="video-select">Copy Embed Code</label>
                                <textarea class="form-control w-100 emded_code" name="emded_code" rows="15" readonly></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <div class="col-12 mt-2 share_btn">
                    <button type="button" id="submitData" data-action="direct"
                        class="btn btn-primary">Share</button>
                </div>
            </div>
        </div>
    </div>
</div>
