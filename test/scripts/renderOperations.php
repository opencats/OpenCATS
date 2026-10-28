<?php
/** CLI-only synthetic visual fixture. Real Reports templates; no database or session writes. */
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
chdir(dirname(__DIR__,2));
define('LEGACY_ROOT','.');
define('HTML_ENCODING','UTF-8');
require 'constants.php';
require 'lib/Template.php';
require 'lib/Charts.php';
require 'lib/Pager.php';
require 'lib/DateUtility.php';
set_error_handler(function ($severity,$message,$file,$line) { throw new ErrorException($message,0,$severity,$file,$line); });
class CATSUtility { public static function getIndexName() { return 'index.php'; } }
class TemplateUtility
{
    public static function printHeader($title,$includes=array())
    {
        echo '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Stage 7a synthetic review</title><link rel="stylesheet" href="vendor/twbs/bootstrap/dist/css/bootstrap.min.css"><link rel="stylesheet" href="main.css">';
        foreach ($includes as $file) echo str_ends_with($file,'.css') ? '<link rel="stylesheet" href="'.Template::escapeAttr($file).'">' : '<script defer src="'.Template::escapeAttr($file).'"></script>';
        echo '</head><body>';
    }
    public static function printHeaderBlock() { echo '<div class="alert alert-warning rounded-0 py-1 mb-0">Fixture-backed visual review — synthetic records; application authentication and live totals are not represented.</div>'; }
    public static function printTabs($active) { echo '<nav class="border-bottom p-2"><strong>OpenCATS</strong> · Home · Activity · Tasks · Job Orders · Candidates · Companies · Contacts · Lists · Calendar · <strong>Reports</strong></nav>'; }
    public static function printQuickSearch() {}
    public static function printFooter() { echo '</body></html>'; }
}
$_SESSION['CATS'] = new class {
    public function getAccessLevel($action) { return ACCESS_LEVEL_SA; }
    public function getTimeZoneOffset() { return 0; }
};
$kind=$argv[1] ?? 'ordinary';
$view=$argv[2] ?? 'overview';
if (!in_array($kind,array('empty','ordinary','busy','unavailable','restricted','custom','recruiter','desk','unassigned','deniedTasks'),true) || !in_array($view,array('overview','activity','tasks','jobs','warnings','outcomes'),true)) throw new InvalidArgumentException('Invalid fixture.');
$empty=array('activity'=>0,'calls'=>0,'conversations'=>0,'emails'=>0,'meetings'=>0,'statusChanges'=>0,'other'=>0,'jobs'=>0,'tasks'=>0,'overdue'=>0,'warnings'=>0);
$groups=array('recruiter'=>array(),'desk'=>array());
$names=array(1=>'Alex Morgan',2=>'Priya Shah',3=>'Helen Ward',4=>'James Carter',0=>'Unassigned');
$desks=array(1=>'Commercial',2=>'Industrial',3=>'Engineering',0=>'Unassigned / no Job Desk');
foreach ($names as $id=>$name) $groups['recruiter'][$id]=$empty+array('name'=>$name);
if ($kind!=='empty')
{
    foreach ($groups['recruiter'] as $id=>&$row)
        $row=array('activity'=>$id*9,'calls'=>$id*4,'conversations'=>$id*2,'emails'=>$id*2,'meetings'=>$id,'statusChanges'=>$id,'other'=>$id,
            'jobs'=>$id+2,'tasks'=>$id+3,'overdue'=>$id%3,'warnings'=>$id%3,'name'=>$row['name']);
    unset($row);
    // Desk totals partition the same synthetic population; no invented user membership.
    $groups['desk'][1]=$groups['recruiter'][1];
    $groups['desk'][2]=$groups['recruiter'][2];
    $groups['desk'][3]=$groups['recruiter'][3];
    $groups['desk'][0]=$groups['recruiter'][4];
    foreach ($empty as $key=>$value) $groups['desk'][0][$key]+=$groups['recruiter'][0][$key];
    foreach ($groups['desk'] as $id=>&$row) $row['name']=$desks[$id];
    unset($row);
}
$recruiter='all'; $desk='all';
if (in_array($kind,array('recruiter','desk','unassigned'),true))
{
    $id=$kind==='recruiter'?2:($kind==='desk'?3:0);
    $groups['recruiter']=array($id=>$groups['recruiter'][$id]);
    $groups['desk']=array($id=>array_replace($groups['recruiter'][$id],array('name'=>$desks[$id])));
    if ($kind==='desk') $desk=(string) $id; else $recruiter=(string) $id;
}
$totals=array('activity'=>0,'jobs'=>0,'tasks'=>0,'overdue'=>0,'warnings'=>0);
foreach ($groups['recruiter'] as $row) foreach ($totals as $key=>$unused) $totals[$key]+=$row[$key];
$details=array();
if ($kind!=='empty') for ($i=1;$i<=($kind==='busy'?25:4);++$i) $details[]=array('id'=>$i,'title'=>array('activity'=>'Recorded call — Northbridge Engineering','tasks'=>'Candidate interview follow-up','jobs'=>'CNC Maintenance Engineer','warnings'=>'Northbridge Engineering','outcomes'=>'Recorded outcome pair')[$view==='overview'?'jobs':$view].' '.$i,'actorName'=>'Priya Shah','deskName'=>'Engineering','sourceDate'=>date('Y-m-d'),'status'=>'Open','url'=>'index.php?m=tasks&a=show&taskID='.$i);
if ($view==='warnings')
    foreach ($details as $i=>&$row) $row['status']=array('W02: no recorded submission; W08: invalid recruiter','W01: overdue commitment','W07: upcoming','W07: due today')[$i%4];
unset($row);
if ($view==='activity')
    foreach ($details as $i=>&$row) { unset($row['status']); $row['type']=array(100,200,500,800)[$i%4]; }
unset($row);
if ($view==='tasks')
    foreach ($details as $i=>&$row)
    {
        $row['overdue']=$i<min(4,$totals['overdue']);
        $row['sourceDate']=$row['overdue']?date('Y-m-d',strtotime('-1 day')):($i===4?date('Y-m-d'):($i%2?null:date('Y-m-d',strtotime('+2 days'))));
        $row['status']='in_progress';
    }
unset($row);
$days=(int) date('j');
$comparisonDays=min($days,(int) date('t',strtotime('first day of last month')));
$comparison=$kind==='unavailable'?null:array('currentStart'=>date('Y-m-01'),'currentEnd'=>date('Y-m-').sprintf('%02d',$comparisonDays),
    'previousStart'=>date('Y-m-01',strtotime('first day of last month')),'previousEnd'=>date('Y-m-',strtotime('first day of last month')).sprintf('%02d',$comparisonDays),
    'days'=>$comparisonDays,'current'=>$totals['activity'],'previous'=>$kind==='empty'?0:(int) ($totals['activity']*2/3));
$trend=array();
foreach (array(1=>(int) ($totals['activity']*0.2),10=>(int) ($totals['activity']*0.3),17=>$totals['activity']-(int) ($totals['activity']*0.2)-(int) ($totals['activity']*0.3)) as $day=>$count)
{
    $date=date('Y-m-').sprintf('%02d',min($day,$days));
    $trend[$date]=($trend[$date] ?? 0)+$count;
}
$op=array('filters'=>array('range'=>$kind==='custom'?'custom':'month','start'=>date('Y-m-01'),'end'=>date('Y-m-d'),'recruiter'=>$recruiter,'desk'=>$desk,'view'=>$view,'page'=>'1','comparisonPage'=>'1'),
    'available'=>array_fill_keys(array('activity','tasks','jobs','warnings'),$kind!=='unavailable'), 'totals'=>$totals,'groups'=>$groups,
    'choices'=>array('recruiter'=>$names,'desk'=>$desks),'details'=>$details,'pager'=>new Pager(count($details),25,1),
    'trend'=>$kind==='empty'?array():$trend, 'comparison'=>$comparison,
    'oversight'=>$kind!=='restricted','dispositions'=>array('remind_later'=>$kind==='empty'?0:2,'close'=>$kind==='empty'?0:1,'reappeared'=>$kind==='empty'?0:1),
    'reviewActions'=>$kind==='empty'?0:12,'repeatedReviews'=>$kind==='empty'?0:2,'jobsAtRisk'=>$kind==='empty'?0:3);
if ($kind==='deniedTasks') { $op['available']['tasks']=false; $op['available']['warnings']=false; }
// Synthetic R02 values use the same projection shape; persistence is covered by OperationsReportTest.
$op['filters'] += array('milestone'=>'submissions','outcomeSort'=>'date','outcomeDirection'=>'desc');
$outDefinitions=array('submissions'=>array('id'=>400,'label'=>'Submissions recorded','configuredLabel'=>'Submitted'),
    'interviews'=>array('id'=>500,'label'=>'Interview stage recorded','configuredLabel'=>'Interviewing'),
    'offers'=>array('id'=>600,'label'=>'Offers recorded','configuredLabel'=>'Offered'),
    'placements'=>array('id'=>800,'label'=>'Placements recorded','configuredLabel'=>'Placed'));
$outTotals=$kind==='empty'?array('submissions'=>0,'interviews'=>0,'offers'=>0,'placements'=>0):array('submissions'=>48,'interviews'=>18,'offers'=>6,'placements'=>4);
$outChanges=array();
foreach ($outTotals as $key=>$count)
{
    $prior=(int) floor($count/2);
    $outChanges[$key]=array('current'=>$count,'previous'=>$prior,'absolute'=>$count-$prior,'percent'=>$prior?100*($count-$prior)/$prior:null);
}
$outDesks=array(); $outWeeks=array();
foreach (array('Commercial','Engineering','Unassigned / no Job Desk') as $i=>$name)
{
    $row=array('name'=>$name);
    foreach ($outTotals as $key=>$count) $row[$key]=$i===2?$count-2*(int) floor($count/3):(int) floor($count/3);
    $outDesks[$i]=$row;
    $outWeeks[]=array_replace($row,array('name'=>date('Y-m-').sprintf('%02d',min(1+$i*7,(int) date('j'))).' (partial)'));
}
$outDetails=array();
if ($kind!=='empty') for ($i=1;$i<=($kind==='busy'?25:4);++$i)
    $outDetails[]=array('candidateID'=>$i,'jobID'=>$i+10,'title'=>$i===1?'Candidate <img src=x onerror=alert(1)>':'Candidate '.$i,
        'jobTitle'=>'Recorded vacancy '.$i,'sourceDate'=>date('Y-m-d'),'repeats'=>$i===1?3:1,
        'deskName'=>'Engineering','isSpeculative'=>$i%2);
$op['outcomes']=array('definitions'=>$outDefinitions,'available'=>array_fill_keys(array_keys($outDefinitions),$kind!=='unavailable'),
    'totals'=>$outTotals,'changes'=>$outChanges,'comparison'=>$comparison ?? array('days'=>1,'currentStart'=>date('Y-m-d'),'currentEnd'=>date('Y-m-d'),'previousStart'=>date('Y-m-d',strtotime('-1 day')),'previousEnd'=>date('Y-m-d',strtotime('-1 day'))),
    'weeks'=>$kind==='empty'?array():$outWeeks,'desks'=>$kind==='empty'?array():$outDesks,
    'details'=>$kind==='unavailable'?array():$outDetails,'pager'=>new Pager($outTotals['submissions'],25,1),
    'actorUnavailable'=>false,'timeZoneOffset'=>0);
if (!in_array($recruiter,array('all','0'),true)) { $op['outcomes']['actorUnavailable']=true; $op['outcomes']['available']=array_fill_keys(array_keys($outDefinitions),false); }
if ($view==='outcomes') $op['choices']['recruiter']=array(0=>'Unknown event actor');

$statistics=array();
preg_match_all('/statisticsData\[\'([^\']+)\'\]/',file_get_contents('modules/reports/Reports.tpl'),$matches);
foreach ($matches[1] as $key) $statistics[$key]=0;
$t=new Template();
$t->assign('active',null);
$t->assign('operations',$op);
$t->assign('statisticsData',$statistics);
$t->display('modules/reports/Reports.tpl');
