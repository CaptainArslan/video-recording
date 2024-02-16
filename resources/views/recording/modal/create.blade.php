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
            <video id="my_final_video" hidden autoplay="" playsinline="" class="h-full w-100 hide"></video>
            <div class="modal-body">
                <div class="main_recorder">

                        <div class="main ">
                            <div class="recording" style="width: 100%;"></div>

                            <div class="mt-2"></div>
                            <div class="buttons save_recording_btn d-flex">

                            <button type="button" role="button" class="btn btn-primary mt-4 save_video "
                                data-status="draft">Save
                                Draft</button>
                            <button type="button" role="button" class="btn btn-primary mt-4 save_video "
                                data-status="publish">Publish
                                Video</button>
                            </div>
                            <div class="control-buttons d-flex mt-2">
                                <button type="button" role="button" class="btn btn-primary mt-4 start_recording" ><i
                                        class="fa fa-play"></i>
                                    &nbsp;&nbsp; Start Recording</button>
                                <button type="button" role="button" class="btn btn-primary mt-4 restart_recording "
                                    data-status="draft">Restart
                                    Recording</button>
                                <button type="button" role="button" class="btn btn-secondary mt-4 pause_recording" ><i
                                        class="fa fa-pause"></i>
                                    &nbsp;&nbsp; Pause</button>
                                <button type="button" role="button" class="btn btn-info mt-4 resume_recording" ><i
                                        class="fa fa-play"></i>
                                    &nbsp;&nbsp; Resume </button>
                                <button type="button" role="button" class="btn btn-danger mt-4 stop_recording" ><i
                                        class="fa fa-stop"></i>
                                    &nbsp;&nbsp; Stop </button>
                            </div>
                        </div>
                        <div class="side_bar d-flex flex-column w-30">
                            <div class="selection_dropdown">
                                <div class=" audio_selector d-flex flex-column" hidden>
                                    <label for="audio-select">Select Audio Device:
                                        <canvas id="audioCanvas" width="250" height="50"></canvas>
                                    </label>
                                    <select id="audio-select" class="form-control">
                                        <option value="">No microphone</option>
                                    </select>
                                    <audio id="microphone" autoplay></audio>
                                </div>
                            </div>
                            <div class=" selection_dropdown">
                                <div class=" video_selector" hidden>
                                    <label for="video-select">Select Video Device:</label>
                                    <select id="video-select" class="form-control">

                                    </select>
                                </div>


                            </div>

                            <div class=" mt-1 selection_dropdown self_checkbox" hidden>
                                <div class="col-md-6">
                                    <div class="form-check face_input">
                                        <input class="form-check-input" type="checkbox" name="show_face" id="show_face" checked>
                                        <label class="form-check-label" for="show_face">
                                            Record Face
                                        </label>
                                    </div>
                                </div>
                            </div>

                        </div>
                </div>

                <!-- My Video -->

                {{--  --}}

            </div>
        </div>
    </div>
</div>
