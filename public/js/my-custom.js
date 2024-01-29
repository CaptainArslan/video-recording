
function loadingStart(title) {
    return Swal.fire({
        title: title ? title : "Loading",
        // closeOnEsc: false,
        timerProgressBar: true,
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        },
    });
}

function loadingStop(url = '') {
    swal.close();
    url && (window.location.href = url);
}

$('.confirm-delete').click(function (e) {
    e.preventDefault();

    alert('delete confirm');
    const id = $(this).data("id");
    const url = $(this).attr("href");

    Swal.fire({
        title: 'Are you sure?',
        text: 'You won\'t be able to revert this!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        // If user clicks "Yes"
        if (result.isConfirmed) {
            // Redirect to the URL for deletion
            window.location.href = url;
        }
    });
});

function deleteRecord(e, param) {
    e.preventDefault();

    alert('delete confirm');
    const id = $(param).data("id");
    const url = $(param).attr("href");

    Swal.fire({
        title: 'Are you sure?',
        text: 'You won\'t be able to revert this!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        // If user clicks "Yes"
        if (result.isConfirmed) {
            // Redirect to the URL for deletion
            window.location.href = url;
        }
    });
}