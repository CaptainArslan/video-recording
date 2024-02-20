<script>
    var EmbededButton = function(context) {
        var ui = $.summernote.ui;
        var button = ui.button({
            contents: 'Embed Code',
            // tooltip: 'Embed Code',
            click: function() {
                // let html = iframeGen();
                let html = poster();
                context.invoke('editor.insertNode', html);
            }
        });
        return button.render();
    }

    let LinkButton = function(context) {
        var ui = $.summernote.ui;
        var button = ui.button({
            contents: 'Video Link',
            // tooltip: 'Video Link',
            click: function() {
                const link = document.createElement('a');
                link.href = videoObj.src;
                // link.setAttribute('target', '_blank');
                link.title = videoObj.title;
                link.innerText = videoObj.title;
                context.invoke('editor.insertNode', link);
            }
        });
        return button.render();
    };

    let ShortButton = function(context) {
        var ui = $.summernote.ui;
        var button = ui.button({
            contents: 'Short URL',
            // tooltip: 'Short URL',
            click: function() {
                const link = document.createElement('a');
                let uri = videoObj.src;
                if (videoObj?.short != '') {
                    uri = videoObj.short;
                }
                link.href = uri;
                // link.setAttribute('target', '_blank');
                link.title = videoObj.title;
                link.innerText = videoObj.title;
                context.invoke('editor.insertNode', link);
            }
        });

        return button.render();
    };
</script>
<script>
    var height = 250;
    var fields = @json($fields);
    fields = fields.map(t => t.replaceAll('\{\{', '').replaceAll('\}\}', ''));
</script>

<script>
    let hint = {
        hint: {
            words: fields, // Assuming 'fields' is defined and populated elsewhere
            match: /\B\{\{(\w*)$/i,
            search: function(keyword, callback) {
                callback($.grep(this.words, function(item) {
                    return item.includes(keyword);
                }));
            },
            content: function(item) {
                return '\{\{ ' + item + ' \}\}'; // Escaping curly braces to avoid Blade syntax
            }
        }
    };
    $('.email-summernote').summernote({
        height: height, // Assuming 'height' is a valid variable
        toolbar: [
            ['style', ['style']],
            ['font', ['bold', 'underline', 'clear', 'strikethrough', 'superscript',
                'subscript'
            ]],
            ['fontname', ['fontname', 'fontsize']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph', 'height']],
            ['table', ['table']],
            ['insert', ['Embeded', 'Link', 'picture',
                'hr'
            ]], // Added 'Embeded' and 'Link' buttons
            ['view', ['fullscreen', 'codeview', 'undo', 'redo']],
            // ['custom', ['MergeButton']]

        ],
        placeholder: '',
        buttons: {
            Embeded: EmbededButton,
            Link: LinkButton,
            Short: ShortButton,
        },
        ...hint
    });

    $('.sms-summernote').summernote({
        toolbar: [
            ['insert', ['Short']]
        ],
        shortcuts: false,
        height: height,
        buttons: {
            Short: ShortButton,
        },
        ...hint
    });
</script>
