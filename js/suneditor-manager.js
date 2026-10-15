/* The three OpenCATS rich-text fields keep their original textarea/POST contract. */
var OpenCATSEditor = (function ()
{
    var instances = {};

    function getHTML(nodeId)
    {
        var editor = instances[nodeId];
        if (!editor)
        {
            return document.getElementById(nodeId).value;
        }

        // SunEditor 3.3.3's html.get() reads only the WYSIWYG DOM, not code view.
        if (editor.$.frameContext.get('isCodeView'))
        {
            var content = document.createElement('div');
            content.innerHTML = editor.$.html.clean(editor.$.frameContext.get('code').value, { forceFormat: true });
            return content.innerHTML;
        }
        return editor.$.html.get();
    }

    function sync(nodeId)
    {
        document.getElementById(nodeId).value = getHTML(nodeId);
    }

    function setHTML(nodeId, html)
    {
        if (instances[nodeId])
        {
            instances[nodeId].$.html.set(html);
        }
        document.getElementById(nodeId).value = html;
    }

    function create(nodeId)
    {
        var textarea = document.getElementById(nodeId);
        if (!textarea || typeof SUNEDITOR === 'undefined' || instances[nodeId])
        {
            return instances[nodeId];
        }

        var initialHTML = textarea.value;
        var editor = SUNEDITOR.create(textarea, {
            plugins: SUNEDITOR.plugins,
            value: '',
            height: '200px',
            width: '100%',
            editorStyle: 'font-family: Arial, sans-serif; font-size: 13px; line-height: 1.6;',
            toolbar_sticky: -1,
            statusbar_resizeEnable: true,
            strictMode: true,
            autoLinkify: false,
            convertTextTags: { strike: 's' },
            buttonList: [
                ['undo', 'redo'], ['link', 'anchor', 'image', 'table', 'hr'], ['fullScreen', 'codeView'],
                '/',
                ['bold', 'italic', 'strike', 'removeFormat'],
                ['list_numbered', 'list_bulleted', 'outdent', 'indent', 'blockquote'],
                ['blockStyle', 'font', 'fontSize']
            ],
            blockStyle: { items: ['p', 'h1', 'h2', 'h3', 'pre'] },
            font: { items: [
                'Arial', 'Comic Sans MS', 'Courier New', 'Georgia', 'Lucida Sans Unicode',
                'Tahoma', 'Times New Roman', 'Trebuchet MS', 'Verdana'
            ] },
            fontSize: { sizeUnit: 'px', unitMap: { px: {
                default: 12, inc: 1, min: 8, max: 72,
                list: [8, 9, 10, 11, 12, 14, 16, 18, 20, 22, 24, 26, 28, 36, 48, 72]
            } } },
            image: { createFileInput: false, defaultFormatType: 'inline' }
        });

        // These three isolated overrides depend on SunEditor 3.3.3 internals.
        // Supported options cannot preserve these semantics; see the migration
        // report and checkRichTextEditor.mjs before changing the pinned version.
        // 3.3.3 compress() deletes every literal newline before parsing, even in
        // pre/text nodes. Character references survive that parsing unchanged.
        // Keep strict filtering; let the editor remove structural indentation.
        // This also covers paste and switching back from source view.
        editor.$.html.compress = function (html)
        {
            return html.replace(/\n/g, '&#10;');
        };
        // The cleaner parses HTML more than once. Each parse consumes the first
        // LF after <pre>. Protect those text characters during cleaning, then
        // supply one disposable LF for the final innerHTML assignment.
        var clean = editor.$.html.clean;
        editor.$.html.clean = function (html, options)
        {
            var marker = 'OpenCATSPreNewline';
            while (html.indexOf(marker) !== -1) marker += '_';
            html = html.replace(/(<pre\b[^>]*>)(\n+)/gi, function (match, tag, lines)
            {
                return tag + lines.replace(/\n/g, marker);
            });
            return clean.call(this, html, options).split(marker).join('\n').replace(/(<pre\b[^>]*>)\n/gi, '$1\n\n');
        };
        // Use the compact serializer in source view too: its pretty printer adds
        // newlines after <br>, which Mailer would turn into additional breaks.
        var convertToCode = editor.$.html._convertToCode;
        editor.$.html._convertToCode = function (html, compact)
        {
            var content = convertToCode.call(this, html, true);
            // html.get() reparses its serialized result; source view does not.
            return compact ? content.replace(/(<pre\b[^>]*>)\n/gi, '$1\n\n') : content;
        };
        instances[nodeId] = editor;
        editor.$.html.set(initialHTML);

        if (textarea.form)
        {
            // Capture runs before existing inline validators, including retries.
            textarea.form.addEventListener('submit', function () { sync(nodeId); }, true);
        }
        return editor;
    }

    return { create: create, getHTML: getHTML, setHTML: setHTML, sync: sync };
}());
