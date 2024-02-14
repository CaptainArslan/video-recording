var EmbededButton = function (context) {
    var ui = $.summernote.ui;
    var button = ui.button({
        contents: 'Embed Code',
        tooltip: 'Embed Code',
        click: function () {
            context.invoke('editor.insertNode', iframeGen());
        }
    });
    return button.render();
}

let LinkButton = function (context) {
    var ui = $.summernote.ui;
    var button = ui.button({
        contents: 'Video Link',
        tooltip: 'Video Link',
        click: function () {
            const link = document.createElement('a');
            link.href = videoObj.src;
            link.setAttribute('target', '_blank');
            link.title = videoObj.title;
            link.innerText = videoObj.title;
            context.invoke('editor.insertNode', link);
        }
    });
    return button.render();
};

let ShortButton = function (context) {
    var ui = $.summernote.ui;
    var button = ui.button({
        contents: 'Short URL',
        tooltip: 'Short URL',
        click: function () {
            const link = document.createElement('a');
            let uri = videoObj.src;
            if (videoObj?.short != '') {
                uri = videoObj.short;
            }
            link.href = uri;
            link.setAttribute('target', '_blank');
            link.title = videoObj.title;
            link.innerText = videoObj.title;
            context.invoke('editor.insertNode', link);
        }
    });

    return button.render();
};


