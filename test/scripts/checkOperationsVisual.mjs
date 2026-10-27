/* Focused synthetic Reports template check against the existing Selenium service. */
import assert from 'node:assert/strict';
import {mkdirSync,writeFileSync} from 'node:fs';
import {resolve} from 'node:path';
const endpoint=process.env.SELENIUM_URL || 'http://localhost:4444';
const base=process.env.OPERATIONS_FIXTURE_URL;
const output=process.env.OPERATIONS_EVIDENCE;
assert.ok(base && output,'Supply synthetic fixture URL and evidence directory');
mkdirSync(output,{recursive:true});
let session;
async function command(method,path,body) {
    const r=await fetch(endpoint+path,{method,headers:{'content-type':'application/json'},...(body?{body:JSON.stringify(body)}:{})});
    const result=await r.json();
    if (!r.ok || result.value?.error) throw new Error(JSON.stringify(result.value));
    return result.value;
}
const execute=script=>command('POST',`/session/${session}/execute/sync`,{script,args:[]});
async function setViewport(width,height) {
    await command('POST',`/session/${session}/window/rect`,{width,height});
    const inner=await execute('return {width:innerWidth,height:innerHeight}');
    await command('POST',`/session/${session}/window/rect`,{width:width+(width-inner.width),height:height+(height-inner.height)});
    assert.deepEqual(await execute('return {width:innerWidth,height:innerHeight}'),{width,height});
}
const technical=/\b(actor|warning subjects?|personal suppression|effective disposition|native source responsibility|source scope|W0[12378]|R0[1468])\b|Stage 7|evidence hash/i;
async function verifyPresentation() {
    const state=await execute(`const root=document.querySelector('.reports-operations');
        return {text:root.innerText, summary: Array.from(root.querySelectorAll(':scope > .row')[0].querySelectorAll('.card')).map(card=>card.innerText),
            dates:Array.from(root.querySelectorAll('[data-operations-custom-date]')).map(el=>getComputedStyle(el).display!=='none'),
            counts:Array.from(root.querySelectorAll(':scope > .row')[0].querySelectorAll('.h4')).map(el=>el.textContent.trim()), path:location.pathname,
            range:document.querySelector('#ops-range').value, about:document.querySelector('#operations-about').open,
            tabs:Array.from(root.querySelector('[aria-label="Operational reports"]').querySelectorAll('a')).map(a=>a.textContent.trim())};`);
    assert.doesNotMatch(state.text,technical,'Internal terminology must not appear in normal visible Reports content');
    assert.equal(state.summary.length,4);
    const expected=state.path.includes('empty')?['0','0','0','0']:state.path.includes('unavailable')?['N/A','N/A','N/A','N/A']:
        state.path.includes('deniedTasks')?['90','N/A','20','N/A']:state.path.includes('recruiter')?['18','5','4','2']:
        state.path.includes('desk-scope')?['27','6','5','0']:state.path.includes('unassigned')?['0','3','2','0']:['90','25','20','4'];
    assert.deepEqual(state.counts,expected);
    for (const label of ['Recorded activity','Outstanding tasks','Current job orders','Records needing attention']) assert.ok(state.summary.some(text=>text.includes(label)));
    assert.deepEqual(state.dates,[state.range==='custom',state.range==='custom']);
    assert.equal(state.about,false);
    assert.deepEqual(state.tabs,['Overview','Activity & Engagement','Desk Workload','Follow-up & Candidate Care','Warnings & Follow-up']);
    assert.ok(state.text.includes('your eligible accounts only, not all company accounts'));
    assert.ok(state.text.includes('must not be added together'));
}
async function verifyCharts() {
    const charts=await execute(`return Array.from(document.querySelectorAll('.reports-operations .oc-chart')).map(el=>({
        id:el.id,state:el.dataset.chartState,data:JSON.parse(el.querySelector('.oc-chart-data').textContent),
        values:Array.from(el.querySelectorAll('tbody tr')).map(tr=>Array.from(tr.children).map(td=>td.textContent.trim()))}));`);
    for (const chart of charts) {
        assert.ok(['ready','empty'].includes(chart.state),chart.id+' must render or honestly report no data');
        assert.deepEqual(chart.values,chart.data.labels.map((label,i)=>[label,...chart.data.datasets.map(ds=>String(ds.data[i]))]),chart.id+' exact values');
        if (chart.id==='operations-task-workload') {
            assert.deepEqual(chart.data.datasets.map(d=>d.label),['Overdue','Other outstanding']);
            assert.ok(chart.data.labels.includes('Unassigned') || chart.data.labels.length<=1);
            const expected=await execute(`return Array.from(document.querySelectorAll('#operations-comparisons table')[0].tBodies[0].rows).filter(tr=>tr.children.length===6).map(tr=>({name:tr.children[0].textContent.trim(),tasks:Number(tr.children[3].textContent),overdue:Number(tr.children[4].textContent)}));`);
            chart.data.labels.forEach((label,i)=>{
                const row=expected.find(r=>r.name===label); if (!row) return;
                assert.equal(chart.data.datasets[0].data[i],row.overdue);
                assert.equal(chart.data.datasets[0].data[i]+chart.data.datasets[1].data[i],row.tasks);
            });
        }
        if (chart.id==='operations-activity-recruiters') {
            const rows=await execute(`return Array.from(document.querySelectorAll('[aria-label="Activity by recorded person"] tbody tr')).map(tr=>({name:tr.children[0].textContent.trim(),total:Number(tr.children[1].textContent)}));`);
            assert.deepEqual(chart.data.labels,rows.map(r=>r.name));
            chart.data.labels.forEach((label,i)=>assert.equal(chart.data.datasets.reduce((total,ds)=>total+ds.data[i],0),rows[i].total));
            assert.deepEqual(chart.data.datasets.map(ds=>ds.label),['Calls','Emails','Meetings','Status changes','Other / unclassified']);
        }
        if (chart.id==='operations-desk-workload') {
            const rows=await execute(`return Array.from(document.querySelectorAll('#operations-comparisons table')[1].tBodies[0].rows).filter(tr=>tr.children.length===6).map(tr=>({name:tr.children[0].textContent.trim(),total:Number(tr.children[2].textContent)}));`);
            chart.data.labels.forEach((label,i)=>assert.equal(chart.data.datasets[0].data[i],rows.find(r=>r.name===label).total));
        }
    }
}
const measurements=[];
try {
    const created=await command('POST','/session',{capabilities:{alwaysMatch:{browserName:'chrome','goog:chromeOptions':{args:['--headless=new','--no-sandbox','--disable-dev-shm-usage']}}}});
    session=created.sessionId;
    for (const [width,height] of [[1440,900],[1920,1080],[1280,900],[390,844]]) {
        await setViewport(width,height);
        let before;
        if (process.env.OPERATIONS_BEFORE_FIXTURE) {
            await command('POST',`/session/${session}/url`,{url:process.env.OPERATIONS_BEFORE_FIXTURE});
            before=await execute(`return {lastComparison:Array.from(document.querySelector('#ops-workload').parentElement.querySelectorAll('tbody tr')).at(-1)?.getBoundingClientRect().bottom,legacy:document.querySelector('#contents > header').getBoundingClientRect().top};`);
            if (width===1440 || width===1920) writeFileSync(resolve(output,`before-${width}.png`),Buffer.from(await command('GET',`/session/${session}/screenshot`),'base64'));
        }
        await command('POST',`/session/${session}/url`,{url:base+'/ordinary.html'});
        const state=await execute(`return {title:document.querySelector('h1').textContent, width:innerWidth, scroll:document.documentElement.scrollWidth,
            legacy:document.querySelector('#contents > header').getBoundingClientRect().top,
            summary:document.querySelector('[aria-label="Operational reports"]').getBoundingClientRect().bottom,
            links:Array.from(document.querySelectorAll('a')).map(a=>a.getAttribute('href'))};`);
        assert.equal(state.title,'Recruitment Operations');
        assert.ok(state.scroll<=state.width+1,`Horizontal overflow at ${width}`);
        assert.ok(state.links.some(url=>url.includes('a=showSubmissionReport')));
        assert.ok(state.links.some(url=>url.includes('a=showPlacementReport')));
        if (width>=1280) assert.ok(state.summary<height,'Operational navigation must be above the fold');
        await verifyPresentation();
        await verifyCharts();
        const density=await execute(`return {lastComparison:Array.from(document.querySelector('#ops-workload').parentElement.querySelectorAll('tbody tr')).at(-1)?.getBoundingClientRect().bottom, about:document.querySelector('#operations-about').getBoundingClientRect().top};`);
        if (width>=1280) assert.ok(await execute("return document.querySelector('#operations-task-workload canvas').getBoundingClientRect().bottom<innerHeight"),'Key workload chart must fit in the desktop viewport');
        measurements.push({width,height,...state,...density,before});
        writeFileSync(resolve(output,`operations-${width}.png`),Buffer.from(await command('GET',`/session/${session}/screenshot`),'base64'));
        if (width===1440) {
            await execute(`document.querySelector('#contents > header').scrollIntoView({behavior:'instant',block:'start'});`);
            writeFileSync(resolve(output,'legacy-1440.png'),Buffer.from(await command('GET',`/session/${session}/screenshot`),'base64'));
        }
    }
    for (const fixture of ['empty','busy','unavailable','activity','jobs','warnings','restricted','custom','recruiter','desk-scope','unassigned','deniedTasks']) {
        await setViewport(1440,900);
        await command('POST',`/session/${session}/url`,{url:base+'/'+fixture+'.html'});
        await verifyPresentation();
        await verifyCharts();
        const text=await execute('return document.body.innerText');
        assert.ok(text.includes('Existing & Specialist Reports'));
        if (fixture==='unavailable') assert.ok(text.includes('N/A'));
        if (fixture==='empty') assert.ok(text.includes('No data to chart for this selection.'));
        if (fixture==='busy') { assert.ok(text.includes('Candidate interview follow-up')); assert.ok(text.includes('Overdue')); assert.ok(text.includes('Due today')); }
        if (fixture==='restricted') {
            assert.ok(text.includes('Review details are not available with your access.'));
            assert.equal(await execute("return document.querySelector('.reports-operations a[href*=warningOversight]')===null"),true);
        }
        if (fixture==='warnings') {
            assert.ok(text.includes('No submission recorded; Job recruiter needs attention'));
            assert.ok(text.includes('Warnings & Follow-up — Records Needing Attention'));
        }
        if (fixture==='jobs') assert.ok(text.includes('Desk Workload — Current Jobs'));
        if (fixture==='busy') assert.ok(text.includes('Follow-up & Candidate Care — Outstanding Tasks'));
        if (fixture==='activity') {
            assert.ok(text.includes('Call: not reached')); assert.ok(text.includes('Status change'));
            assert.ok(text.includes('Equivalent elapsed periods')); assert.ok(text.includes('30 more recorded events'));
            assert.equal(await execute("return Chart.getChart(document.querySelector('#operations-activity-trend canvas')).config.type"),'line');
            assert.equal(await execute("return document.querySelector('#operations-activity-recruiters').dataset.chartState"),'ready');
        }
        if (fixture==='jobs') {
            assert.equal(await execute("return document.querySelector('#operations-desk-workload').dataset.chartState"),'ready');
            assert.equal(await execute("return Chart.getChart(document.querySelector('#operations-desk-workload canvas')).config.type"),'bar');
            assert.equal(await execute("return Chart.getChart(document.querySelector('#operations-desk-workload canvas')).options.scales.x.stacked"),false);
        }
        if (fixture==='unavailable' || fixture==='deniedTasks') assert.equal(await execute("return document.querySelector('#operations-task-workload')===null"),true);
        if (fixture==='warnings') {
            assert.ok(text.includes('Upcoming')); assert.ok(text.includes('Overdue'));
            assert.equal(await execute("return document.querySelectorAll('.reports-cue-overdue').length>0 && document.querySelectorAll('.reports-cue-attention').length>0"),true);
        }
        assert.equal(await execute("return document.querySelectorAll('.reports-operations .bg-success,.reports-cue-success').length"),0);
        if (fixture==='activity') assert.equal(await execute("return document.querySelector('#operations-activity-trend').dataset.chartState"),'ready');
        writeFileSync(resolve(output,fixture+'-1440.png'),Buffer.from(await command('GET',`/session/${session}/screenshot`),'base64'));
        if (fixture==='activity') {
            await execute("document.querySelector('#operations-activity-recruiters').closest('section').scrollIntoView({behavior:'instant',block:'start'});");
            writeFileSync(resolve(output,'activity-recruiters-1440.png'),Buffer.from(await command('GET',`/session/${session}/screenshot`),'base64'));
        }
    }
    for (const fixture of ['activity','jobs','busy','warnings']) {
        await setViewport(390,844);
        await command('POST',`/session/${session}/url`,{url:base+'/'+fixture+'.html'});
        assert.equal(await execute('return document.documentElement.scrollWidth<=innerWidth'),true,fixture+' narrow layout');
        await verifyCharts();
    }
    // The same date inputs become available immediately for custom ranges, without changing form values.
    await command('POST',`/session/${session}/url`,{url:base+'/ordinary.html'});
    for (const range of ['custom','week','quarter','month']) {
        const dates=await execute(`document.querySelector('#ops-range').value='${range}'; document.querySelector('#ops-range').dispatchEvent(new Event('change',{bubbles:true})); return Array.from(document.querySelectorAll('[data-operations-custom-date]')).map(el=>getComputedStyle(el).display!=='none');`);
        assert.deepEqual(dates,[range==='custom',range==='custom']);
    }
    assert.equal(await execute("document.querySelector('#operations-about').open=true; return document.querySelector('#operations-about').innerText.includes('including resets')"),true);
    writeFileSync(resolve(output,'measurements.json'),JSON.stringify(measurements,null,2));
    console.log('PASS: 13 fixtures; stacked workload/category charts, line trend, neutral Desk comparison, exact table reconciliation, filter/Unassigned/denied states and labelled urgency; 1440/1920/1280/390 viewports; legacy links retained.');
} finally { if (session) await command('DELETE',`/session/${session}`); }
