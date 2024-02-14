
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

function copyToClipboard(text) {
    const el = document.createElement('textarea');
    el.value = text;
    document.body.appendChild(el);
    el.select();
    document.execCommand('copy');
    document.body.removeChild(el);
    showSwal();
}

function showSwal(txt = "Copied to the clipboard.") {
    // let txt = "Copied to the clipboard.";
    try {
        toastr.success(txt);
    } catch (error) {
        Swal.fire({
            title: 'Copied',
            text: txt,
            icon: 'success',
            timer: 1000,
            showConfirmButton: false
        });
    }
}

function toogleOptions(selector, value) {
    if (value == 'contacts') {
        $(selector).find('.contact_selector').show();
        $(selector).find('.tag_selector').hide();
    } else {
        $(selector).find('.contact_selector').hide();
        $(selector).find('.tag_selector').show();
    }
}

function show_error(msg) {
    Swal.fire({
        icon: 'error',
        title: 'Oops...',
        text: msg,
        timer: 2000,
        showConfirmButton: false
    });
}

var videoObj = {
    title: 'YouTube video player',
    src: 'https://www.youtube.com/embed/bXlQ3Mw4uGc?si=GcNQKPVx13MzvbB0',
    short: "https://www.youtube.com/embed/bXlQ3Mw4uGc?si=GcNQKPVx13MzvbB0" // I assume this is for a short URL, which isn't being used currently.
};

let iframeallow =
    `accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share`;

function iframeGen(is_html = false) {
    const iframe = document.createElement('iframe');

    iframe.width = '560';
    iframe.height = '315';
    iframe.src = videoObj.src;
    iframe.title = videoObj.title;
    iframe.frameBorder = '0';
    iframe.allow =
        iframeallow;
    iframe.allowFullscreen = true;

    //     <a href="https://send-link.email/v1/smtp_email/event/clicked/message/eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJtZXNzYWdlSWQiOiJrVjU2Q3RmV0VCSkYxYVk4ZmFPeCIsInVybCI6Imh0dHBzJTNBJTJGJTJGcnlhbnZpZGVvLmpkZnRlc3QueHl6JTJGdmlkZW8lMkZSNGFuZSIsImhvc3QiOiJodHRwczovL3NlbmQtbGluay5lbWFpbCIsImlhdCI6MTcwNzkwODMwMjE1MX0.RJdSwBVnRa43edDR99qqRY-TQ_Uv--DH9NCUp_qZd14" target="_blank" data-saferedirecturl="https://www.google.com/url?q=https://send-link.email/v1/smtp_email/event/clicked/message/eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJtZXNzYWdlSWQiOiJrVjU2Q3RmV0VCSkYxYVk4ZmFPeCIsInVybCI6Imh0dHBzJTNBJTJGJTJGcnlhbnZpZGVvLmpkZnRlc3QueHl6JTJGdmlkZW8lMkZSNGFuZSIsImhvc3QiOiJodHRwczovL3NlbmQtbGluay5lbWFpbCIsImlhdCI6MTcwNzkwODMwMjE1MX0.RJdSwBVnRa43edDR99qqRY-TQ_Uv--DH9NCUp_qZd14&amp;source=gmail&amp;ust=1707994892116000&amp;usg=AOvVaw3yfKrzmlknMQ5H1huwJHjR">
    //     <img src="https://ci3.googleusercontent.com/meips/ADKq_NZpkr2x0iJ4tglZlld437GWVVUJEaQjeAa72Bk6Hv7hiqAN9VqbRHHwYrvg_XNOGypLk9jMoASJN88_IO--z6g3srUhY5fumJg6Zqe9nLgeEvT1LY2ApgIuBuLOobo1K8ls6MwMsMnuaJGgU9KWaV45FjARcKT7aCLT=s0-d-e1-ft#https://storage.googleapis.com/preview-production-assets/email/img/hl_default_img/promotion-wide.png" style="width:500px;height:400px;max-width:100%;max-height:100%" class="CToWUd" data-bit="iit">
    //   </a>

    if (is_html) {
        return `<iframe
                width="${iframe.width}"
                height="${iframe.height}"
                src="${iframe.src}"
                title="${iframe.title}"
                frameborder="${iframe.frameBorder}"
                allow="${iframeallow}"
                allowfullscreen>
            </iframe>`;
    }

    return iframe;
}



function copyLink(param) {
    let link = $(param).data('link');
    copyToClipboard(link);
}

// Function to render pagination links
function renderPagination(recordings) {
    var html = `<nav aria-label="Page navigation"><ul class="pagination">`;
    if (recordings.prev_page_url !== null) {
        html +=
            `<li class="page-item"><a class="page-link" href="#" data-page="${recordings.current_page - 1}">Previous</a></li>`;
    }
    for (var i = 1; i <= recordings.last_page; i++) {
        html +=
            `<li class="page-item ${recordings.current_page === i ? 'active' : ''}"><a class="page-link" href="#" data-page="${i}">${i}</a></li>`;
    }
    if (recordings.next_page_url !== null) {
        html +=
            `<li class="page-item"><a class="page-link" href="#" data-page="${recordings.current_page + 1}">Next</a></li>`;
    }
    html += `</ul></nav>`;
    $('#pagination-container').html(html);
}

// Event listener for pagination links
$(document).on('click', '.pagination a.page-link', function (event) {
    event.preventDefault();
    let page = $(this).data('page');
    fetchData(page);
});

function initializeSelect2(select2class, formid) {
    let dt = $(select2class);
    $(select2class).select2({
        dropdownParent: $(formid), // modal : id modal
        placeholder: dt.attr('placeholder') ?? 'Choose an option',
        allowClear: true,
        closeOnSelect: false,
        width: "100%",
        height: "40px",
        multiple: true,
    });
}

function copyLink(param) {
    let link = $(param).data('link');
    copyToClipboard(link);
}

// $('.copy-link').click(function(e) {
//     e.preventDefault();
//     let link = $(this).data('link');
//     console.log(link);
//     copyToClipboard(link);
// });

// $('.copy-iframe').click(function(e) {
//     e.preventDefault();
//     let link = $(this).data('link');
//     text =
//         iframeGen(true);
//     copyToClipboard(text);
// });


// Function to render fetched data
