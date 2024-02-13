<div class="modal fade" id="recording-modal" tabindex="-1" role="dialog" aria-labelledby="recording-modal-title"
    aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="new-recording">Recorder</h5>
                <button type="button" class="btn btn-danger close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row selection_dropdown">
                    <div class="col-md-6 video_selector" hidden>
                        <label for="video-select">Select Video Device:</label>
                        <select id="video-select" class="form-control">
                            <option value="">Select Video device</option>
                        </select>
                    </div>
                    <div class="col-md-6 audio_selector" hidden>
                        <label for="audio-select">Select Audio Device:</label>
                        <select id="audio-select" class="form-control">
                            <option value="">Select audio device</option>
                        </select>
                    </div>
                </div>

                <!-- My Video -->
                <div class="recording" style="width: 100%;"></div>

                <div class="mt-2"></div>
                <div class="control-buttons mt-2">
                    <button type="button" role="button" class="btn btn-primary mt-4 start_recording" hidden><i
                            class="fa fa-play"></i>
                        &nbsp;&nbsp; Start</button>

                    <button type="button" role="button" class="btn btn-secondary mt-4 pause_recording" hidden><i
                            class="fa fa-pause"></i>
                        &nbsp;&nbsp; Pause</button>

                    <button type="button" role="button" class="btn btn-info mt-4 resume_recording" hidden><i
                            class="fa fa-play"></i>
                        &nbsp;&nbsp; Resume </button>

                    <button type="button" role="button" class="btn btn-danger mt-4 stop_recording" hidden><i
                            class="fa fa-stop"></i>
                        &nbsp;&nbsp; Stop </button>
                </div>

                <button type="button" role="button" class="btn btn-primary mt-4 restart_recording save_recording_btn"
                    data-status="draft">Restart
                    Recording</button>
                <button type="button" role="button" class="btn btn-primary mt-4 save_video save_recording_btn"
                    data-status="draft">Save
                    Draft</button>
                <button type="button" role="button" class="btn btn-primary mt-4 save_video save_recording_btn"
                    data-status="publish">Publish
                    Video</button>
            </div>
        </div>
    </div>
</div>
