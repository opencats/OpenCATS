/* Focused native Bootstrap contracts using the existing CLI fixture/Playwright pattern.
 * PLAYWRIGHT_MODULE=/path/to/playwright/index.mjs CHROMIUM_PATH=/path/to/chromium node test/scripts/checkWarningReviews.mjs
 * Does not exercise authenticated application navigation or database writes.
 */
import { createServer } from 'node:http';
import { readFileSync, mkdirSync } from 'node:fs';
import { execFileSync } from 'node:child_process';
import { resolve, dirname, extname } from 'node:path';
import { fileURLToPath } from 'node:url';
import assert from 'node:assert/strict';
const { chromium } = await import(process.env.PLAYWRIGHT_MODULE || 'playwright');
const root = resolve(dirname(fileURLToPath(import.meta.url)), '../..');
const output = process.env.WARNING_SCREENSHOTS || '/tmp/opencats-warning-screenshots';
mkdirSync(output, { recursive: true });
const fixtures = Object.fromEntries(['home', 'companies', 'joborders'].map(kind => [kind,
    execFileSync(process.env.PHP_BINARY || 'php', [resolve(root, 'test/scripts/renderWarningReviews.php'), kind], { encoding: 'utf8' })]));
const server = createServer((req, res) => {
    const url = new URL(req.url, 'http://localhost');
    if (url.pathname === '/') { res.setHeader('Content-Type', 'text/html'); res.end(fixtures[url.searchParams.get('view') || 'home']); return; }
    const path = resolve(root, '.' + url.pathname);
    if (!path.startsWith(root + '/') || !['.js', '.css'].includes(extname(path))) { res.writeHead(404); res.end(); return; }
    try { res.setHeader('Content-Type', extname(path) === '.js' ? 'text/javascript' : 'text/css'); res.end(readFileSync(path)); }
    catch { res.writeHead(404); res.end(); }
});
await new Promise(r => server.listen(0, '127.0.0.1', r));
const browser = await chromium.launch({ headless: true, ...(process.env.CHROMIUM_PATH ? { executablePath: process.env.CHROMIUM_PATH } : {}) });
const errors = [];
try {
    const page = await browser.newPage();
    page.on('pageerror', e => errors.push(e.message));
    page.on('response', res => { if (res.status() >= 400) errors.push(`${res.status()} ${res.url()}`); });
    for (const view of ['home', 'companies', 'joborders']) {
        await page.goto(`http://127.0.0.1:${server.address().port}/?view=${view}`);
        assert.equal(await page.locator('script[src*="bootstrap.bundle"]').count(), 1);
        assert.equal(await page.locator('#warningReviewModal').count(), 1);
        if (view === 'home') {
            for (const context of await page.locator('[data-home-context-toggle]').all()) await context.click();
        }
        if (view !== 'companies') {
            await page.getByRole('button', { name: 'Mark Reviewed — 5 working days' }).first().click();
            const reviewed = await page.evaluate(() => window.submitted);
            assert.equal(reviewed.reviewAction, 'remind_later');
            assert.equal(reviewed.warning_rule, 'W02');
        }
        const trigger = page.locator('[data-review-action="remind_later"]').first();
        const expected = JSON.parse(await trigger.getAttribute('data-review'));
        const modal = page.locator('#warningReviewModal');
        await trigger.focus();
        await page.keyboard.press('Enter');
        await modal.waitFor({ state: 'visible' });
        await page.waitForFunction(() => document.activeElement.id === 'warningReviewReminder');
        assert.equal(await modal.locator('#warningReviewReminder').evaluate(el => el.selectedIndex), 1);
        assert.equal(await modal.locator('[name="expectedVersion"]').inputValue(), '3');
        assert.equal(await modal.locator('[name="expectedEvidence"]').inputValue(), 'a'.repeat(64));
        await modal.locator('#warningReviewReminder').selectOption({ index: 2 });
        const date = await modal.locator('#warningReviewReminder').inputValue();
        await modal.getByRole('button', { name: 'Confirm Remind Later' }).click();
        const post = await page.evaluate(() => window.submitted);
        for (const key of ['warning_rule', 'data_item_type', 'data_item_id', 'reason_key', 'reason_id', 'returnTo']) assert.equal(post[key], String(expected[key]));
        assert.equal(post.reviewUntil, date);
        assert.equal(post.reviewAction, 'remind_later');
        assert.equal(post.csrfToken, 'fixture-token');
        assert.ok(!('closeReason' in post));
        await page.keyboard.press('Escape');
        await modal.waitFor({ state: 'hidden' });
        await page.waitForFunction(() => document.activeElement.dataset.reviewAction === 'remind_later');
        // Reopening restores the HTML default after a different duration was selected.
        await trigger.click();
        await page.waitForFunction(() => document.activeElement.id === 'warningReviewReminder');
        assert.equal(await modal.locator('#warningReviewReminder').evaluate(el => el.selectedIndex), 1);
        await modal.getByRole('button', { name: 'Cancel', exact: true }).click();
        await modal.waitFor({ state: 'hidden' });
        assert.deepEqual(await page.evaluate(() => window.submitted), post);
        if (view !== 'joborders') {
            await page.locator('[data-review-action="close"]').first().click();
            await modal.waitFor({ state: 'visible' });
            assert.equal(await modal.locator('#warningReviewCloseReason').inputValue(), '');
            assert.equal(await modal.locator('#warningReviewCloseReason').evaluate(el => el.checkValidity()), false);
            await modal.locator('#warningReviewCloseReason').selectOption('incorrect_warning');
            const closeDate = await modal.locator('#warningReviewDeadline').getAttribute('max');
            await modal.locator('#warningReviewDeadline').fill(closeDate);
            await modal.getByRole('button', { name: 'Confirm Temporary Close' }).click();
            const close = await page.evaluate(() => window.submitted);
            assert.equal(close.warning_rule, 'W07');
            assert.equal(close.closeReason, 'incorrect_warning');
            assert.equal(close.reviewUntil, closeDate);
            assert.notEqual(close.requestID, post.requestID);
            await modal.getByRole('button', { name: 'Cancel', exact: true }).click();
            await modal.waitFor({ state: 'hidden' });
            await page.locator('[data-review-action="close"]:visible').last().click();
            await page.waitForFunction(() => document.activeElement.id === 'warningReviewCloseReason');
            assert.equal(await modal.locator('#warningReviewCloseReason').inputValue(), '');
            await modal.getByRole('button', { name: 'Cancel', exact: true }).click();
            await modal.waitFor({ state: 'hidden' });
            assert.deepEqual(await page.evaluate(() => window.submitted), close);
        } else assert.equal(await page.locator('[data-review-action="close"]').count(), 0);
        if (view === 'home') {
            await page.getByRole('button', { name: 'Personally Reviewed / Remind Later (2)' }).click();
            await page.locator('#suppressedWarnings.show').waitFor();
            await page.locator('#suppressedWarnings').getByRole('button', { name: 'Show Again' }).first().click();
            assert.equal((await page.evaluate(() => window.submitted)).reviewAction, 'reset');
        }
        for (const reminder of await page.locator('[data-review-action="remind_later"]:visible').all()) {
            await reminder.click();
            await page.waitForFunction(() => document.activeElement.id === 'warningReviewReminder');
            assert.equal(await modal.locator('#warningReviewReminder').evaluate(el => el.selectedIndex), 1);
            await modal.locator('#warningReviewReminder').selectOption({ index: 0 });
            await modal.getByRole('button', { name: 'Cancel', exact: true }).click();
            await modal.waitFor({ state: 'hidden' });
        }
        await page.screenshot({ path: resolve(output, view + '-desktop.png'), fullPage: true });
        await page.setViewportSize({ width: 375, height: 812 });
        assert.ok(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth));
        await trigger.click();
        await modal.waitFor({ state: 'visible' });
        await page.waitForFunction(() => document.activeElement.id === 'warningReviewReminder');
        await page.screenshot({ path: resolve(output, view + '-mobile-modal.png'), fullPage: true });
        await page.keyboard.press('Escape');
        await modal.waitFor({ state: 'hidden' });
        await page.setViewportSize({ width: 1280, height: 900 });
    }
    assert.deepEqual(errors, []);
    console.log('PASS: five-day defaults and reopening, explicit Close reason and reset, cancel without submit, Home/Company/Job Order modal identity, reminder and Close POST fields, keyboard focus/Escape, Collapse, Show Again, mobile widths, single bundle and Task panel coexistence.');
} finally { await browser.close(); await new Promise(r => server.close(r)); }
