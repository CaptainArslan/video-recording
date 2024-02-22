<div class="modal fade" id="edit-recording-modal" tabindex="-1" role="dialog" aria-labelledby="edit-recording-modal-title"
    aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="title">Edit</h5>
                <button type="button" class="btn btn-danger close-modal" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="javascript:void(0)" method="POST" id="update-recording">
                    @csrf
                    @method('PUT')
                    {{-- alert messages --}}
                    <div class="alert alert-danger alert-message d-none" role="alert"> </div>
                    {{-- update form --}}
                    <div class="row">
                        <div class="col-md-12">
                            <label for="video-select">Title</label>
                            <input type="text" class="form-control" name="title" value="" required>
                        </div>
                        <div class="col-md-12 mt-2">
                            <label for="audio-select">Description</label>
                            <textarea name="description" class="form-control" id="" cols="10" rows="3"></textarea>
                        </div>
                    </div>

                    <button type="submit" role="button" class="btn btn-primary mt-4"
                        data-status="publish">update</button>
                </form>
            </div>
        </div>
    </div>
</div>
