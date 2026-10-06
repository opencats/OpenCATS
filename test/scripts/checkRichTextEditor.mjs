/** Browser contract tests using the same Playwright harness as installer checks.
 * PLAYWRIGHT_MODULE=/path/to/playwright/index.mjs CHROMIUM_PATH=/path/to/chromium node test/scripts/checkRichTextEditor.mjs
 * No database or mail delivery. FormData captures the real browser POST fields.
 */
import { createServer } from 'node:http';
import { readFileSync, mkdirSync } from 'node:fs';
import { resolve, dirname, extname } from 'node:path';
import { fileURLToPath } from 'node:url';
import assert from 'node:assert/strict';
const { chromium } = await import(process.env.PLAYWRIGHT_MODULE || 'playwright');
const root = resolve(dirname(fileURLToPath(import.meta.url)), '../..');
const initial = '<p>Existing &amp; copied é</p><pre>  first\n  second\n\n last</pre>';
const escape = text => text.replaceAll('&', '&amp;').replaceAll('<', '&lt;').replaceAll('>', '&gt;');
const fixture = `<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="stylesheet" href="/vendor/twbs/bootstrap/dist/css/bootstrap.min.css"><link rel="stylesheet" href="/js/suneditor/suneditor.min.css">
<script src="/js/suneditor/suneditor.min.js"></script><script src="/js/suneditor-manager.js"></script></head>
<body><main class="container-fluid"><form id="form" onsubmit="window.validations++;return document.getElementById('title').value!==''">
<input id="title" name="title" value="Job"><div class="row"><div class="col-sm-4">Description</div><div class="col-12 col-sm">
<textarea id="description" name="description">${escape(initial)}</textarea></div></div>
<textarea id="notes" name="notes">Plain notes</textarea><button type="submit">Submit</button><button type="reset">Reset</button></form></main>
<script>window.validations=0; OpenCATSEditor.create('description'); document.getElementById('title').focus();
// Capture after the real submit event/inline validator, without navigating or sending mail.
document.addEventListener('submit',function(e){if(!e.defaultPrevented)window.post=Object.fromEntries(new FormData(e.target));e.preventDefault();});</script></body></html>`;
const server = createServer((req, res) => {
    if (req.url === '/') { res.setHeader('Content-Type', 'text/html'); res.end(fixture); return; }
    const path = resolve(root, '.' + new URL(req.url, 'http://localhost').pathname);
    if (!path.startsWith(root + '/') || !['.js', '.css'].includes(extname(path))) { res.writeHead(404); res.end(); return; }
    try { res.setHeader('Content-Type', extname(path) === '.js' ? 'text/javascript' : 'text/css'); res.end(readFileSync(path)); }
    catch { res.writeHead(404); res.end(); }
});
await new Promise(r => server.listen(0, '127.0.0.1', r));
const browser = await chromium.launch({ headless: true, ...(process.env.CHROMIUM_PATH ? { executablePath: process.env.CHROMIUM_PATH } : {}) });
const errors = [], requests = [];
try {
    const page = await browser.newPage();
    page.on('pageerror', error => errors.push(error.message));
    page.on('request', req => requests.push(req.url()));
    page.on('response', res => { if (res.status() >= 400) errors.push(`${res.status()} ${res.url()}`); });
    await page.goto(`http://127.0.0.1:${server.address().port}/`);
    const edit = page.locator('.sun-editor-editable[contenteditable="true"]');
    await edit.waitFor();
    const get = () => page.evaluate(() => OpenCATSEditor.getHTML('description'));
    const set = html => page.evaluate(html => OpenCATSEditor.setHTML('description', html), html);
    const submit = () => page.getByRole('button', { name: 'Submit', exact: true }).click();
    assert.equal(await page.evaluate(() => document.activeElement.id), 'title');
    assert.equal(await get(), initial, 'escaped initial HTML and pre whitespace');
    assert.equal(await page.locator('#notes').isVisible(), true);
    assert.equal(await edit.count(), 1);
    assert.equal(Math.round((await edit.boundingBox()).height), 200);
    for (const command of ['bold', 'italic', 'strike', 'removeFormat', 'blockStyle', 'font', 'fontSize', 'list_numbered', 'list_bulleted', 'indent', 'outdent', 'blockquote', 'link', 'anchor', 'image', 'table', 'hr', 'undo', 'redo', 'codeView', 'fullScreen']) {
        assert.ok(await page.locator(`.se-toolbar [data-command="${command}"]`).count(), command);
    }
    await edit.fill('Toolbar formatting');
    await edit.press('ControlOrMeta+A');
    await page.locator('.se-toolbar [data-command="bold"]').click();
    assert.ok((await get()).includes('<strong>'), 'bold toolbar action');
    await page.locator('.se-toolbar [data-command="strike"]').click();
    assert.ok((await get()).includes('<s>'), 'strikethrough uses server-supported markup');
    await page.locator('.se-toolbar [data-command="undo"]').click();
    assert.ok(!(await get()).includes('<s>'), 'undo formatting');
    await page.locator('.se-toolbar [data-command="redo"]').click();
    assert.ok((await get()).includes('<s>'), 'redo formatting');
    await edit.fill('Typed é & immediate submit');
    await submit();
    assert.ok((await page.evaluate(() => window.post.description)).includes('Typed é &amp; immediate submit'));
    await set('<p>Programmatic</p>');
    await page.locator('#title').fill('');
    await submit();
    assert.equal(await page.locator('#description').inputValue(), '<p>Programmatic</p>');
    assert.ok(!(await page.evaluate(() => window.post.description)).includes('Programmatic'), 'invalid form did not submit');
    await edit.fill('After validation failure');
    await page.locator('#title').fill('Corrected');
    await submit();
    assert.ok((await page.evaluate(() => window.post.description)).includes('After validation failure'));
    await page.getByRole('button', { name: 'Reset', exact: true }).click();
    assert.ok((await get()).includes('After validation failure'), 'Reset retains rich-text edits as before');
    await submit();
    assert.ok((await page.evaluate(() => window.post.description)).includes('After validation failure'));

    const cases = [
        '<p>First</p><p>Second<br>line</p><ol><li>One</li><li>Two</li></ol><ul><li>Three</li></ul>',
        '<p><strong>Bold</strong> <em>italic</em> <s>strike</s> <a href="https://example.test/">Link</a></p>',
        '<p><span style="font-family: Georgia; font-size: 18px;">Font</span> £ € é 中文 &amp; &lt;entity&gt;</p>',
        '<table><tbody><tr><td>Cell A</td><td>Cell B</td></tr></tbody></table>',
        '<pre>  first\n  second\n\n last</pre>',
        '<pre>\n\n  first\n  second\n\n last</pre>',
        '<pre>\n\nOpenCATSPreNewline\n  first\n  second\n\n last</pre>',
        '<p>normal\ntext  spaces</p>'
    ];
    for (const html of cases) {
        await set(html);
        const before = await get();
        // Protect the 3.3.3 clean override, including its marker collision guard.
        if (html.includes('<pre>')) assert.equal(before, html, 'clean override preserves leading pre blank lines and literal marker text');
        // Protect the compress override: these newlines affect Mailer output.
        if (html.includes('normal')) assert.equal(before, html, 'compress override preserves intentional text newlines');
        assert.ok(before.includes(html.includes('<pre>') ? '  first\n  second\n\n last' : html.includes('normal') ? 'normal\ntext  spaces' : html.includes('table') ? 'Cell A' : html.includes('Font') ? 'font-size: 18px' : html.includes('First') ? '<li>Two</li>' : 'https://example.test/'));
        if (!html.includes('\n')) assert.ok(!before.includes('\n'), 'no serializer line breaks in email');
        await page.locator('.se-toolbar [data-command="codeView"]').click();
        // Protect _convertToCode: pretty-printing after <br> adds email breaks.
        if (html.includes('First')) assert.equal(await page.locator('textarea.se-code-viewer').inputValue(), before, 'serializer override adds no source formatting newlines');
        assert.equal(await get(), before, 'source preview does not introduce whitespace');
        await submit();
        assert.equal(await page.evaluate(() => window.post.description), before, 'source-mode submission');
        await page.locator('.se-toolbar [data-command="codeView"]').click();
        assert.equal((await get()).replace(/ se-(?:figure|component)-selected/g, ''), before, 'source/WYSIWYG round trip');
    }
    await page.locator('.se-toolbar [data-command="codeView"]').click();
    const code = page.locator('textarea.se-code-viewer');
    await code.fill('<p>Source edit</p>\n<ul>\n  <li>Source list</li>\n</ul>');
    assert.equal(await get(), '<p>Source edit</p><ul><li>Source list</li></ul>');
    await submit();
    assert.equal(await page.evaluate(() => window.post.description), '<p>Source edit</p><ul><li>Source list</li></ul>');
    await set('<p>Template while in code view</p>');
    assert.equal(await get(), '<p>Template while in code view</p>');
    await page.locator('.se-toolbar [data-command="codeView"]').click();
    // Representative clipboard payloads, using the editor's real paste handler.
    for (const data of [
        { text: 'Plain é & text\nSecond line' },
        { html: '<p><b>Formatted</b> <a href="https://example.test/">link</a></p><ul><li>List item</li></ul>' },
        { html: '<!--StartFragment--><p class="MsoNormal" style="mso-margin-top-alt:auto"><b>Word styled</b></p><!--EndFragment-->' },
        { html: '<meta name="generator" content="LibreOffice"><p style="margin-bottom:0cm">LibreOffice text</p>' },
        { html: '<b id="docs-internal-guid-test"><p><span style="font-weight:700">Google Docs text</span></p></b>' }
    ]) {
        await set(''); await edit.click();
        await edit.evaluate((el, data) => { const clip = new DataTransfer(); clip.setData(data.html ? 'text/html' : 'text/plain', data.html || data.text); el.dispatchEvent(new ClipboardEvent('paste', { clipboardData: clip, bubbles: true, cancelable: true })); }, data);
        await page.waitForFunction(() => document.querySelector('.sun-editor-editable').textContent.trim().length > 0);
        assert.ok((await edit.innerText()).includes(data.text ? 'Plain é & text' : data.html.includes('Formatted') ? 'Formatted' : data.html.includes('MsoNormal') ? 'Word styled' : data.html.includes('LibreOffice') ? 'LibreOffice text' : 'Google Docs text'));
    }
    const bar = page.locator('.se-status-bar').first();
    const barBox = await bar.boundingBox();
    const oldHeight = (await edit.boundingBox()).height;
    await page.mouse.move(barBox.x + barBox.width - 10, barBox.y + barBox.height / 2);
    await page.mouse.down(); await page.mouse.move(barBox.x + barBox.width - 10, barBox.y + barBox.height / 2 + 40); await page.mouse.up();
    assert.ok((await edit.boundingBox()).height > oldHeight, 'editor can be resized');
    const screenshots = process.env.EDITOR_SCREENSHOTS || '/tmp/opencats-editor-screenshots';
    mkdirSync(screenshots, { recursive: true });
    for (const width of [1280, 390]) {
        await page.setViewportSize({ width, height: 900 });
        assert.ok(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth), `layout at ${width}px`);
        await page.screenshot({ path: resolve(screenshots, `editor-${width}.png`), fullPage: true });
    }
    assert.deepEqual(errors, []);
    assert.ok(requests.every(url => url.startsWith(`http://127.0.0.1:${server.address().port}/`)));
    assert.ok(requests.every(url => !/ckeditor/i.test(url)));
    console.log('PASS: initial/copy HTML, typing, validation retry, POST, Reset, source, whitespace, formatting, paste, local assets and layout');
} finally { await browser.close(); await new Promise(r => server.close(r)); }
