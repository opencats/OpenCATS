<?php 
//$cc = E::controller('cats');
//$cc->incCats();
ob_start();
chdir(CATS_FE_DIR);
include_once('./index_cats.php');
$cntCats=ob_get_contents();
ob_end_clean();
TemplateUtility::printModalHeader('Wyślij SMS',array(),'Wyślij SMS');

$title = $args['route']->desc;

//include_once('pathconv.php');

ob_start();
	//echo   tplFilterOutput(array('content'=>evGetGlobal('_commonHeader')['output']));
	echo evStrReplace(evGetGlobal('_commonHeader')['output'],'<head>','<head><base href="'.SITE_PATH.'">') ;
?>
	
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:100,200,300,400,500,600,700" rel="stylesheet" type="text/css">
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet"> 
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
    <link href="<?php echo SITE_PATH;?>assets/css/tranquil-heart.css" rel="stylesheet"> 
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.0/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>	
    
</head>
<body style="background: #eee;">
<script type="text/javascript">parentSetPopTitle('<?php echo $title;?>');</script>

<?php echo evGetGlobal('_modalHeader')['output'];?>
<?php echo evGetGlobal('_quickAction')['output'];?>    
<div class="container">    
<?php 	

 echo $args['content'];
?>
</div>
<?php  
 //echo evGetGlobal('printFooter')['output'];
//cho evGetGlobal('myProfile')['output'];
 $cnt=ob_get_contents();
 ob_end_clean();
 echo $cnt;
 /* MB: niepotrzebne - base w naglowku
function tplFilterOutput($args){
 	$content = $args['content'];
 	$content = evStrReplace($content,'src="js/','src="'.SITE_PATH.'js/');
 	$content = evStrReplace($content,'src="images/','src="'.SITE_PATH.'images/');
 	$content = evStrReplace($content,'src="locale/','src="'.SITE_PATH.'locale/');
 
 	$content = evStrReplace($content,'href="not-ie.css','href="'.SITE_PATH.'not-ie.css');
 	$content = evStrReplace($content,'import "main.css','import "'.SITE_PATH.'main.css');
 	$content = evStrReplace($content,'href="ie.css','href="'.SITE_PATH.'ie.css');
 	$content = evStrReplace($content,'href="index.php','href="'.SITE_PATH.'index.php');
 	return $content;
  }
  
 echo tplFilterOutput(array(
 		'content'=>$cnt
 ));*/