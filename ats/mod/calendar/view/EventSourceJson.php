<?php
//$evdo = evEscJs(evRunOb('evd',array(
//		'args'=>$args,
//)));
//vd($args);
$req = $args['input']['request'];
$startDate=$req['start'];
$endDate = $req['end'];
$userId = \E::c('user')->getCurrentUserId();
$events = $this->model()->getEventsByStartEnd($startDate,$endDate,$userId);
//ie();
/*vd(array(
	'$events'=>$events,	
));*/
?>[
<?php foreach ($events as $k => $event) {
//$id = $event['id']
	$event['iconsHTMLSmall']='';
	$event['iconsHMTLLarge']='';
	if ($event['dataItemType'] > 0)
	{
		$event['iconsHTMLSmall'] = $this->getHTMLOfLink(
				$event['dataItemId'], $event['dataItemType'], false
				);
		$event['iconsHMTLLarge'] = $this->getHTMLOfLink(
				$event['dataItemId'], $event['dataItemType'], true
				);
	}
	
?>
{
<?php 
  $con = '';
  foreach($event as $ke => $ve){
  	if ($ke!='allDay' || $ve!='0'){
		$line = $con.'"'.$ke.'": '.evEscJs($ve);
		echo $line;
		$con = ",\n";
  	}
  }
?>  
},
<?php }//foreach $events ?>
{
	"allDay": "",
	"title": "Test event 2",
	"id": "0",
	"end": "2099-06-10 21:00:00",
	"start": "2099-06-10 16:00:00",
	"hour": "16",
	"min": "00"	
}
]


