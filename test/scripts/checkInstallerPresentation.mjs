/* Run with a locally installed Playwright; PHP fixtures and a read-only asset server
 * exercise the real installer JavaScript without touching a database or config. */
import { createServer } from 'node:http';
import { readFileSync, mkdirSync } from 'node:fs';
import { execFileSync } from 'node:child_process';
import { resolve, dirname, extname } from 'node:path';
import { fileURLToPath } from 'node:url';
import assert from 'node:assert/strict';
const { chromium } = await import(process.env.PLAYWRIGHT_MODULE || 'playwright');
const root = resolve(dirname(fileURLToPath(import.meta.url)), '../..');
const output = resolve(process.env.INSTALLER_SCREENSHOTS || '/tmp/opencats-installer-screenshots');
mkdirSync(output, { recursive: true });
const fixture = kind => execFileSync(process.env.PHP_BINARY || 'php', [resolve(root, 'test/scripts/renderInstaller.php'), kind], { encoding: 'utf8' });
const fixtures = Object.fromEntries(['extras', 'shell', 'locked', 'start', 'checks', 'missing-database', 'notinstalled', 'unsupported', 'pending-admin', 'pending-user', 'password', 'localization', 'siteName', 'text', 'conclusion'].map(kind => [kind, fixture(kind)]));
let locked = false;
const requests = [];
const server = createServer(async (req, res) => {
    const url = new URL(req.url, 'http://localhost');
    if (url.pathname === '/ajax.php') {
        let body = '';
        for await (const chunk of req) body += chunk;
        const params = new URLSearchParams(body);
        const action = params.get('a');
        requests.push(Object.fromEntries(params));
        let response;
        if (locked) response = fixtures.locked;
        else if (action === 'startInstall') response = fixtures.start;
        else if (action === 'installTest') response = fixtures.checks;
        else if (action === 'databaseConnectivity' && params.has('user')) response = '<script>setActiveStep(2);showTextBlock("databaseConnectivity");document.getElementById("testDatabaseConnectivity").disabled=true;document.getElementById("testDatabaseConnectivityIndicator").style.visibility="visible";Installpage_append("a=testDatabaseConnectivity");</script>';
        else if (action === 'testDatabaseConnectivity') response = fixtures['missing-database'];
        else if (action === 'databaseConnectivity') response = '<script>setActiveStep(2);showTextBlock("databaseConnectivity");</script>';
        else if (action === 'mailSettings') response = '<script>setActiveStep(5);showTextBlock("mailSettings");changeMailForm();</script>';
        else { res.writeHead(400); res.end('Unexpected fixture action: ' + action); return; }
        res.setHeader('Content-Type', 'text/html'); res.end(response); return;
    }
    if (url.pathname === '/installwizard.php' || url.pathname === '/fixture') {
        res.setHeader('Content-Type', 'text/html');
        res.end(fixtures[url.searchParams.get('kind') || 'shell']); return;
    }
    const path = resolve(root, '.' + url.pathname);
    if (!path.startsWith(root + '/') || !['.js', '.css', '.gif', '.png'].includes(extname(path))) { res.writeHead(404); res.end(); return; }
    try {
        res.setHeader('Content-Type', ({'.css':'text/css','.js':'text/javascript','.gif':'image/gif','.png':'image/png'})[extname(path)]);
        res.end(readFileSync(path));
    } catch { res.writeHead(404); res.end(); }
});
await new Promise(resolve => server.listen(0, '127.0.0.1', resolve));
const base = `http://127.0.0.1:${server.address().port}`;
const browser = await chromium.launch({ headless: true, ...(process.env.CHROMIUM_PATH ? { executablePath: process.env.CHROMIUM_PATH } : {}) });
try {
    const page = await browser.newPage();
    const errors = [];
    page.on('pageerror', error => errors.push(error.message));
    async function capture(name, width) {
        assert.ok(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth), `Horizontal overflow: ${name} at ${width}`);
        await page.screenshot({ path: resolve(output, `${width}-${name}.png`), fullPage: true });
    }
    async function panel(id, step) {
        await page.evaluate(({id, step}) => {
            hideDivsWithin(document.getElementById('allSpans'));
            document.getElementById('subFormBlock').innerHTML = '';
            showTextBlock(id); setActiveStep(step);
        }, {id, step});
        assert.ok(await page.locator('#' + id).isVisible());
        assert.equal(await page.locator('[aria-current="step"]').getAttribute('id'), 'step' + step);
    }
    for (const width of [1440, 390]) {
        await page.setViewportSize({ width, height: 950 });
        await page.goto(base + '/installwizard.php');
        await page.locator('#testFailed').waitFor({ state: 'visible' });
        assert.equal(await page.locator('nav li').count(), 7);
        await capture('system-check', width);
        await page.getByRole('button', { name: 'Retry Installation', exact: true }).click();
        await page.locator('#testFailed').waitFor({ state: 'visible' });
        await page.evaluate(() => Installpage_populate('a=databaseConnectivity'));
        await page.locator('#dbname').waitFor({ state: 'visible' });
        await capture('database-form', width);
        await page.getByRole('button', { name: 'Test Database Connectivity', exact: true }).click();
        await page.locator('#MySQLTestFailed').waitFor({ state: 'visible' });
        assert.equal(requests.at(-2).host, 'localhost');
        assert.ok(await page.locator('#dbname').isVisible());
        assert.ok(await page.locator('#testDatabaseConnectivity').isEnabled());
        await capture('database-failure', width);
        await panel('emptyDatabase', 3); await capture('loading-data', width);
        await panel('resumeParsing', 4);
        await page.evaluate(() => { document.getElementById('docExecutableOrg').value = '/usr/bin/antiword'; });
        await page.locator('#docEnabled').uncheck();
        assert.ok(await page.locator('#docExecutable').isDisabled());
        await page.locator('#docEnabled').check();
        assert.equal(await page.locator('#docExecutable').inputValue(), '/usr/bin/antiword');
        await capture('resume-indexing', width);
        await page.getByRole('button', { name: 'Skip this Step', exact: true }).click();
        await page.locator('#mailSupport').waitFor({ state: 'visible' });
        for (const option of ['opt0', 'opt1', 'opt2', 'opt3', 'opt4']) {
            await page.locator('#mailSupport').selectOption(option);
            assert.equal(await page.locator('#mailSendmail').isVisible(), option === 'opt2');
            assert.equal(await page.locator('#mailSmtpHost').isVisible(), ['opt3', 'opt4'].includes(option));
            assert.equal(await page.locator('#mailSmtpUsername').isVisible(), option === 'opt4');
        }
        await capture('mail-settings', width);
        await panel('pickOptionalComponents', 6);
        await page.evaluate(html => execJS(html), fixtures.extras);
        assert.equal(await page.locator('#extrasList input[type=radio]').count(), 2);
        assert.ok(await page.locator('#extrasList input[value=false]').isChecked());
        await page.locator('#extrasList input[value=true]').check();
        await capture('extras', width);
        await panel('installingComponentsMaint', 7);
        await page.evaluate(() => { setProgressUpdating('SELECT example', 1, 10, 'Example'); setProgressUpdating('SELECT example', 5, 10, 'Example'); });
        assert.equal(await page.locator('#d3').getAttribute('aria-valuenow'), '44');
        await capture('progress', width);
        for (const [id, step] of [['testPassed', 1], ['testWarning', 1], ['testFailedWarning', 1], ['installCompleteProd', 7], ['installCompleteDemo', 7], ['queryResetDatabase', 3], ['queryInstallBackup', 3], ['phpVersion', 1]]) {
            await panel(id, step); await capture(id, width);
        }
        locked = true;
        await page.getByRole('button', { name: 'Restart Install', exact: true }).click();
        await page.locator('#installLocked').waitFor({ state: 'visible' });
        await capture('already-installed', width);
        locked = false;
        for (const kind of ['notinstalled', 'unsupported', 'pending-admin', 'pending-user', 'password', 'localization', 'siteName', 'text', 'conclusion']) {
            await page.goto(base + '/fixture?kind=' + kind);
            await capture(kind, width);
        }
    }
    assert.deepEqual(errors, []);
    console.log(`Installer browser checks passed at 1440px and 390px. Screenshots: ${output}`);
} finally {
    await browser.close();
    server.close();
}
