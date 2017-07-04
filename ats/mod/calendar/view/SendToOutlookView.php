<?php 
//<a href="ical.php?date=20120302&amp;startTime=1300&amp;endTime=1400&amp;subject=Meeting&amp;desc=Meeting to discuss processes.">Add Appointment to your Outlook Calendar</a>


$date      = $_GET['date'];
$startTime = $_GET['startTime'];
$l   = $_GET['l'];
$subject   = $_GET['subject'];
$desc      = $_GET['desc'];
$t = $_GET['t'];
$eId = $_GET['i'];

$te = E::enum('eventType')->byAttr('dbValue',$t);
/*vd(array(
	'$te'=>$te,	
		
));*/
//ie();
$subject = '['.$te->desc.'] '.$subject;

//vd(array('$l'=>$l));
//cho $l;
$startDate = $date;
if ($l=='d'){
	$endTime = '000000';
	//$endDateD = date('Ymd',$date);
	$stop_date =  DateTime::createFromFormat('Ymd',$date);//  new DateTime($endDateD);
	//cho 'date before day adding: ' . $stop_date->format('Y-m-d H:i:s');
	$stop_date->modify('+1 day');
	//echo 'date after adding 1 day: ' . $stop_date->format('Y-m-d H:i:s');	
	$endDate = $stop_date->format('Ymd');
} else {// minuty
	
	$endDate = $date;
	$minutes = intval($l);
	$hours = floor($minutes/60);
	$minutes = $minutes - ($hours*60);
	$endt = intval($startTime)+($hours*10000)+($minutes*100);
	if ($endt>235000)  $endt = 235000;
	$endTime = ""+$endt;
	 while (strlen($endTime)<6){
	 	$endTime = '0'.$endTime;
	 }
	/*echo 'hours:'.$hours;
	echo 'min:'.$minutes;
	echo 'endTime:'.$endTime;
	echo 'end1:'.(intval($startTime)+($hours*10000));
	die();*/
}
//ie();

$ical = "BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//hacksw/handcal//NONSGML v1.0//EN
BEGIN:VEVENT
UID:" .$eId. ".atsEvents.".SITE_DOMAIN."
DTSTAMP:" . gmdate('Ymd').'T'. gmdate('His') . "Z
DTSTART:".$startDate."T".$startTime."
DTEND:".$endDate."T".$endTime."
SUMMARY:".$subject."
DESCRIPTION:".$desc."
END:VEVENT
END:VCALENDAR";

//set correct content-type-header
header('Content-type: text/calendar; charset=utf-8');
header('Content-Disposition: inline; filename=calendar.ics');
echo $ical;
exit;