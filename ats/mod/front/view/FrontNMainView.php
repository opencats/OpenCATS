<!-- FrontNMainView -->
<?php 
//$cc = E::controller('cats');
//$cc->incCats();


ob_start();
$isModal = evGetGlobal('isModal');
//vd(array('$isModal'=>$isModal));
if ($isModal){
	echo evGetGlobal('printModalHeader')['output'];
} else {
	//cho evGetGlobal('printHeader')['output'];
	//echo evGetGlobal('_commonHeader')['output'];
	?>	
<html style="font-size:18px;">
<head>	
	<base href="<?php echo SITE_PATH;?>">
    <meta charset="utf-8">   
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="description" content="System requirements, installation process and namespace auto-loading.">
    <meta name="viewport" content="width=device-width, initial-scale=2.0">

    <link href="https://fonts.googleapis.com/css?family=Open+Sans:100,200,300,400,500,600,700" rel="stylesheet" type="text/css">
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet"> 
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
    <link href="<?php echo SITE_PATH;?>assets/css/tranquil-heart.css" rel="stylesheet"> 
    <link href="<?php echo SITE_PATH;?>assets/css/style.css?v=<?php echo rand();?>" rel="stylesheet">   

    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
    <script type="text/javascript" src="https://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script> 
    <script type="text/javascript" src="<?php echo SITE_PATH;?>assets/js/prettify.js"></script>
    <script type="text/javascript" src="<?php echo SITE_PATH;?>assets/js/script.js"></script>
    <?php //E::c('angular')->htmlHeader(); ?>
    
    <?php //E::uiO(array(
    		//'name'=>'ouInit',
    		//'def'=>array(
    		//		'xmlViews'=>evGetGlobal('ouXmlViews'),
    		//		'jsonViews'=>evGetGlobal('ouJsonViews'),
    		//),
    //));?>

	   
</head>
<body style="background: #fff">
<?php 	
	//echo evGetGlobal('_quickAction')['output'];
	//echo evGetGlobal('_popupContainer')['output'];
	
	$tabInfos = evGetGlobal('printTabs')['result']['tabInfos']['tabs'];
	//echo evGetGlobal('printHeaderBlock')['output'];<br/>
	//echo "<br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/>";
	//vd(array(
	//	'$tabInfos'=>$tabInfos
	//));
?>
	<header>
        <nav class="navbar navbar-fixed-top" style="padding-top:5px;">
            <div class="navbar-header">
                <a class="navbar-brand" href="<?php echo SITE_URL;?>">
                <img src="<?php echo ATS_APPLOGO_HREF;?>" border="0" alt="<?php __('CATS Applicant Tracking System');?>" style="width:250px;border: 1px solid #ccc;padding:5px;"/></a>
            </div>
			<div id="topRightOuter" style="color:yellow;float: right; font-size: 10px; margin-right: 10px;"> 
				<?php $dta = evGetGlobal('_topRight')['result'];
				
				echo $dta['userNameSite'].$dta['adminInfo'].$dta['logoutHref'];
				?>
			</div>
<ul class="nav nav-tabs" style="float:right;clear:both;margin-right:40px;margin-top: 10px;">
 <?php foreach ($tabInfos as $moduleName => $tabInfo) { 
 if ($tabInfo['active'] && sizeof($tabInfo['subTabs'])>0) {
 	?> 
  <li class="dropdown active open">
  <a class="dropdown-toggle" data-toggle="dropdown" href="<?php echo $tabInfo['href']?>"><?php echo $tabInfo['text']?><span class="caret"></span></a>
    <ul class="dropdown-menu">
                    	<?php 
                    	foreach ($tabInfo['subTabs'] as $sti => $stInfo) {
                    	?>    
      					<li><a href="<?php echo $stInfo['href'];?>" onclick="<?php echo $stInfo['onclick'];?>"><?php echo $stInfo['text'];?></a></li>
      					<?php }//stinfo ?>
    </ul>    
  </li>
 <?php 
 } else if ($tabInfo['active']){// active - no subtabs
 ?>	
 	<li class="active"><a href="<?php echo $tabInfo['href']?>"><?php echo $tabInfo['text']?></a></li>
 <?php 	
 } else {//not active
 ?>
 	<li><a href="<?php echo $tabInfo['href']?>"><?php echo $tabInfo['text']?></a></li>
 <?php 
 }	
 
 }//foreach ($tabInfos ?>    
</ul>			
<!-- 		
            <div class="collapse navbar-collapse" style="clear:both;">
            	<ul class="nav navbar-nav navbar-right navbar-primary"> 
 <?php foreach ($tabInfos as $moduleName => $tabInfo) { 
 $style= ($tabInfo['active'])? 'color:#2e3842;background-color:#fff;':'';
 	?>           	
                    <li style="<?php echo $style;?>">
                    <a href="<?php echo $tabInfo['href']?>" style="<?php echo $style;?>"><?php echo $tabInfo['text']?></a>
                    <?php if ($tabInfo['active']) {
                    	?>
                    	<ul class="nav navbar-nav navbar-right navbar-secondary" style="clear: both;position:absolute;left:-200px;top:30px;">
                    	<?php 
                    	foreach ($tabInfo['subTabs'] as $sti => $stInfo) {
                    	?>
                    		<li><a href="<?php echo $stInfo['href'];?>"><?php echo $stInfo['text'];?></a></li>
                    	<?php }//stinfo ?>
                    	</ul>
                    <?php 
                    	}//if active ?>
                    </li> 
 <?php }//foreach ($tabInfos ?>                   
                </ul>
                        
                    </div>
 -->	                    
        </nav>
    </header>         
<div id="content" class="container container-documentation">
<div class="ngAppContainer">
	<?php //E::c('angular')->htmlApp(); ?>
</div>
<div class="alert alert-success" style="border: 0 none; border-left: 3px solid #179b90; background-color: #21b2a6;display:none"><p style=" color: #fff; line-height: 24px; text-align:center; margin-bottom: 0px;"><b style="border: 0px none; margin-top:0;">Note:</b> Hybridauth 3.0 is currently in beta stage and it might NOT be suitable for production use.<br><span style="margin-left:42px;">Hybridauth 2.9 documentation can be found at <a href="https://hybridauth.github.io/hybridauth/" target="_blank">https://hybridauth.github.io/hybridauth/</a></span></p></div>
        <div class="block row">
            <div class="col col-md-12">
                <br>
            </div>
        </div>
    </div>    
<?php 	
	
	
	//cho evGetGlobal('printTabs')['output'];
} 
 echo $args['content'];
 echo evGetGlobal('printFooter')['output'];
//cho evGetGlobal('myProfile')['output'];
 $cnt=ob_get_contents();
 ob_end_clean();
 
 echo $cnt;
/* MB: niepotrzebne - base w naglowku
 * function tplFilterOutput($args){
 	$content = $args['content'];
 	$content = evStrReplace($content,'src="js/','src="'.SITE_PATH.'js/');
 	$content = evStrReplace($content,'src="images/','src="'.SITE_PATH.'images/');
 	$content = evStrReplace($content,'src="locale/','src="'.SITE_PATH.'locale/');
 	$content = evStrReplace($content,'src="modules/','src="'.SITE_PATH.'modules/');
 	
 
 	$content = evStrReplace($content,'href="not-ie.css','href="'.SITE_PATH.'not-ie.css');
 	$content = evStrReplace($content,'import "main.css','import "'.SITE_PATH.'main.css');
 	$content = evStrReplace($content,'href="ie.css','href="'.SITE_PATH.'ie.css');
 	$content = evStrReplace($content,'href="index.php','href="'.SITE_PATH.'index.php');
 	return $content;
  }
  
 echo tplFilterOutput(array(
 		'content'=>$cnt
 ));*/