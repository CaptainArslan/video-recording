var parentWindow = window.parent;
window.addEventListener("message", (e) => {
    var data = e.data;
    console.log();
    console.log(data);
    if (data.type == 'location') {
        checkForauth(data);
    }
});

$(document).ready(function () {
    let dt = {
        location: window.env.CRM_LOCATION_ID,
        token: window.env.CRM_TOKEN,
    };
    if (dt?.token && dt?.location && dt?.token != "" && dt?.location != "") {
        checkForauth(dt);
    } else {
        parentWindow.postMessage('authconnecting', '*');
    }
});

// let mainusertoken = '';

function checkForauth(dt) {
    loadingStart();
    console.log("Checking for URL");
    var url = "/checking/auth";
    $.ajax({
        url: url,
        type: 'GET',
        data: {
            location: dt.location,
            token: dt.token
        },
        success: function (data) {
            loadingStop();
            console.log(data);
            toastr.success("Location connected successfully!");
            location.href = "/dashboard?v=" + new Date().getTime();
        },
        error: function (data) {
            loadingStop();
            toastr.error("Error Occured while connecting");
            console.log("Error in ajax call : " + data);
        },
        complete: function () {
            alert("completion" + data);
            toastr.info("Completion");
            loadingStop();
        }
    });
}