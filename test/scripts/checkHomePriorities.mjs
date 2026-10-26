/* Database-free Home component checks using real PHP templates and Bootstrap assets. */
import { createServer } from 'node:http';
import { readFileSync, mkdirSync, writeFileSync } from 'node:fs';
import { execFileSync } from 'node:child_process';
import { resolve, dirname, extname } from 'node:path';
import { fileURLToPath } from 'node:url';
import assert from 'node:assert/strict';
const { chromium } = await import(process.env.PLAYWRIGHT_MODULE || 'playwright');
const root = resolve(dirname(fileURLToPath(import.meta.url)), '../..');
const output = process.env.HOME_SCREENSHOTS || '/tmp/opencats-stage5d2b-refinement-screenshots';
mkdirSync(output, {recursive: true});
const fixtures = Object.fromEntries(['normal', 'empty', 'busy', 'page2', 'denied', 'mixed', 'quiet', 'long', 'restricted', 'unsafe', 'compact2', 'removed', 'fullfirst', 'warning-led', 'summary-single'].map(scenario => [scenario,
    execFileSync(process.env.PHP_BINARY || 'php', [resolve(root, 'test/scripts/renderWarningReviews.php'), 'home', scenario], {encoding: 'utf8'})]));
const activityFixtures = Object.fromEntries(['normal','unsafe','restricted','empty','mixed','mixed-company'].map(scenario => [scenario, execFileSync(process.env.PHP_BINARY || 'php', [resolve(root, 'test/scripts/renderWarningReviews.php'), 'homeActivity',scenario], {encoding:'utf8'})]));
let activityReads = 0;
const server = createServer((req, res) => {
    const url = new URL(req.url, 'http://localhost');
    if (url.searchParams.get('a') === 'contextActivity') { activityReads++; res.setHeader('X-Home-Activity','1'); res.setHeader('Content-Type','text/html'); res.end(activityFixtures[new URL(req.headers.referer || 'http://localhost').searchParams.get('activity') || new URL(req.headers.referer || 'http://localhost').searchParams.get('scenario')] || activityFixtures.normal); return; }
    if (url.pathname === '/') { res.setHeader('Content-Type', 'text/html'); res.end(fixtures[url.searchParams.get('scenario') || 'normal']); return; }
    const path = resolve(root, '.' + url.pathname);
    if (!path.startsWith(root + '/') || !['.js', '.css', '.svg', '.gif', '.png', '.ico', '.html'].includes(extname(path))) {res.writeHead(404); res.end(); return;}
    try {res.setHeader('Content-Type', ({'.js':'text/javascript', '.css':'text/css', '.svg':'image/svg+xml', '.gif':'image/gif', '.png':'image/png', '.ico':'image/x-icon', '.html':'text/html'})[extname(path)]); res.end(readFileSync(path));}
    catch {res.writeHead(404); res.end();}
});
await new Promise(r => server.listen(0, '127.0.0.1', r));
// Optional maintainer preview uses this same database-free fixture server.
if (process.env.HOME_PREVIEW === '1') {
    console.log(`Synthetic Home preview: http://127.0.0.1:${server.address().port}/`);
    console.log('Scenarios: normal, long, quiet, empty, busy, page2, denied, mixed, restricted (?scenario=long).');
    console.log('Native writes are intercepted; record navigation requires the authenticated application.');
    await new Promise(resolve => process.once('SIGTERM', () => server.close(resolve)));
    process.exit(0);
}
const browser = await chromium.launch({headless: true, ...(process.env.CHROMIUM_PATH ? {executablePath: process.env.CHROMIUM_PATH} : {})});
const errors = [];
try {
    const page = await browser.newPage();
    page.on('pageerror', e => errors.push(e.message));
    page.on('response', response => {if (response.status() >= 400) errors.push(`${response.status()} ${response.url()}`);});
    const visit = async (scenario, fresh = false) => page.goto(`http://127.0.0.1:${server.address().port}/?scenario=${scenario}${fresh ? '&fresh=1' : ''}`);
    await page.addInitScript(() => { if (new URLSearchParams(location.search).has('fresh')) sessionStorage.clear(); });
    const measurements = {browser: await browser.version(), platform: process.platform, referenceClock:'2026-10-15', cases:[]};
    const measure = async (dataset, state, targetPage = page) => {
        const result = await targetPage.evaluate(() => {
            const rows = Array.from(document.querySelectorAll('#priorityRows > li'));
            const rects = rows.map(el => el.getBoundingClientRect());
            const actions = rows.map(el => el.querySelector('.home-priority-actions').getBoundingClientRect().width);
            const footer = document.querySelector('[data-home-queue] .card-footer').getBoundingClientRect();
            const panel = document.querySelector('[data-home-context].show');
            const box = el => { const r=el.getBoundingClientRect(); return {top:r.top,bottom:r.bottom,left:r.left,width:r.width,height:r.height,fullyVisible:r.top>=0&&r.bottom<=innerHeight}; };
            const selected = panel ? document.getElementById(panel.dataset.homeRow) : null;
            return {context:panel ? {
                id:panel.id,panel:box(panel.querySelector('.home-context-content')),selectedRow:box(selected),
                summaryFacts:box(selected.querySelector('.home-priority-facts')),
                facts:Array.from(panel.querySelectorAll('.home-context-facts dt, .home-context-facts dd')).map(el=>({text:el.textContent,...box(el)})),
                actions:Array.from(panel.querySelectorAll('a, button, summary')).filter(el=>el.getBoundingClientRect().height>0).map(el=>({text:el.textContent.trim(),href:el.getAttribute('href'),...box(el)})),
                subsequentSummaries:rows.slice(rows.indexOf(selected)+1).map(el=>({id:el.id,...box(el.querySelector('.home-priority-facts'))}))
            } : null,width:innerWidth,height:innerHeight,dpr:devicePixelRatio,scale:visualViewport.scale,
                font:getComputedStyle(document.querySelector('.home-screen')).fontFamily,
                shellHeight:document.querySelector('.home-chrome').getBoundingClientRect().height,
                firstRowTop:rects[0]?.top ?? null, visibleRows:rects.filter(r => r.top >= 0 && r.bottom <= innerHeight).length,
                renderedRows:rows.length,
                inventory: {
                    summary: Array.from(document.querySelectorAll('.home-summary-item')).map(el=>({text:el.textContent.trim(),href:el.getAttribute('href')})),
                    filters: Array.from(document.querySelectorAll('[aria-label="Priority filters"] a')).map(el=>({text:el.textContent,href:el.getAttribute('href')})),
                    workNavigation: Array.from(document.querySelectorAll('[aria-label="Work navigation"] a')).map(el=>({text:el.textContent,href:el.getAttribute('href')})),
                    rows: rows.map(el=>({id:el.id,links:Array.from(el.querySelectorAll('a[href]')).map(a=>({text:a.textContent.trim(),href:a.getAttribute('href')})),complete:el.querySelector('[data-home-task]')?.dataset.homeTask,context:el.querySelector('[data-home-context-toggle]')?.getAttribute('aria-controls')})),
                    reviewCount: document.querySelector('[aria-controls="suppressedWarnings"]').textContent.trim(),
                    formCount: document.querySelectorAll('#homeTaskComplete, #warningReviewModal').length
                },
                recent: Array.from(document.querySelectorAll('#MRUBlock a')).map(a => ({text:a.textContent,href:a.getAttribute('href')})),
                navigationTops: Array.from(document.querySelectorAll('#primary > li')).map(el=>el.getBoundingClientRect().top),
                computed: Array.from(document.querySelectorAll('.home-summary-item, .home-summary-item strong, .home-priority-subject h3 a, .home-priority-related a, .home-context-toggle, .home-priority-actions .btn-outline-secondary, .home-due-cue, .home-task-priority')).slice(0,25).map(el=>({text:el.textContent,color:getComputedStyle(el).color,background:getComputedStyle(el).backgroundColor,border:getComputedStyle(el).borderTopColor,weight:getComputedStyle(el).fontWeight})),
                rowIDs:rows.map(row => row.id), rowHeights:rects.map(r => r.height), actionWidths:actions, footerBottom:footer.bottom,
                footerVisible:footer.top >= 0 && footer.bottom <= innerHeight,
                overflow:document.documentElement.scrollWidth > innerWidth,
                railWidth:document.getElementById('homeContextRail').hidden ? 0 : document.getElementById('homeContextRail').getBoundingClientRect().width,
                queueWidth:document.querySelector('[data-home-queue]').getBoundingClientRect().width};
        });
        measurements.cases.push({dataset,state,...result});
        return result;
    };
    const laptopCases = async verify => {
        for (const [width,height] of [[1366,768],[1280,800]]) {
            await page.setViewportSize({width,height});
            for (const [scenario,rowID] of [['normal','priority-task-7'],['long','priority-task-7'],['warning-led','priority-warning-W07-7'],['warning-led','priority-warning-W03-7']]) {
                await visit(scenario,true);
                await page.locator(`#${rowID} [data-home-context-toggle]`).click();
                await page.waitForFunction(()=>!document.querySelector('.collapsing') && !document.querySelector('[data-home-activity-content][aria-busy]'));
                await page.evaluate(()=>scrollTo(0,0));
                const result=await measure(`${scenario}-${rowID}`,'inline');
                const name=`inline-${scenario}-${rowID}-${width}-${height}`;
                await page.screenshot({path:resolve(output,`${name}.png`)});
                await page.screenshot({path:resolve(output,`${name}-full.png`),fullPage:true});
                if (!verify) continue;
                assert.equal(result.overflow,false);
                assert.equal(result.railWidth,0);
                assert.equal(await page.locator(`#context-${rowID}`).count(),1);
                if (scenario==='normal') {
                    assert.equal(result.context.summaryFacts.fullyVisible,true);
                    for (const label of ['Edit / Reschedule','Task details & history']) {
                        const action=result.context.actions.find(a=>a.text===label);
                        assert.ok(action.fullyVisible,`${width}: ${label} should be near the heading in view`);
                    }
                    assert.ok(new Set(result.context.facts.filter((_,i)=>i%2===0).map(f=>f.left)).size>=3);
                    const panel=page.locator(`#context-${rowID}`);
                    await page.locator(`#${rowID} [data-home-context-jump]`).click();
                    await page.keyboard.press('Tab');
                    assert.equal(await panel.getByRole('button',{name:'Close context',exact:true}).evaluate(el=>el===document.activeElement),true);
                    await page.keyboard.press('Tab');
                    assert.equal(await panel.getByRole('button',{name:'Complete Task',exact:true}).evaluate(el=>el===document.activeElement),true);
                    await page.keyboard.press('Tab');
                    const edit=panel.getByRole('link',{name:'Edit / Reschedule',exact:true});
                    assert.equal(await edit.evaluate(el=>el===document.activeElement),true);
                    // The actual focused action node must survive both moves and return to inline order.
                    await page.setViewportSize({width:1440,height:900});
                    await page.waitForFunction(()=>!document.getElementById('homeContextRail').hidden);
                    assert.equal(await edit.evaluate(el=>el===document.activeElement),true);
                    assert.equal(await panel.count(),1);
                    await page.setViewportSize({width,height});
                    await page.waitForFunction(()=>document.getElementById('homeContextRail').hidden);
                    assert.equal(await edit.evaluate(el=>el===document.activeElement),true);
                    assert.equal(await panel.count(),1);
                    await page.keyboard.press('Escape');
                    await panel.waitFor({state:'hidden'});
                }
                if (scenario==='warning-led') {
                    assert.deepEqual(await page.locator('#priorityRows > li').evaluateAll(els=>els.map(el=>el.id)),[
                        'priority-task-8','priority-warning-W02-7','priority-warning-W03-7','priority-warning-W07-7','priority-warning-W08-7-invalid_owner']);
                    assert.deepEqual(await page.locator('.home-summary-item strong').allTextContents(),['0','1','1','4','3']);
                    if (rowID==='priority-warning-W07-7') {
                        await page.locator(`#context-${rowID} [data-review-action="close"]`).click();
                        await page.waitForFunction(()=>document.activeElement.id==='warningReviewCloseReason');
                        await page.locator('#warningReviewModal').getByRole('button',{name:'Cancel',exact:true}).click();
                        await page.locator('#warningReviewModal').waitFor({state:'hidden'});
                    }
                }
            }
        }
    };
    if (process.env.HOME_CAPTURE_ONLY === '1') {
        // Baseline uses the exact same synthetic records and clock as the refinement.
        for (const [width,height] of [[1920,1080],[1440,900],[1366,768],[1280,900],[1280,800]]) {
            await page.setViewportSize({width,height});
            await visit('normal',true);
            await measure('normal','closed');
            await page.screenshot({path:resolve(output,`home-normal-${width}-${height}-closed.png`)});
            await page.locator('#priority-task-7 [data-home-context-toggle]').click();
            await page.waitForFunction(() => !document.querySelector('.collapsing') && !document.querySelector('[data-home-activity-content][aria-busy]'));
            await measure('normal','open');
            await page.screenshot({path:resolve(output,`home-normal-${width}-${height}-open.png`)});
            if (width === 1920) await page.screenshot({path:resolve(output,'home-normal-1920-full-page.png'),fullPage:true});
        }
        await page.setViewportSize({width:1440,height:900});
        await visit('long',true);
        await measure('long','closed');
        await page.screenshot({path:resolve(output,'home-long-1440-closed.png')});
        await page.locator('#priority-task-7 [data-home-context-toggle]').click();
        await page.waitForFunction(() => !document.querySelector('.collapsing') && !document.querySelector('[data-home-activity-content][aria-busy]'));
        await measure('long','open');
        await page.screenshot({path:resolve(output,'home-long-1440-open.png')});
        await page.screenshot({path:resolve(output,'home-desktop-full-page.png'),fullPage:true});
        for (const scenario of ['busy','empty']) {
            await visit(scenario,true); await measure(scenario,'closed');
            await page.screenshot({path:resolve(output,`home-${scenario}-viewport.png`)});
        }
        const captureTouch = await browser.newPage({hasTouch:true,deviceScaleFactor:1});
        await captureTouch.addInitScript(()=>sessionStorage.clear());
        for (const [width,height] of [[1024,768],[390,844],[320,844]]) {
            await captureTouch.setViewportSize({width,height});
            await captureTouch.goto(`http://127.0.0.1:${server.address().port}/?scenario=normal`);
            await measure('normal-touch','closed',captureTouch);
            await captureTouch.screenshot({path:resolve(output,`home-normal-touch-${width}-${height}-closed.png`)});
            await captureTouch.locator('#priority-task-7 [data-home-context-toggle]').click();
            await captureTouch.waitForFunction(()=>!document.querySelector('.collapsing') && !document.querySelector('[aria-busy]'));
            await captureTouch.evaluate(()=>scrollTo(0,0));
            await measure('normal-touch','open',captureTouch);
            await captureTouch.screenshot({path:resolve(output,`home-normal-touch-${width}-${height}-open.png`)});
            await captureTouch.screenshot({path:resolve(output,`home-normal-touch-${width}-full-page.png`),fullPage:true});
        }
        await captureTouch.close();
        await laptopCases(false);
        writeFileSync(resolve(output,'measurements.json'),JSON.stringify(measurements,null,2));
        assert.deepEqual(errors,[]);
        console.log('Captured matching-data baseline; no functional test result claimed.');
    } else {
    if (process.env.HOME_RESPONSIVE_ONLY !== '1') {
    if (!process.env.HOME_CONTINUE_AFTER_LAYOUT) {
    for (const [width, height] of [[1920,1080], [1440,900], [1366,768], [1280,900], [1280,800]]) {
        await page.setViewportSize({width,height});
        await visit('normal', true);
        assert.equal(await page.locator('#priorityRows > li').count(), 8);
        assert.equal(await page.locator('[data-home-context].show').count(), 0);
        assert.equal(await page.locator('#suppressedWarnings.show').count(), 0);
        assert.deepEqual(await page.locator('.home-summary-item strong').allTextContents(), ['1','1','7','3','3']);
        assert.ok(await page.evaluate(() => Math.abs(document.querySelector('[data-home-queue]').getBoundingClientRect().width - document.querySelector('#contents').getBoundingClientRect().width) < 1));
        assert.equal(await page.locator('script[src*="bootstrap.bundle"]').count(), 1);
        assert.ok(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth));
        assert.equal(await page.locator('script').filter({hasText: 'unsafe'}).count(), 0);
        const identities = await page.locator('#priorityRows > li').evaluateAll(rows => rows.map(row => ({id:row.id, text:row.querySelector('.home-priority-facts').innerText})));
        const closed = await measure('normal','closed');
        assert.match(await page.locator('#priority-task-7 .home-priority-due').innerText(), /Overdue.*14-10-26/s);
        assert.equal(await page.locator('#priority-task-7 .home-task-priority').innerText(), 'Priority: Low');
        assert.match(await page.locator('#priority-task-8 .home-priority-due').innerText(), /Due today.*15-10-26/s);
        assert.equal(await page.locator('#priority-task-9 .home-task-priority').innerText(), 'Priority: High');
        assert.match(await page.locator('#priority-task-9 .home-priority-due').innerText(), /Due.*17-10-26/s);
        assert.equal(await page.locator('#priority-task-10 .home-priority-due').innerText(), 'No due date');
        assert.equal(await page.locator('#priority-task-8 .home-task-priority').innerText(), 'Priority: Normal');
        if (height >= 900) assert.ok(closed.visibleRows >= 6);
        if (width === 1920) {assert.equal(closed.visibleRows, 8); assert.equal(closed.footerVisible, true);}
        await page.screenshot({path:resolve(output,`home-normal-${width}-${height}-closed.png`)});
        await page.locator('#priority-task-7 [data-home-context-toggle]').click();
        await page.waitForFunction(() => !document.querySelector('.collapsing') && !document.querySelector('[data-home-activity-content][aria-busy]'));
        const opened = await measure('normal','open');
        assert.equal(opened.overflow, false);
        if (width >= 1400) assert.ok(opened.visibleRows >= 6);
        if (width === 1920) {assert.equal(opened.visibleRows, 8); assert.equal(opened.footerVisible, true);}
        // Only the explicit presentation jump appears; the records, order and action identities stay fixed.
        const openIDs = await page.locator('#priorityRows > li').evaluateAll(rows => rows.map(row => row.id));
        assert.deepEqual(openIDs, identities.map(row => row.id));
        assert.deepEqual(await page.locator('#priorityRows > li').evaluateAll(rows => rows.map(row => ({id:row.id, text:row.querySelector('.home-priority-facts').innerText.replace('Read context','').trim()}))),identities);
        assert.equal(await page.locator('#context-priority-task-7 h3').innerText(), 'Call Alex about interview feedback');
        assert.equal(await page.locator('#priority-task-7 .home-priority-actions [data-home-task]').getAttribute('data-home-title'), 'Call Alex about interview feedback');
        await page.screenshot({path:resolve(output,`home-normal-${width}-${height}-open.png`)});
        if (width === 1920) await page.screenshot({path:resolve(output,'home-normal-1920-full-page.png'),fullPage:true});
        await page.locator('#context-priority-task-7').getByRole('button',{name:'Close context',exact:true}).click();
        await page.waitForFunction(() => !document.querySelector('.collapsing') && !document.querySelector('[data-home-activity-content][aria-busy]'));
    }
    await page.setViewportSize({width:1920,height:1080});
    await visit('normal',true);
    const beforeOpenReads = activityReads;
    assert.equal(await page.locator('.home-support section').count(),3);
    assert.match(await page.locator('#home-accounts-heading').locator('..').innerText(),/Last qualifying contact: 16-09-26/);
    assert.ok((await page.locator('.home-summary-item').first().getAttribute('href')).includes('due=overdue'));
    assert.ok((await page.locator('.home-summary-item').nth(1).getAttribute('href')).includes('due=today'));
    await page.locator('#priority-task-7 [data-home-context-toggle]').click();
    await page.waitForFunction(()=>!document.querySelector('.collapsing') && !document.querySelector('[data-home-activity-content][aria-busy]'));
    assert.equal(activityReads,beforeOpenReads+1);
    assert.equal((await page.locator('.home-summary-item').nth(3).innerText()).replace(/\s+/g,' '),'3 Visible warnings Including: 1 recruitment warning · 1 client follow-up');
    assert.ok((await page.locator('#context-priority-task-7 .home-recent-activity').innerText()).includes('Confirmed the revised brief'));
    await page.locator('#context-priority-task-7 [aria-label="Close context"]').click();
    await page.waitForFunction(()=>!document.querySelector('.collapsing'));
    assert.equal(await page.locator('.home-chrome-single #header').count(),1);
    const routes = await page.locator('#primary a').evaluateAll(links => links.map(a => a.getAttribute('href')));
    await page.locator('#quickSearchFor').focus();
    await page.setViewportSize({width:1280,height:900});
    await page.waitForFunction(() => !document.querySelector('.home-chrome-single'));
    assert.equal(await page.locator('#quickSearchFor').evaluate(el => el === document.activeElement),true);
    assert.deepEqual(await page.locator('#primary a').evaluateAll(links => links.map(a => a.getAttribute('href'))),routes);
    await page.setViewportSize({width:1920,height:1080});
    await page.waitForFunction(() => document.querySelector('.home-chrome-single') !== null);
    assert.equal(await page.locator('#MRUBlock a').count(),5);
    assert.equal(await page.locator('.home-chrome > #MRUPanel').count(),1);
    const navTops = await page.locator('#primary > li').evaluateAll(items=>items.map(el=>el.getBoundingClientRect().top));
    assert.equal(new Set(navTops).size,1); // Populated recent records cannot strand Settings.
    assert.deepEqual(await page.locator('#primary a').evaluateAll(links=>links.map(a=>a.getAttribute('href'))),routes);
    assert.equal(await page.locator('#quickSearchForm').count(),1);
    assert.equal(await page.locator('#headerBlock img').count(),1);
    assert.ok(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth));
    await page.setViewportSize({width:1280,height:900});
    await visit('normal');
    const task = page.locator('#priority-task-7');
    const primary = task.locator('.home-priority-actions').getByRole('button', {name: 'Complete Task', exact: true});
    await primary.focus();
    await page.keyboard.press('Enter');
    const modal = page.locator('#homeTaskComplete');
    await modal.waitFor({state:'visible'});
    await page.waitForFunction(() => document.activeElement.closest('#homeTaskComplete') !== null);
    assert.equal(await modal.locator('[name="taskID"]').inputValue(), '7');
    assert.equal(await modal.locator('[name="homeContext"]').inputValue(), 'priority-task-7');
    assert.equal(await modal.locator('[name="csrfToken"]').inputValue(), 'fixture-token');
    await modal.getByRole('button', {name:'Cancel',exact:true}).click();
    await modal.waitFor({state:'hidden'});
    assert.equal(await page.evaluate(() => window.submissions || 0), 0);
    await primary.click();
    await modal.waitFor({state:'visible'});
    await page.screenshot({path:resolve(output,'home-complete-confirmation.png'),fullPage:true});
    await modal.getByRole('button', {name:'Complete Task',exact:true}).click();
    const saved = await page.evaluate(() => window.submitted);
    assert.equal(saved.taskID, '7');
    assert.equal(saved.returnTo, 'home');
    assert.equal(saved.homeFilter, 'all');
    assert.equal(await modal.locator('form').getAttribute('aria-busy'), 'true');
    await page.screenshot({path:resolve(output,'home-complete-busy.png'),fullPage:true});
    // A second submit event must be stopped before the fixture's native submit interceptor.
    await modal.locator('form').evaluate(form => form.requestSubmit(form.querySelector('[type="submit"]')));
    assert.equal(await page.evaluate(() => window.submissions), 1);
    await page.keyboard.press('Escape');
    await modal.waitFor({state:'hidden'});
    await task.locator('[data-home-context-toggle]').click();
    await page.locator('#context-priority-task-7.show').waitFor();
    assert.match(await page.locator('#context-priority-task-7').getByRole('link', {name:'Edit / Reschedule'}).getAttribute('href'), /m=tasks&a=edit&taskID=7.*returnTo=home/);
    assert.ok((await page.locator('#context-priority-task-7 details').textContent()).includes('Ask for feedback'));
    assert.ok(!(await page.locator('#contents').textContent()).includes('<script>unsafe</script>'));
    await page.screenshot({path: resolve(output,'home-context.png'), fullPage:true});
    await page.reload();
    await page.locator('#context-priority-task-7.show').waitFor();
    // Row/page/filter restoration uses fixed server routes; the browser stores presentation only.
    await visit('busy');
    assert.equal(await page.locator('#priorityRows > li').count(), 8);
    assert.match(await page.locator('[data-home-queue]').innerText(), /Showing 8 of 43 priorities/);
    assert.match(await page.getByRole('link', {name:'View all priorities (43)',exact:true}).getAttribute('href'), /a=priorities&filter=all/);
    await page.screenshot({path:resolve(output,'home-busy.png'),fullPage:true});
    await visit('fullfirst',true);
    assert.equal(await page.locator('#priorityRows > li').count(),25);
    const fullFirst = await page.locator('#priorityRows > li').evaluateAll(rows=>rows.map(el=>el.id));
    await visit('page2');
    assert.equal(await page.locator('#priorityRows > li').count(), 18);
    const fullSecond = await page.locator('#priorityRows > li').evaluateAll(rows=>rows.map(el=>el.id));
    assert.equal(new Set([...fullFirst,...fullSecond]).size,43);
    assert.match(await page.locator('[aria-label="Priority pages"]').innerText(), /1/);
    await page.screenshot({path:resolve(output,'home-page2.png'),fullPage:true});
    await visit('normal',true);
    const firstIDs = await page.locator('#priorityRows > li').evaluateAll(rows=>rows.map(el=>el.id));
    await visit('compact2',true);
    const secondIDs = await page.locator('#priorityRows > li').evaluateAll(rows=>rows.map(el=>el.id));
    assert.equal(firstIDs.length,8); assert.equal(secondIDs.length,1);
    assert.equal(new Set([...firstIDs,...secondIDs]).size,9);
    await visit('normal',true);
    await page.locator('#priority-task-7 [data-home-context-toggle]').click();
    await page.waitForFunction(()=>!document.querySelector('.collapsing') && !document.querySelector('[data-home-activity-content][aria-busy]'));
    // Prepared post-mutation state: no persistence claim from this intercepted fixture.
    await visit('removed');
    assert.equal(await page.locator('#priority-task-7, #context-priority-task-7').count(),0);
    assert.equal(await page.locator('.home-context-open, .home-has-rail').count(),0);
    assert.equal(await page.locator('#priorityRows > li').count(),8);
    assert.deepEqual(await page.locator('.home-summary-item strong').allTextContents(),['0','1','6','2','3']);
    for (const scenario of ['empty','denied']) {
        await visit(scenario);
        assert.equal(await page.locator('#priorityRows > li').count(), 0);
        assert.match(await page.locator('[data-home-queue]').innerText(), /Nothing waiting in this view/);
        assert.deepEqual(await page.locator('.home-summary-item strong').allTextContents(), scenario === 'denied' ? ['0','0','0','0'] : ['0','0','0','0','0']);
        await page.screenshot({path:resolve(output,`home-${scenario}.png`),fullPage:true});
    }
    await visit('mixed');
    const taskContext = page.locator('#context-priority-task-7');
    assert.equal(await taskContext.locator('[data-review-action], button[name="reviewAction"]').count(), 0);
    assert.equal(await page.locator('#priority-warning-W08-7-invalid_owner [data-review-action]').count(), 0);
    assert.equal(await page.locator('#priority-warning-W02-7 [data-review-action="close"]').count(), 0);
    assert.equal(await page.locator('#priority-warning-W07-7 [data-review-action="close"]').count(), 1);
    assert.ok((await page.locator('#priority-warning-W07-7').innerText()).includes('Plan the next follow-up'));
    // Later scenarios are independent visits; clear before scripts run, after pagehide.
    await page.addInitScript(() => sessionStorage.clear());
    // One authorised context instance moves between rail/inline layouts; no duplicate forms.
    for (const [width, height] of [[1920,1080], [1440,900], [1400,900], [1280,900], [375,812], [320,900]]) {
        await page.setViewportSize({width, height});
        await page.evaluate(() => sessionStorage.clear());
        await visit('long');
        if (width === 1440) { await measure('long','closed'); await page.screenshot({path:resolve(output,'home-long-1440-closed.png')}); }
        const toggle = page.locator('#priority-task-7 [data-home-context-toggle]');
        await toggle.focus();
        await page.keyboard.press('Enter');
        const context = page.locator('#context-priority-task-7');
        await context.locator('h3').waitFor({state:'visible'});
        await page.waitForFunction(() => !document.querySelector('.collapsing') && !document.querySelector('[data-home-activity-content][aria-busy]'));
        assert.equal(await context.count(), 1);
        assert.equal(await toggle.getAttribute('aria-expanded'), 'true');
        assert.equal(await toggle.innerText(), 'Context');
        assert.equal(await toggle.evaluate(el => el === document.activeElement), true);
        const geometry = await page.evaluate(() => ({
            rail: !document.getElementById('homeContextRail').hidden,
            railWidth: document.getElementById('homeContextRail').getBoundingClientRect().width,
            queueWidth: document.querySelector('[data-home-queue]').getBoundingClientRect().width,
            overflow: document.documentElement.scrollWidth > innerWidth,
            logoWidth: document.querySelector('#headerBlock img').getBoundingClientRect().width,
            font: getComputedStyle(document.querySelector('#priorityRows')).fontSize,
            root: getComputedStyle(document.documentElement).fontSize
        }));
        assert.equal(geometry.rail, width >= 1400);
        if (geometry.rail) {assert.equal(geometry.railWidth, 360); assert.ok(geometry.queueWidth >= 960);}
        assert.equal(geometry.overflow, false);
        assert.equal(geometry.logoWidth, 144);
        assert.equal(geometry.font, '14px');
        assert.equal(geometry.root, '16px');
        if (width === 1440) { await measure('long','open'); await page.screenshot({path:resolve(output,'home-long-1440-open.png')}); }
        await page.screenshot({path: resolve(output, `home-long-context-${width}.png`), fullPage:true});
        await page.locator('#priority-task-7 [data-home-context-jump]').click();
        assert.equal(await context.locator('h3').evaluate(el => el === document.activeElement), true);
        if (width === 375) await page.screenshot({path:resolve(output,'home-narrow-context-viewport.png')});
        // Resize while keyboard focus is inside the same panel.
        if (width === 1920) {
            await page.setViewportSize({width:1280,height:900});
            await page.waitForFunction(() => document.getElementById('homeContextRail').hidden);
            assert.equal(await context.locator('h3').evaluate(el => el === document.activeElement), true);
            await page.setViewportSize({width,height});
            await page.waitForFunction(() => !document.getElementById('homeContextRail').hidden);
            assert.equal(await context.locator('h3').evaluate(el => el === document.activeElement), true);
        }
        await page.keyboard.press('Escape');
        await context.waitFor({state:'hidden'});
        assert.equal(await toggle.getAttribute('aria-expanded'), 'false');
        assert.equal(await toggle.evaluate(el => el === document.activeElement), true);
        assert.equal(await page.locator('.home-context-open').count(), 0);
        assert.equal(await page.locator('.home-has-rail').count(), 0);
        // Switching source must hide the previous preview.
        await toggle.click();
        await page.waitForFunction(() => !document.querySelector('.collapsing') && !document.querySelector('[data-home-activity-content][aria-busy]'));
        await page.locator('#priority-task-8 [data-home-context-toggle]').click();
        await page.waitForFunction(() => !document.querySelector('.collapsing') && !document.querySelector('[data-home-activity-content][aria-busy]'));
        assert.equal(await page.locator('[data-home-context].show').count(), 1);
        assert.equal(await page.locator('.home-context-open').count(), 1);
        assert.equal(await context.isVisible(), false);
        await page.locator('#context-priority-task-8').getByRole('button', {name:'Close context', exact:true}).click();
        await page.waitForFunction(() => !document.querySelector('.collapsing') && !document.querySelector('[data-home-activity-content][aria-busy]'));
        assert.equal(await page.locator('[data-home-context].show').count(), 0);
        assert.ok(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth));
    }
    await page.setViewportSize({width:1440,height:900});
    } // Layout/interaction checks above can be retained when only the remaining fixture assertion changes.
    if (process.env.HOME_CONTINUE_AFTER_LAYOUT) {
        await page.addInitScript(() => sessionStorage.clear());
        await visit('normal',true);
    }
    if (process.env.HOME_CONTINUE_AFTER_LAYOUT !== 'review') {
    for (const scenario of ['quiet','restricted']) {
        await page.evaluate(() => sessionStorage.clear());
        await visit(scenario);
        if (scenario === 'restricted') {
            assert.equal(await page.locator('[data-home-task]').count(), 0);
            assert.equal(await page.locator('#contents').getByRole('link', {name:'Alex Taylor',exact:true}).count(), 0);
            await page.locator('#priority-task-7 [data-home-context-toggle]').click();
            await page.waitForFunction(() => !document.querySelector('.collapsing') && !document.querySelector('[data-home-activity-content][aria-busy]'));
            assert.equal(await page.getByRole('link', {name:'Edit / Reschedule'}).count(), 0);
        } else assert.equal(await page.locator('#priorityRows > li').count(), 1);
        await page.screenshot({path:resolve(output,`home-${scenario}.png`), fullPage:true});
    }
    await visit('unsafe');
    await page.locator('#priority-task-7 [data-home-context-toggle]').click();
    await page.waitForFunction(() => !document.querySelector('.collapsing') && !document.querySelector('[data-home-activity-content][aria-busy]'));
    const unsafeContext = page.locator('#context-priority-task-7');
    await unsafeContext.locator('summary').focus();
    await page.keyboard.press('Enter');
    assert.ok((await unsafeContext.innerText()).includes('<script>unsafe</script>'));
    assert.equal(await page.locator('#priority-task-7 .home-task-priority').innerText(),'Priority: Legacy <priority>');
    assert.ok((await unsafeContext.innerText()).includes('Legacy <priority>'));
    assert.equal(await page.locator('priority').count(),0);
    assert.equal(await page.locator('script').filter({hasText:'unsafe'}).count(),0);
    assert.equal(await unsafeContext.locator('script').count(),0);
    assert.equal(await unsafeContext.locator('.home-recent-activity img').count(),0);
    assert.ok((await unsafeContext.locator('.home-activity-entries a').innerText()).includes('<img src=x'));
    assert.ok((await unsafeContext.locator('.home-recent-activity').innerText()).includes('<img src=x'));
    assert.equal(await page.evaluate(()=>window.homeUnsafe),undefined);
    // Full long labels remain available through the explicit keyboard/touch disclosure.
    await visit('long');
    await page.locator('#priority-task-7 [data-home-context-toggle]').focus();
    await page.keyboard.press('Enter');
    await page.waitForFunction(() => !document.querySelector('.collapsing') && !document.querySelector('[data-home-activity-content][aria-busy]'));
    assert.ok((await page.locator('#context-priority-task-7').innerText()).includes('Northbridge Engineering, Manufacturing & Technical Recruitment Partnerships Limited'));
    assert.ok((await page.locator('#context-priority-task-7 h3').innerText()).includes('regional engineering team'));
    assert.ok(!(await page.locator('#context-priority-task-7').innerText()).includes('Call Alex about interview feedback'));
    await page.evaluate(() => document.documentElement.style.fontSize = '200%');
    await page.screenshot({path:resolve(output,'home-long-text-200-percent.png'),fullPage:true});
    assert.ok(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth), JSON.stringify(await page.evaluate(()=>Array.from(document.querySelectorAll('body *')).filter(e=>e.getBoundingClientRect().right>innerWidth+1).slice(0,15).map(e=>({tag:e.tagName,cls:e.className,right:e.getBoundingClientRect().right})))));
    assert.equal(await page.locator('#context-priority-task-7').isVisible(),true);
    await page.evaluate(() => document.documentElement.style.fontSize = '');
    }
    // Existing personal review writers still receive the native modal submitter/identity.
    for (const width of [1440,375]) {
        await page.setViewportSize({width,height:900});
        await page.evaluate(() => sessionStorage.clear());
        await visit('normal');
        await page.locator('#priority-warning-W07-7 [data-home-context-toggle]').click();
        await page.waitForFunction(() => !document.querySelector('.collapsing') && !document.querySelector('[data-home-activity-content][aria-busy]'));
        await page.locator('#context-priority-warning-W07-7 [data-review-action="close"]').click();
        const review = page.locator('#warningReviewModal');
        await page.waitForFunction(() => document.activeElement.id === 'warningReviewCloseReason');
        assert.equal(await review.locator('#warningReviewCloseReason').inputValue(), '');
        assert.equal(await review.locator('#warningReviewCloseReason').evaluate(el => el.checkValidity()), false);
        await page.screenshot({path:resolve(output,`home-review-required-${width}.png`),fullPage:true});
        await review.getByRole('button', {name:'Cancel',exact:true}).click();
        await review.waitFor({state:'hidden'});
        assert.equal(await page.evaluate(() => window.submissions || 0), 0);
        await page.locator('#context-priority-warning-W07-7 [data-review-action="close"]').click();
        await page.waitForFunction(() => document.activeElement.id === 'warningReviewCloseReason');
        await review.locator('#warningReviewCloseReason').selectOption('already_handled');
        const maximum = await review.locator('#warningReviewDeadline').getAttribute('max');
        await review.locator('#warningReviewDeadline').fill(maximum);
        await review.getByRole('button', {name:'Confirm Temporary Close'}).click();
        const submitted = await page.evaluate(() => window.submitted);
        assert.equal(submitted.warning_rule, 'W07');
        assert.equal(submitted.reviewAction, 'close');
        assert.equal(submitted.closeReason, 'already_handled');
        assert.equal(submitted.reviewUntil, maximum);
        assert.equal(submitted.homeContext, 'priority-warning-W07-7');
        assert.equal(submitted.csrfToken, 'fixture-token');
        assert.ok(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth));
    }
    await page.setViewportSize({width:1440,height:900});
    await page.evaluate(() => sessionStorage.clear());
    // Focus recipe is rendered, including forced-colour fallback.
    await visit('normal');
    await page.locator('#priority-task-7 .home-priority-actions [data-home-task]').focus();
    assert.deepEqual(await page.locator('#priority-task-7 .home-priority-actions [data-home-task]').evaluate(el => {
        const s = getComputedStyle(el); return [s.outlineWidth, s.outlineOffset, s.outlineColor, s.boxShadow];
    }), ['3px', '2px', 'rgb(16, 24, 40)', 'rgb(255, 255, 255) 0px 0px 0px 2px']);
    await page.screenshot({path:resolve(output,'home-keyboard-focus.png'),fullPage:true});
    await page.locator('#priority-task-7 [data-home-context-toggle]').focus();
    await page.keyboard.press('Enter');
    await page.locator('#priority-task-8 [data-home-context-toggle]').focus();
    await page.keyboard.press('Enter');
    await page.waitForFunction(() => !document.querySelector('.collapsing') && !document.querySelector('[data-home-activity-content][aria-busy]'));
    assert.equal(await page.locator('[data-home-context].show').count(), 1);
    assert.equal(await page.locator('.home-context-open').count(), 1);
    await page.locator('#priority-task-7 .home-priority-actions [data-home-task]').focus();
    await page.emulateMedia({forcedColors:'active'});
    assert.equal(await page.locator('#priority-task-7 .home-priority-actions [data-home-task]').evaluate(el => getComputedStyle(el).outlineStyle), 'solid');
    await page.emulateMedia({forcedColors:'none'});
    }
    // Additional whole-screen tablet/phone evidence uses actual coarse-pointer controls.
    const touchPage = await browser.newPage({hasTouch:true, deviceScaleFactor:1});
    touchPage.on('pageerror', e => errors.push(e.message));
    touchPage.on('response', r => {if (r.status() >= 400) errors.push(`${r.status()} ${r.url()}`);});
    await touchPage.addInitScript(() => sessionStorage.clear());
    for (const [width,height] of [[1024,768],[390,844],[320,844]]) {
        await touchPage.setViewportSize({width,height});
        await touchPage.goto(`http://127.0.0.1:${server.address().port}/?scenario=normal`);
        assert.ok(await touchPage.evaluate(() => matchMedia('(pointer: coarse)').matches));
        await measure('normal-touch','closed',touchPage);
        await touchPage.screenshot({path:resolve(output,`home-normal-touch-${width}-${height}-closed.png`)});
        await touchPage.locator('#priority-task-7 [data-home-context-toggle]').click();
        await touchPage.waitForFunction(() => !document.querySelector('.collapsing') && !document.querySelector('[data-home-activity-content][aria-busy]'));
        await touchPage.evaluate(() => scrollTo(0,0));
        const result = await measure('normal-touch','open',touchPage);
        assert.equal(result.overflow,false);
        assert.equal(result.railWidth,0);
        assert.equal(await touchPage.locator('#context-priority-task-7').count(),1);
        assert.ok(await touchPage.locator('.home-priority-actions .btn').evaluateAll(buttons => buttons.every(b => b.getBoundingClientRect().height >= 44)));
        await touchPage.screenshot({path:resolve(output,`home-normal-touch-${width}-${height}-open.png`)});
        await touchPage.screenshot({path:resolve(output,`home-normal-touch-${width}-full-page.png`),fullPage:true});
        await touchPage.locator('#priority-task-7 [data-home-context-jump]').click();
        assert.equal(await touchPage.locator('#context-priority-task-7 h3').evaluate(el => el === document.activeElement),true);
        await touchPage.locator('#context-priority-task-7 h3').scrollIntoViewIfNeeded();
        await touchPage.screenshot({path:resolve(output,`home-normal-touch-${width}-context-scrolled.png`)});
    }
    await touchPage.close();
    await page.setViewportSize({width:1440,height:900});
    await visit('normal',true);
    const routineAction = page.locator('#priority-task-7 .home-priority-actions [data-home-task]');
    const cueText = await page.locator('.home-due-cue, .home-task-priority').allTextContents();
    // Check computed contrast of the actual due surfaces and high priority; no token-literal assertions.
    const contrast = await page.locator('.home-due-cue, .home-priority-high, .home-summary-item, .home-summary-item strong, .home-priority-subject a, .home-priority-related a, .home-context-toggle, .home-priority-actions .btn-outline-primary, .home-priority-actions .btn-primary').evaluateAll(labels => {
        const luminance = css => css.match(/[\d.]+/g).slice(0,3).map(Number).map(v => {
            v /= 255; return v <= .04045 ? v / 12.92 : ((v + .055) / 1.055) ** 2.4;
        }).reduce((sum,v,i) => sum + v * [.2126,.7152,.0722][i],0);
        return labels.map(el => {
            let background = el;
            while (background.parentElement && getComputedStyle(background).backgroundColor === 'rgba(0, 0, 0, 0)') background = background.parentElement;
            const a = luminance(getComputedStyle(el).color), b = luminance(getComputedStyle(background).backgroundColor);
            return (Math.max(a,b)+.05)/(Math.min(a,b)+.05);
        });
    });
    assert.ok(contrast.every(ratio => ratio >= 4.5));
    await routineAction.hover();
    assert.equal(await routineAction.isEnabled(),true);
    await page.screenshot({path:resolve(output,'home-action-hover.png')});
    await routineAction.focus();
    await page.emulateMedia({forcedColors:'active'});
    assert.deepEqual(await page.locator('.home-due-cue, .home-task-priority').allTextContents(),cueText);
    assert.equal(await routineAction.evaluate(el => getComputedStyle(el).outlineStyle),'solid');
    await page.screenshot({path:resolve(output,'home-forced-colours.png')});
    await page.emulateMedia({forcedColors:'none'});
    await visit('empty',true);
    await page.screenshot({path:resolve(output,'home-empty-1440-900-viewport.png')});
    await laptopCases(true);
    // Subject identity is part of the Activity contract; notes and recorder names are not a substitute.
    for (const [scenario,expected] of [
        ['empty','0\nVisible warnings\nIncluding: 0 recruitment warnings · 0 client follow-ups'],
        ['summary-single','1\nVisible warning\nIncluding: 0 recruitment warnings · 1 client follow-up'],
        ['warning-led','4\nVisible warnings\nIncluding: 3 recruitment warnings · 1 client follow-up']
    ]) {
        await visit(scenario,true);
        assert.equal((await page.locator('.home-summary-item').nth(3).innerText()).replace(/\s+/g,' '),expected.replace(/\s+/g,' '));
        assert.match(await page.locator('.home-summary-item').nth(3).getAttribute('href'),/a=priorities&filter=warnings$/);
    }
    for (const [width,height] of [[1920,1080],[1440,900],[1366,768],[1280,800],[390,844],[320,844]]) {
        await page.setViewportSize({width,height});
        for (const [activity,rowID,names,destinations] of [
            ['mixed','priority-task-9',['Priya Shah','Helen Ward','Alex Taylor'],['m=candidates&a=show&candidateID=21','m=contacts&a=show&contactID=12','m=candidates&a=show&candidateID=22']],
            ['mixed-company','priority-task-8',['Daniel Brooks','Helen Ward','Morgan & Co'],['m=contacts&a=show&contactID=13','m=contacts&a=show&contactID=12','m=companies&a=show&companyID=7']]
        ]) {
            await page.goto(`http://127.0.0.1:${server.address().port}/?scenario=normal&fresh=1&activity=${activity}`);
            await page.locator(`#${rowID} [data-home-context-toggle]`).click();
            await page.waitForFunction(()=>!document.querySelector('.collapsing') && !document.querySelector('[aria-busy]'));
            const panel = page.locator(`#context-${rowID}`);
            const entries = panel.locator('.home-activity-entries > li');
            assert.equal(await entries.count(),3);
            assert.equal(await panel.getByRole('link',{name:'View record & full Activity'}).getAttribute('href'),'index.php?'+(activity==='mixed'?'m=joborders&a=show&jobOrderID=7':'m=companies&a=show&companyID=7'));
            assert.deepEqual(await entries.locator('a').allTextContents(),names);
            assert.deepEqual(await entries.locator('a').evaluateAll(els=>els.map(el=>el.getAttribute('href'))),destinations.map(path=>'index.php?'+path));
            assert.deepEqual(await entries.locator('.text-body-secondary').filter({hasText:'Recorded by'}).allTextContents(),Array(3).fill('Recorded by Maya Patel'));
            assert.ok((await entries.nth(0).innerText()).includes('Called; awaiting feedback.'));
            assert.equal(await page.locator('[data-home-context].show').count(),1);
            const actions = panel.locator('nav[aria-label="Task actions"]');
            assert.equal(await actions.getByRole('link',{name:'Edit / Reschedule'}).isVisible(),true);
            assert.equal(await actions.evaluate(el=>Boolean(el.compareDocumentPosition(el.parentElement.querySelector('.home-recent-activity')) & Node.DOCUMENT_POSITION_FOLLOWING)),true);
            await page.evaluate(()=>scrollTo(0,0));
            const result = await measure(activity,'open');
            assert.equal(result.overflow,false);
            assert.equal(result.railWidth,width>=1400?360:0);
            if(width>=1400) assert.ok(result.queueWidth>=960);
            const name=`activity-${activity}-${width}-${height}`;
            await page.screenshot({path:resolve(output,`${name}-viewport.png`)});
            await page.screenshot({path:resolve(output,`${name}-full.png`),fullPage:true});
            await panel.screenshot({path:resolve(output,`${name}-context.png`)});
        }
    }
    writeFileSync(resolve(output,process.env.HOME_CONTINUE_AFTER_LAYOUT ? 'remaining-measurements.json' : (process.env.HOME_RESPONSIVE_ONLY === '1' ? 'responsive-measurements.json' : 'measurements.json')), JSON.stringify(measurements,null,2));
    writeFileSync(resolve(output,'cue-contrast.json'), JSON.stringify(contrast,null,2));
    console.log(JSON.stringify(measurements.cases.map(({dataset,state,width,firstRowTop,visibleRows,footerVisible,overflow}) => ({dataset,state,width,firstRowTop,visibleRows,footerVisible,overflow})),null,2));
    // Native review policy and explicit Close selection are exercised in checkWarningReviews.mjs.
    assert.deepEqual(errors, []);
    console.log(process.env.HOME_CONTINUE_AFTER_LAYOUT ? 'PASS: remaining review-modal, keyboard/forced-colour focus, touch/reflow and semantic contrast checks; earlier layout/interaction checks were not rerun.' : process.env.HOME_RESPONSIVE_ONLY === '1' ? 'PASS: supplemental coarse-pointer tablet/phone/320px reflow, semantic cue contrast, forced colours and hover evidence.' : 'PASS: eight-row queue, closed context, exact full-list totals and second page, empty/denied state, escaped labels, native Complete POST identity/CSRF/return, cancel, duplicate submission guard, edit link, stored context, keyboard modal, native shell, long/quiet/restricted fixtures, single selected preview, 360px rail and inline resize/focus, 1920/1440/1400/1280/375/320 widths, and two-colour/forced-colour focus.');
    }
} finally {await browser.close(); await new Promise(r=>server.close(r));}
