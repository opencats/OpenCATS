
function placeCkEditorIn(nodeId)
{
    CKEDITOR.replace(nodeId, { extraPlugins: 'font' } );
    CKEDITOR.on('instanceReady', function(ev)
    {
        var tags = ['p', 'ol', 'ul', 'li']; // etc.

        for (var key in tags) {
            ev.editor.dataProcessor.writer.setRules(
                tags[key],
                {
                    indent : false,
                    breakBeforeOpen : false,
                    breakAfterOpen : false,
                    breakBeforeClose : false,
                    breakAfterClose : false, 
                });
        }
    });
}
