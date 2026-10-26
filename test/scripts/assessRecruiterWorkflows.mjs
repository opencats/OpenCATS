/* Focused assessment driver: existing Playwright/native application pattern.
 * Requires an explicitly disposable app; never points at the primary checkout. */
import {mkdirSync,existsSync,writeFileSync} from 'node:fs';
import {resolve} from 'node:path';
import {execFileSync} from 'node:child_process';
import assert from 'node:assert/strict';
const {chromium} = await import(process.env.PLAYWRIGHT_MODULE || 'playwright');
const base = process.env.WORKFLOW_BASE_URL;
assert.equal(process.env.OPENCATS_WORKFLOW_DISPOSABLE,'1');
const appIP = execFileSync('docker',['inspect','opencats-workflow-assessment-php','--format','{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}'],{encoding:'utf8'}).trim();
assert.equal(new URL(base).hostname,appIP);
assert.equal(execFileSync('docker',['network','inspect','opencats-workflow-assessment-net','--format','{{.Internal}}'],{encoding:'utf8'}).trim(),'true');
const output = process.env.WORKFLOW_EVIDENCE;
assert.ok(output);
mkdirSync(output,{recursive:true});
const phase = process.argv[2] || 'inspect';
const role = process.env.WORKFLOW_ROLE || 'testerEdit';
assert.ok(['testerEdit','testerRead'].includes(role));
const state = `/tmp/opencats-workflow-${role}-localized-state.json`;
const browser = await chromium.launch({headless:true,executablePath:process.env.CHROMIUM_PATH});
const context = await browser.newContext({timezoneId:process.env.WORKFLOW_BROWSER_TIMEZONE || 'UTC',viewport:{width:1920,height:1080},...(existsSync(state)?{storageState:state}:{})});
context.setDefaultTimeout(15000);
context.setDefaultNavigationTimeout(30000);
let exercised = false;
const page = await context.newPage();
const errors = [];
page.on('pageerror',e=>errors.push(e.message));
page.on('dialog',dialog=>dialog.dismiss());
await context.route('**/*',route=>new URL(route.request().url()).origin===new URL(base).origin?route.continue():route.abort());
const go = path=>page.goto(`${base}/index.php?${path}`);
const snapshot = async name=>{
    await page.screenshot({path:resolve(output,`${name}.png`)});
    writeFileSync(resolve(output,`${name}.json`),JSON.stringify(await page.evaluate(()=>({
        url:location.href,width:innerWidth,height:innerHeight,dpr:devicePixelRatio,scrollY,timezone:Intl.DateTimeFormat().resolvedOptions().timeZone,
        overflow:document.documentElement.scrollWidth>innerWidth,text:document.body.innerText
    })),null,2));
};
const inspect = async target=>console.log(JSON.stringify(await target.locator('input:not([type=hidden]),select,textarea,button,a').evaluateAll(els=>els.map(el=>({tag:el.tagName,id:el.id,name:el.name,text:el.innerText?.trim().slice(0,100),href:el.getAttribute('href'),value:el.type==='password'?'[password]':el.value,options:el.tagName==='SELECT'?Array.from(el.options).map(o=>[o.value,o.text]):undefined}))),null,2));
const db = sql=>execFileSync('docker',['exec','opencats-workflow-assessment-db','mysql','-udev','-pdev','cats_test','--batch','--raw','-e',sql],{encoding:'utf8'});
const statusChange = async (job,candidate,status,name) => {
    await go(`m=joborders&a=show&jobOrderID=${job}`);
    await page.locator(`a[onclick*="a=changeStatus"][onclick*="candidateID=${candidate}"]`).click();
    const frame=page.frameLocator('#popupFrameIFrame');
    await frame.locator('#statusID').selectOption(status);
    if (await frame.locator('#triggerEmail').isChecked()) await frame.locator('#triggerEmail').uncheck();
    await snapshot(`${name}-status-dialog`);
    await frame.locator('#submit').click();
    await Promise.all([page.waitForNavigation(),frame.getByRole('button',{name:'Close',exact:true}).click()]);
    await page.reload();
    assert.match(db(`SELECT status FROM candidate_joborder WHERE candidate_id=${candidate} AND joborder_id=${job}`),new RegExp(`\\n${status}\\n`));
    await snapshot(`${name}-reloaded`);
};
try {
    await go('m=home');
    if (await page.locator('#username').count()) {
        await page.locator('#username').fill(role);
        await page.locator('#password').fill('opencats-assessment-only');
        await page.locator('#loginForm').getByRole('button',{name:'Login',exact:true}).click();
        await page.waitForURL(url=>url.searchParams.get('m')==='home');
    }
    assert.equal(await page.locator('#username').count(),0);
    if (phase === 'inspect') {
        exercised = true;
        if (process.argv[3]) await go(process.argv[3]);
        console.log((await page.locator('body').innerText()).slice(0,11000));
        await inspect(page);
        await snapshot('inspect');
    }
    if (phase === 'A' || phase === 'A-return') {
        exercised = true;
        if (phase === 'A') {
        const baseline = db("SELECT (SELECT COUNT(*) FROM activity) AS activities, (SELECT COUNT(*) FROM candidate_joborder_status_history) AS progress");
        for (const [width,height] of [[1920,1080],[1440,900],[1366,768],[1024,768],[390,844]]) {
            await page.setViewportSize({width,height});
            await go('m=home');
            await snapshot(`A-start-${width}-${height}`);
        }
        await page.setViewportSize({width:1920,height:1080});
        await go('m=home');
        assert.deepEqual((await page.locator('.home-summary-item strong').allTextContents()).slice(0,3),['1','1','4']);
        await page.locator('#priority-task-970001 [data-home-context-toggle]').click();
        await page.waitForFunction(()=>!document.querySelector('.collapsing'));
        await snapshot('A-task-context');
        await page.locator('#priority-task-970001 [data-home-task]').click();
        await page.locator('#homeTaskComplete').getByRole('button',{name:'Cancel',exact:true}).click();
        await page.locator('#homeTaskComplete').waitFor({state:'hidden'});
        assert.match(db('SELECT status FROM task WHERE task_id=970001'),/open/);
        await page.locator('#priority-task-970001 [data-home-task]').click();
        await page.locator('#homeTaskComplete').getByRole('button',{name:'Complete Task',exact:true}).click();
        await page.waitForURL(url=>url.searchParams.get('taskNotice')==='saved');
        await page.reload();
        assert.equal(await page.locator('#priority-task-970001').count(),0);
        assert.match(db('SELECT status,completed_by FROM task WHERE task_id=970001'),/completed\s+10003/);
        assert.equal(db("SELECT (SELECT COUNT(*) FROM activity) AS activities, (SELECT COUNT(*) FROM candidate_joborder_status_history) AS progress"),baseline);
        await snapshot('A-completed-reloaded');
        }
        await go('m=home&a=priorities&filter=tasks&page=1');
        await page.locator('#priority-task-970002 [data-home-context-toggle]').click();
        await page.waitForFunction(()=>!document.querySelector('.collapsing'));
        await page.locator('#context-priority-task-970002').getByRole('link',{name:'Edit / Reschedule'}).click();
        await page.locator('#dueDate_Day_ID').selectOption({label:'19'});
        await snapshot('A-reschedule-native-form');
        await page.getByRole('button',{name:'Save Task',exact:true}).click();
        await page.waitForURL(url=>url.searchParams.get('m')==='home');
        assert.equal(new URL(page.url()).searchParams.get('filter'),'tasks');
        assert.equal(new URL(page.url()).searchParams.get('page'),'1');
        await page.reload();
        assert.match(db('SELECT due_date,priority FROM task WHERE task_id=970002'),/2026-10-19\s+normal/);
        await snapshot('A-rescheduled-return');
        await go('m=home&a=priorities&filter=warnings&page=1');
        const reviewRow = page.locator('#priority-warning-W03-970002');
        await reviewRow.locator('[data-home-context-toggle]').click();
        await page.waitForFunction(()=>!document.querySelector('.collapsing'));
        await page.locator('#context-priority-warning-W03-970002').getByRole('button',{name:'Mark Reviewed — 5 working days'}).click();
        await page.waitForURL(url=>url.searchParams.get('m')==='home');
        await page.reload();
        assert.match(db("SELECT warning_rule,action,review_until FROM warning_review WHERE reviewing_user=10003 AND data_item_id=970002"),/W03\s+remind_later/);
        await page.getByRole('button',{name:/Personally Reviewed/}).click();
        await page.waitForFunction(()=>!document.querySelector('.collapsing'));
        await snapshot('A-personal-review-recovery');
        writeFileSync(resolve(output,'A-database-evidence.txt'),db("SELECT task_id,title,due_date,priority,status,completed_by FROM task WHERE task_id BETWEEN 970001 AND 970004; SELECT warning_rule,action,review_until,reviewing_user FROM warning_review WHERE reviewing_user=10003; SELECT data_item_id,the_field,previous_value,new_value FROM history WHERE data_item_type=1000 AND data_item_id BETWEEN 970001 AND 970004;"));
    }

    if (phase === 'B' || phase === 'B-return') {
        exercised = true;
        if (phase === 'B') {
        await go('m=contacts&a=show&contactID=970001');
        await page.locator('#addActivityLink').click();
        const frame = page.frameLocator('#popupFrameIFrame');
        await frame.locator('#activityTypeID').selectOption('500');
        await frame.locator('#activityNote').fill('Helen confirmed electrical certification is essential; agreed to review two CNC maintenance profiles on Monday.');
        await snapshot('B-contact-conversation-dialog');
        await frame.locator('#submit').click();
        await frame.getByRole('button',{name:'Close',exact:true}).click();
        await page.waitForURL(url=>url.searchParams.get('m')==='contacts');
        await page.reload();
        assert.ok((await page.locator('body').innerText()).includes('Helen confirmed electrical certification'));
        }
        await go('m=contacts&a=show&contactID=970001');
        await page.locator('[data-task-quick-add]').click();
        const quick = page.locator('#task-quick-add-modal');
        await quick.locator('#quick-task-title').fill('Review maintenance profiles with Helen');
        await quick.locator('#dueDate_Month_ID').selectOption({label:'Oct'});
        await quick.locator('#dueDate_Day_ID').selectOption({label:'19'});
        await quick.getByRole('button',{name:'More options',exact:true}).click();
        await quick.locator('[name="purpose"]').selectOption('relationship_follow_up');
        await snapshot('B-contextual-add-task-dialog');
        await quick.locator('[type="submit"]').click();
        await quick.waitFor({state:'hidden'});
        await page.reload();
        assert.ok((await page.locator('[data-task-panel]').innerText()).includes('Review maintenance profiles with Helen'));
        await snapshot('B-contact-activity-and-task');
        await page.getByRole('link',{name:'Northbridge Precision',exact:true}).first().click();
        await snapshot('B-company-relationship-evidence');
        await go('m=home&a=priorities&filter=tasks');
        assert.ok((await page.locator('#priorityRows').innerText()).includes('Review maintenance profiles with Helen'));
        await snapshot('B-home-follow-up');
        writeFileSync(resolve(output,'B-database-evidence.txt'),db("SELECT task_id,title,due_date,purpose,data_item_type,data_item_id FROM task WHERE title='Review maintenance profiles with Helen'; SELECT data_item_type,data_item_id,type,notes,date_occurred FROM activity WHERE data_item_id=970001 ORDER BY activity_id;"));
    }
    if (phase === 'C') {
        exercised = true;
        await go('m=companies&a=show&companyID=970001');
        await page.getByRole('link',{name:'CNC Maintenance Engineer',exact:true}).first().click();
        assert.equal(new URL(page.url()).searchParams.get('jobOrderID'),'970001');
        await snapshot('C-job-native-details');
        await page.locator('a[href*="a=edit&jobOrderID=970001"]').first().click();
        await page.locator('.sun-editor-editable').fill('Permanent CNC maintenance engineer. Electrical certification essential; planned maintenance and fault finding; day shift. Helen will review two suitable profiles.');
        await page.locator('#status').selectOption('On Hold');
        await snapshot('C-amend-brief-hold-form');
        await page.locator('#submit').click();
        await page.waitForURL(url=>url.searchParams.get('a')==='show');
        await page.reload();
        assert.match(db('SELECT status,description,owner,recruiter,contact_id,first_actionable_at FROM joborder WHERE joborder_id=970001'),/On Hold/);
        await snapshot('C-on-hold-reloaded');
        await page.locator('a[href*="a=edit&jobOrderID=970001"]').first().click();
        await page.locator('#status').selectOption('Active');
        await page.locator('#submit').click();
        await page.waitForURL(url=>url.searchParams.get('a')==='show');
        await page.reload();
        assert.match(db('SELECT status,description FROM joborder WHERE joborder_id=970001'),/Active.*Electrical certification/s);
        await snapshot('C-resumed-reloaded');
        writeFileSync(resolve(output,'C-database-evidence.txt'),db("SELECT joborder_id,title,status,company_id,contact_id,owner,recruiter,openings,openings_available,first_actionable_at,description FROM joborder WHERE joborder_id>=970001; SELECT the_field,previous_value,new_value FROM history WHERE data_item_type=400 AND data_item_id=970001 ORDER BY history_id;"));
    }
    if (phase === 'D-align') {
        exercised = true;
        await go('m=home&a=priorities&filter=warnings');
        await page.locator('#priority-warning-W02-970001').getByRole('link',{name:'Open Job Order',exact:true}).click();
        assert.equal(new URL(page.url()).searchParams.get('jobOrderID'),'970001');
        await snapshot('D-home-to-correct-job');
        for (const name of ['Alex','Priya']) {
            await page.getByRole('link',{name:'Add Candidate to This Job Order'}).click();
            const frame=page.frameLocator('#popupFrameIFrame');
            await frame.locator('#wildCardString_fullname').fill(name);
            await frame.locator('#searchByFullName').click();
            await frame.getByRole('button',{name,exact:true}).waitFor();
            await snapshot(`D-search-${name}`);
            const popupPromise=context.waitForEvent('page');
            await frame.locator('a[title="Show Candidate"]').first().click();
            const popup=await popupPromise;
            await popup.waitForLoadState();
            assert.ok((await popup.locator('body').innerText()).includes(name));
            await popup.screenshot({path:resolve(output,`D-review-${name}-candidate-popup.png`)});
            await popup.close();
            await frame.getByRole('button',{name,exact:true}).click();
            await frame.getByRole('button',{name:'Close',exact:true}).click();
            await page.waitForURL(url=>url.searchParams.get('jobOrderID')==='970001');
            await page.reload();
        }
        await snapshot('D-two-independent-alignments');
        console.log(db('SELECT candidate_id,joborder_id,status FROM candidate_joborder WHERE joborder_id=970001'));
        await inspect(page);
    }
    if (phase === 'D-submit') {
        exercised = true;
        await statusChange(970001,970001,'400','D-submit-Alex');
        await statusChange(970001,970002,'400','D-submit-Priya');
        writeFileSync(resolve(output,'D-database-evidence.txt'),db("SELECT candidate_id,joborder_id,status FROM candidate_joborder WHERE joborder_id>=970001; SELECT candidate_id,joborder_id,status_from,status_to,date FROM candidate_joborder_status_history WHERE joborder_id>=970001 ORDER BY candidate_joborder_status_history_id;"));
        await go('m=home&a=priorities&filter=warnings');
        assert.equal(await page.locator('#priority-warning-W02-970001').count(),0);
        await snapshot('D-submitted-warning-recalculation');
    }
    if (phase === 'B-default') {
        exercised = true;
        await go('m=home&a=priorities&filter=warnings');
        await page.locator('#priority-warning-W07-970002').getByRole('link',{name:'Add follow-up',exact:true}).click();
        assert.equal(await page.locator('#purpose').inputValue(),'general');
        await page.locator('#title').fill('Confirm remaining warehouse staffing with Daniel');
        await page.locator('#dueDate_Month_ID').selectOption({label:'Oct'});
        await page.locator('#dueDate_Day_ID').selectOption({label:'20'});
        await snapshot('B-home-add-follow-up-general-default');
        await page.getByRole('button',{name:'Save Task',exact:true}).click();
        await page.waitForURL(url=>url.searchParams.get('m')==='home');
        await page.reload();
        assert.ok((await page.locator('#priority-warning-W07-970002').innerText()).includes('Plan the next follow-up'));
        await snapshot('B-saved-follow-up-warning-remains');
        writeFileSync(resolve(output,'B-default-database-evidence.txt'),db("SELECT title,purpose,due_date,data_item_type,data_item_id FROM task WHERE title='Confirm remaining warehouse staffing with Daniel'; SELECT NOW() AS db_clock, date_occurred,notes FROM activity WHERE notes LIKE 'Helen confirmed%';"));
    }
    if (phase === 'E-start' || phase === 'E-calendar') {
        exercised = true;
        if (phase === 'E-start') {
        await statusChange(970001,970001,'500','E-interview-Alex');
        await statusChange(970001,970002,'500','E-interview-Priya');
        }
        await go('m=joborders&a=show&jobOrderID=970001');
        await page.locator('a[onclick*="a=addActivity"][onclick*="candidateID=970001"]').click();
        const frame=page.frameLocator('#popupFrameIFrame');
        await frame.locator('#activityTypeID').selectOption('500');
        await frame.locator('#activityNote').fill('Helen and Alex agreed an onsite technical interview. Confirm electrical certification and planned maintenance experience.');
        await frame.locator('#scheduleEvent').check();
        await frame.locator('#dateAdd_Month_ID').selectOption({label:'Oct'});
        await frame.locator('#dateAdd_Day_ID').selectOption({label:'20'});
        await frame.locator('#hour').selectOption('10');
        await frame.locator('#minute').selectOption('00');
        await frame.locator('#title').fill('Alex Morgan - CNC technical interview');
        await frame.locator('#description').fill('Northbridge Precision with Helen Ward; bring electrical certification.');
        await snapshot('E-interview-activity-calendar-dialog');
        await frame.locator('#submit').click();
        await Promise.all([page.waitForNavigation(),frame.getByRole('button',{name:'Close',exact:true}).click()]);
        await page.reload();
        await snapshot('E-interview-recorded');
        writeFileSync(resolve(output,'E-interview-database-evidence.txt'),db("SELECT calendar_event_id,type,date,title,data_item_type,data_item_id,joborder_id FROM calendar_event WHERE title LIKE 'Alex Morgan%';"));
        await go('m=calendar&view=DAYVIEW&year=2026&month=10&day=20');
        await snapshot('E-calendar-focused-workspace');
        await inspect(page);
    }
    if (phase === 'E-finish') {
        exercised = true;
        await go('m=calendar&view=DAYVIEW&year=2026&month=10&day=20');
        await page.locator('.calendarEntry').filter({hasText:'Alex Morgan - CNC technical interview'}).first().click();
        await page.getByRole('button',{name:'Edit Event',exact:true}).click();
        const edit=page.locator('#editEventForm');
        await edit.locator('#dateEdit_Day_ID').selectOption({label:'21'});
        await edit.locator('#hourEdit').selectOption('11');
        await snapshot('E-reschedule-calendar-form');
        await edit.getByRole('button',{name:'Save',exact:true}).click();
        await page.waitForLoadState();
        await go('m=calendar&view=DAYVIEW&year=2026&month=10&day=21');
        await page.reload();
        assert.match(db("SELECT date FROM calendar_event WHERE title='Alex Morgan - CNC technical interview'"),/2026-10-21 11:00:00/);
        await snapshot('E-rescheduled-calendar-reloaded');
        await go('m=joborders&a=show&jobOrderID=970001');
        await page.locator('a[onclick*="a=addActivity"][onclick*="candidateID=970001"]').click();
        const frame=page.frameLocator('#popupFrameIFrame');
        await frame.locator('#activityTypeID').selectOption('500');
        await frame.locator('#activityNote').fill('Client feedback: Alex meets the electrical maintenance requirement. Candidate feedback: Alex accepts the day shift and wishes to proceed. Agree offer details after the scheduled technical interview.');
        await frame.locator('#submit').click();
        await Promise.all([page.waitForNavigation(),frame.getByRole('button',{name:'Close',exact:true}).click()]);
        await page.reload();
        await snapshot('E-job-linked-feedback-reloaded');
        await statusChange(970001,970001,'600','E-offer-Alex');
        await statusChange(970001,970002,'700','E-client-declines-Priya');
        writeFileSync(resolve(output,'E-final-database-evidence.txt'),db("SELECT candidate_id,joborder_id,status FROM candidate_joborder WHERE joborder_id=970001; SELECT calendar_event_id,date,title,data_item_id,joborder_id FROM calendar_event WHERE title LIKE 'Alex Morgan%'; SELECT data_item_id,joborder_id,notes FROM activity WHERE data_item_id=970001 ORDER BY activity_id DESC LIMIT 6;"));
    }
    if (phase === 'F') {
        exercised = true;
        await statusChange(970001,970001,'800','F-place-Alex');
        await go('m=joborders&a=show&jobOrderID=970002');
        for (const name of ['Sam','Jamie']) {
            await page.getByRole('link',{name:'Add Candidate to This Job Order'}).click();
            const frame=page.frameLocator('#popupFrameIFrame');
            await frame.locator('#wildCardString_fullname').fill(name);
            await frame.locator('#searchByFullName').click();
            await frame.getByRole('button',{name,exact:true}).click();
            await Promise.all([page.waitForNavigation(),frame.getByRole('button',{name:'Close',exact:true}).click()]);
            await page.reload();
        }
        await statusChange(970002,970003,'400','F-submit-Sam');
        await statusChange(970002,970004,'400','F-submit-Jamie');
        await statusChange(970002,970003,'800','F-place-Sam');
        assert.match(db('SELECT status,openings,openings_available FROM joborder WHERE joborder_id=970002'),/Active\s+3\s+2/);
        assert.match(db('SELECT status FROM candidate_joborder WHERE candidate_id=970004 AND joborder_id=970002'),/400/);
        await snapshot('F-industrial-remaining-vacancies');
        await go('m=candidates&a=show&candidateID=970003');
        await snapshot('F-placed-candidate-workspace');
        writeFileSync(resolve(output,'F-database-evidence.txt'),db("SELECT joborder_id,title,status,openings,openings_available,company_id,contact_id,owner,recruiter,salary,rate_max,start_date FROM joborder WHERE joborder_id>=970001; SELECT candidate_id,joborder_id,status FROM candidate_joborder WHERE joborder_id>=970001; SELECT candidate_id,joborder_id,status_from,status_to,date FROM candidate_joborder_status_history WHERE joborder_id>=970001 ORDER BY candidate_joborder_status_history_id;"));
    }
    if (phase === 'R') {
        exercised = true;
        assert.equal(role,'testerRead');
        await go('m=home');
        assert.equal((await page.locator('.home-summary-item strong').allTextContents())[2],'0');
        assert.equal(await page.locator('[data-home-task]').count(),0);
        await snapshot('R-read-only-home');
        await go('m=tasks&a=show&taskID=970003');
        assert.equal(await page.getByRole('link',{name:'Edit / Reschedule',exact:true}).count(),0);
        assert.equal(await page.getByRole('button',{name:'Complete Task',exact:true}).count(),0);
        await snapshot('R-readable-task-no-write-actions');
        const before = db('SELECT status,due_date FROM task WHERE task_id=970003');
        await go('m=tasks&a=edit&taskID=970003');
        assert.ok((await page.locator('body').innerText()).includes('Task update denied'));
        assert.equal(db('SELECT status,due_date FROM task WHERE task_id=970003'),before);
        await snapshot('R-direct-edit-denied');
        await go('m=joborders&a=show&jobOrderID=970002');
        await snapshot('R-handover-job-readable');
        await page.getByRole('link',{name:'Sam',exact:true}).first().click();
        await snapshot('R-handover-candidate-readable');
    }
    if (phase === 'evidence') {
        exercised = true;
        for (const [width,height] of [[1920,1080],[1440,900],[1366,768]]) {
            await page.setViewportSize({width,height});
            await go('m=home&a=priorities&filter=tasks');
            await snapshot(`final-native-tasks-${width}-${height}`);
        }
        await page.setViewportSize({width:1920,height:1080});
        await go('m=joborders&a=show&jobOrderID=970002');
        await page.locator('#ajaxPipelineTable').getByRole('link',{name:'Sam',exact:true}).waitFor();
        await snapshot('F-industrial-job-final-top');
        await page.screenshot({path:resolve(output,'F-industrial-job-final-full-page.png'),fullPage:true});
        await go('m=tasks&a=show&taskID=970001');
        assert.ok((await page.locator('body').innerText()).includes('Task history'));
        await snapshot('A-completed-task-native-history');
        await go('m=contacts&a=show&contactID=970001');
        await snapshot('B-contact-final');
        await page.screenshot({path:resolve(output,'B-contact-final-full-page.png'),fullPage:true});
    }
    if (phase === 'evidence-dialogs') {
        exercised = true;
        await go('m=home&a=priorities&filter=tasks');
        const before=db('SELECT status FROM task WHERE task_id=970003');
        await page.locator('#priority-task-970003 [data-home-task]').click();
        await page.locator('#homeTaskComplete').waitFor({state:'visible'});
        await snapshot('A-native-complete-confirmation');
        await page.locator('#homeTaskComplete').getByRole('button',{name:'Cancel',exact:true}).click();
        await page.locator('#homeTaskComplete').waitFor({state:'hidden'});
        assert.equal(db('SELECT status FROM task WHERE task_id=970003'),before);
        await go('m=home&a=priorities&filter=warnings');
        await page.locator('#priority-warning-W07-970002 [data-home-context-toggle]').click();
        await page.waitForFunction(()=>!document.querySelector('.collapsing'));
        await page.locator('#context-priority-warning-W07-970002 [data-review-action="close"][data-review-label$="no next action"]').click();
        await page.locator('#warningReviewCloseReason').waitFor({state:'visible'});
        assert.equal(await page.locator('#warningReviewCloseReason').evaluate(el=>el.checkValidity()),false);
        await snapshot('A-native-W07-reason-required-dialog');
        await page.locator('#warningReviewModal').getByRole('button',{name:'Cancel',exact:true}).click();
    }
    if (phase === 'timezone-probe') {
        exercised = true;
        await go('m=contacts&a=show&contactID=970001');
        await page.locator('#addActivityLink').click();
        const frame=page.frameLocator('#popupFrameIFrame');
        await frame.locator('#activityHour').waitFor({state:'visible'});
        writeFileSync(resolve(output,'B-timezone-probe.json'),JSON.stringify({
            browser:await page.evaluate(()=>({timezone:Intl.DateTimeFormat().resolvedOptions().timeZone,localTime:new Date().toString()})),
            activityHour:await frame.locator('#activityHour').inputValue(),
            database:db("SELECT NOW() AS db_clock, date_occurred FROM activity WHERE notes LIKE 'Helen confirmed%'")
        },null,2));
        await snapshot('B-timezone-dialog-probe-no-save');
    }
    assert.ok(exercised,'Unknown or unimplemented assessment phase');
    await context.storageState({path:state});
    writeFileSync(resolve(output,`${phase}-${role}-browser-errors.json`),JSON.stringify(errors,null,2));
    console.log(`Completed ${phase} as ${role}; browser errors: ${errors.length}`);
} catch(error) {
    await snapshot(`${phase}-failure`);
    console.error(error);
    process.exitCode=1;
} finally {await browser.close();}
