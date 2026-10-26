/* Focused native Calendar round trips using the isolated assessment fixture.
 * Setup and fixture IDs: docs/crm/recruiter-workflow-usability-assessment.md,
 * Calendar correction/retest appendix. Never targets the primary application.
 */
import {writeFileSync} from 'node:fs';
import {execFileSync} from 'node:child_process';
import assert from 'node:assert/strict';
const {chromium}=await import(process.env.PLAYWRIGHT_MODULE);
const phase=process.argv[2],format=process.env.CALENDAR_FORMAT||'DMY';
assert.ok(['baseline','fixed','remaining'].includes(phase));
assert.ok(['DMY','MDY','YMD'].includes(format));
assert.equal(process.env.OPENCATS_WORKFLOW_DISPOSABLE,'1');
assert.equal(execFileSync('docker',['network','inspect','calendar-association-net','--format','{{.Internal}}'],{encoding:'utf8'}).trim(),'true');
const ip=execFileSync('docker',['inspect','calendar-association-php','--format','{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}'],{encoding:'utf8'}).trim();
const base=`http://${ip}:8080`,out=process.env.WORKFLOW_EVIDENCE;
const db=sql=>execFileSync('docker',['exec','calendar-association-db','mysql','-udev','-pdev','cats_test','--batch','--raw','-e',sql],{encoding:'utf8'});
const rows=()=>db('SELECT calendar_event_id,date,title,data_item_type,data_item_id,joborder_id FROM calendar_event ORDER BY calendar_event_id');
const browser=await chromium.launch({headless:true,executablePath:process.env.CHROMIUM_PATH});
const context=await browser.newContext({timezoneId:'UTC',viewport:{width:1440,height:1000}});
await context.route('**/*',r=>new URL(r.request().url()).origin===base?r.continue():r.abort());
const page=await context.newPage();page.setDefaultTimeout(10000);page.on('dialog',d=>d.dismiss());
const go=day=>page.goto(`${base}/index.php?m=calendar&view=DAYVIEW&year=2026&month=10&day=${day}`);
const login=async role=>{await page.goto(`${base}/index.php?m=login`);await page.locator('#username').fill(role);await page.locator('#password').fill('opencats-assessment-only');await page.locator('#loginForm').getByRole('button',{name:'Login',exact:true}).click();await page.waitForURL(u=>u.searchParams.get('m')==='home');};
const open=async title=>{await page.locator('.calendarEntry').filter({hasText:title}).first().click();await page.getByRole('button',{name:'Edit Event',exact:true}).click();};
const save=async()=>{await Promise.all([page.waitForNavigation(),page.locator('#editEventForm').getByRole('button',{name:'Save',exact:true}).click()]);};
const ids=async()=>page.locator('#editEventForm').evaluate(f=>Object.fromEntries(['eventID','dataItemID','dataItemType','jobOrderID'].map(k=>[k,f.elements[k].value])));
try{
 await login('testerEdit');
 if(phase==='baseline'){
  writeFileSync(`${out}/before-failing-edit.txt`,rows());await go(20);await open('Alex Morgan - CNC technical interview');
  const fields=await ids();assert.equal(fields.jobOrderID,'');assert.equal(fields.dataItemID,'970001');
  await page.locator('#dateEdit_Day_ID').selectOption({label:'21'});await page.locator('#hourEdit').selectOption('11');
  await page.screenshot({path:`${out}/before-failing-edit.png`});await save();await go(21);await page.reload();
  assert.match(rows(),/970001\tNULL/);writeFileSync(`${out}/after-failing-edit.txt`,rows());
 }else{
  if(phase!=='remaining'){
  await go(20);await open('Alex Morgan - CNC technical interview');
  assert.deepEqual(await ids(),{eventID:'1',dataItemID:'970001',dataItemType:'100',jobOrderID:'970001'});
  if(format==='YMD')assert.equal(await page.locator('#dateEdit_Year_ID').inputValue(),'2026');
  await page.locator('#dateEdit_Day_ID').selectOption({label:'23'});await page.locator('#hourEdit').selectOption('11');
  await page.screenshot({path:`${out}/${format}-edit.png`});await save();await go(23);await page.reload();
  assert.match(rows(),/1\t2026-10-23 11:00:00\tAlex Morgan - CNC technical interview\t100\t970001\t970001/);
  await page.screenshot({path:`${out}/${format}-reload.png`});await open('Alex Morgan - CNC technical interview');
  await page.locator('#descriptionEdit').fill('Ordinary edit retains Candidate and Job.');await save();
  assert.match(db('SELECT description,data_item_type,data_item_id,joborder_id FROM calendar_event WHERE calendar_event_id=1'),/Ordinary edit retains Candidate and Job\.\t100\t970001\t970001/);
  }
  await go(20);
  for(const [title,job,event,record,type] of [['Second job interview','970002','2','970003','100'],['Unassociated event','','3','',''],['Second job interview','970002','2','970003','100']]){
   await open(title);assert.deepEqual(await ids(),{eventID:event,dataItemID:record,dataItemType:type,jobOrderID:job});
  }
  await page.locator('#descriptionEdit').fill('Second job unchanged');await save();
  await go(20);await open('Unassociated event');await page.locator('#descriptionEdit').fill('Still unassociated');await save();
  assert.match(db('SELECT data_item_id,data_item_type,joborder_id FROM calendar_event WHERE calendar_event_id=3'),/0\t0\tNULL/);
  assert.match(db('SELECT joborder_id FROM calendar_event WHERE calendar_event_id=2'),/\n970002\n/);
  writeFileSync(`${out}/${format}-after.txt`,rows());
  const unchanged=rows();await context.clearCookies();await login('testerRead');await go(23);
  const csrfToken=await page.evaluate(()=>CATSCsrfToken);
  const response=await page.request.post(`${base}/index.php?m=calendar&a=editEvent`,{form:{csrfToken,eventID:'1',type:'400',title:'Forbidden edit',dateEdit:format==='YMD'?'2026-10-24':format==='MDY'?'10-24-26':'24-10-26',allDay:'0',dataItemID:'970001',dataItemType:'100',jobOrderID:'970002',postback:'postback'}});
  assert.match(await response.text(),/Invalid user level|permission|not authorized/i);assert.equal(rows(),unchanged);
  console.log(`${format}: reschedule, ordinary edit, unassociated, consecutive events, read-only POST rejection passed`);
 }
}finally{await browser.close();}
