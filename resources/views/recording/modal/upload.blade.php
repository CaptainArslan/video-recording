<div class="modal fade" id="upload-video-modal" tabindex="-1" role="dialog" aria-labelledby="video-modal-title"
    aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Upload Video</h5>
                <button type="button" class="btn btn-danger close-modal" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="main ">
                    <div class="col-md-12">
                        <label for="video_title">Title</label>
                        <input type="text" class="form-control" name="video_title" value="" required="">
                    </div>

                    <div class="mt-2"></div>

                    <div class="col-md-12">
                        <label for="select-video">Please select video</label>
                        <input type="file" class="form-control" name="video" value="" id="select-video"
                            required="" accept="video/*">
                    </div>

                    <div class="mt-2"></div>

                    <video controls class="w-100" height="360" id="uploaded_video" autoplay="off">
                        <source src="" type="video/mp4,video/*">
                        Your browser does not support the video tag.
                    </video>

                    <div class="mt-2"></div>

                    <div class="d-flex">
                        <button type="button" role="button" class="btn btn-primary mt-4 upload_btn mx-1"
                            data-status="draft">Save
                            Draft</button>
                        <button type="button" role="button" class="btn btn-primary mt-4 upload_btn"
                            data-status="publish">Publish Video</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
