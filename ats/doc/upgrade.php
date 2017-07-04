<?php 
    include_once('constants.php');
    include_once('config.php');

    /* We aren't using any TemplateUtility methods that require us to pull in
     * any of its dependencies.
     */
    /* Version check before we include this. */
   
    $phpVersion = phpversion();
    $phpVersionParts = explode('.', $phpVersion);
    if ($phpVersionParts[0] >= 5)
    {
        include_once('lib/TemplateUtility.php');
    }
    else
    {
        $php4 = true;
    }
	$warning = '';
	$upgUrl = 'http://ats.2al.pl/';	
	$tempDir = CATS_TEMP_DIR.'/';
	$atsDir = dirname(__FILE__);
	$atsPath = basename($atsDir);
	$atsDirBase = evStrReplace($atsDir,$atsPath,'');
	set_time_limit ( 120 );
	
	$krok = (isset($_GET['krok']))?$_GET['krok']:'1';
	$archDir = '';
	//obsługa wejscia
	switch($krok){
		case '2':
			if (!isset($_POST['file']) || evStrEmpty($_POST['file'])){
				$warning = 'Wybierz wersję poniżej zaznaczając jedną z opcji';
				$krok=1;
				break;
			}
			// ok sciagamy wskazany ...
			$zipCnt = file_get_contents($upgUrl.$_POST['file']);
			//cho 'size'.strlen($zipCnt);
			//cho 'contntTo:'.$tempDir.$_POST['file'];
			if (!file_exists($tempDir)) {mkdir($tempDir);}
			if (file_put_contents($tempDir.$_POST['file'], $zipCnt)===false){
				$warning = 'Wystąpił błąd: '.error_get_last()['message'];
				$krok=1;
				//cho nl2br(print_r(error_get_last(),true));
			}			
			break;
		case '3':
			if (evStrEmpty($_POST['filePath'])){
				$warning = 'Błąd - brak nazwy pliku do rozpakowania - powtórz instalację';
				$krok=2;
				break;
			}	
			$zip = new ZipArchive;
			$res = $zip->open($_POST['filePath']);
			if ($res === TRUE) {
			  //$tmpFolder = 	evStrReplace($_POST['filePath'],'.zip','');
			  $tmpFolder = $atsDirBase;
			  $zip->extractTo($tmpFolder);
			  $zip->close();
			} else {
			  $warning = 'Błąd otwarcia pliku '.$_POST['filePath'].' - powtórz instalację';
			  break;
			}	
			//kopia config.php do tmpFolder 
			$cfgFile = dirname(__FILE__).'/config.php';
			$cfgStr = file_get_contents($cfgFile);
			if ($cfgStr===false){
				 $warning = 'Błąd odczytu pliku konfiguracji: '.$cfgFile.' - powtórz instalację';
				 $krok=2;
				 break;
			} 
			if (evStrContains($cfgStr,'ATS_VERSION')){
				//$cfgStr = substr($cfgStr,0,strlen($cfgStr)-4);
				//$res = evRunBuf('cfgContents106',array());
				//$cfgStr.="\n\n".$res['output']."\n\n".'?'.'>';
				$cfgStr = evStrReplace($cfgStr,'ATS_VERSION','ATS_BASE_VERSION', true);
			}
			if (file_put_contents($tmpFolder.'/OpenATS/config.php', $cfgStr)===false){
				$warning = error_get_last()['message'];
				$krok=2;
				break;
				//cho nl2br(print_r(error_get_last(),true));
			}	
			if (unlink($tmpFolder.'/OpenATS/INSTALL_BLOCK')===false){
				$warning = 'Błąd zdjęcia blokady z pliku OpenATS/INSTALL_BLOCK - plik do usunięcia ręćznie';	
			}		
			include_once('lib/DatabaseConnection.php');
			$db = DatabaseConnection::getInstance();
			$sql = 'update user set column_preferences = null;';
			$db->query($sql);
			
			$sql = 'ALTER TABLE candidate AUTO_INCREMENT=3001';
			$db->query($sql);
			
			$atSource = $atsDir .'/attachments';
			$atDest = $atsDirBase.'/OpenATS/attachments';
			if (!recurse_copy ($atSource,$atDest)){
				$warning.='Błąd przy kopiowaniu danych z '.$atSource.' do '.$atDest.' - wykonaj kopię ręcznie przed zmianą nazw katalogów.';
			}
			/*//opy (dirname(__FILE__).'/config.php',$tmpFolder.'/OpenATS/config.php');
			$today = date("Ymd_His");
			$archDir = $atsDir.'.'.$today;
			if (rename($atsDir,$archDir)===false){
				$warning = 'Błąd przy zmianie nazwy katalogu "'.$atsDir.'" na "'.$atsPath.'.'.$today.'" - powtórz instalację';
				break;
			}
			if (rename($tmpFolder.'OpenATS',$atsDir)===false){
				$warning = 'Błąd przy zmianie nazwy katalogu "'.$tmpFolder.'OpenATS'.'" na "'.$atsPath.'" - powtórz instalację';
				break;				
			}*/			
			break;
			case '4':
			if (file_exists($atsDirBase.'/OpenATS/config.php')){
				$warning = 'Katalog <b>'.$atsDirBase.'/OpenATS</b> nadal istnieje !';
				$krok = 3;
				break;
			}
			if (file_exists($atsDir.'/upgrade.php')){
				$warning = 'Katalog <b>'.$atsDir.'</b> nadal zawiera starą wersję aplikacji !';
				$krok = 3;
				break;
			}			
			break;			
			
	}
	
	$res=null;
	$kda = array(
		'1'=>'Wybór wersji',
		'2'=>'Ładowanie pliku',
		'3'=>'Instalacja nowej wersji',
		'4'=>'Rekonfiguracja'
		);
	
	//Obs�uga wyjscia
	switch($krok){
		case '1':
			$vList = file_get_contents($upgUrl.'?a=list');
			$files = explode(';',$vList);
			$res = evRunBuf('cntKrok1',array('files'=>$files,'url'=>$upgUrl,'warning'=>$warning));
		break;
		case '2':
			$res = evRunBuf('cntKrok2',array(
				'file'=>$_POST['file'],
				'filePath'=>$tempDir.$_POST['file'],
				'warning'=>$warning
				)); 
		break;
		case '3':
			//if (evStrEmpty($warning)){
				$res = evRunBuf('cntKrok3',array(
					'file'=>$_POST['file'],
					'filePath'=>$_POST['filePath'],
					'archDir'=>$archDir,
					'warning'=>$warning,
					'atsDirBase'=>$atsDirBase
					)); 
			//}
		break;	
		case '4':
			if (evStrEmpty($warning)){
				$res = evRunBuf('cntKrok4',array(
					'file'=>$_POST['file'],
					'filePath'=>$_POST['filePath'],
					'archDir'=>$archDir,
					'warning'=>$warning,
					'atsDirBase'=>$atsDirBase
					)); 
			}
		break;			
	}

upgView(array('krok'=>$krok,
	'krokDesc'=>$kda[$krok],
	'kda'=>$kda,
	'content'=>$res['output'],
	'warning'=>$warning,
	'atsDir'=>$atsDir,
	'atsDirBase'=>$atsDirBase
	));


function cfgContents106($args){
?>	
define('PROJECT_DIR',dirname(__FILE__));
define('LOCALE_DIR', PROJECT_DIR .'/locale');
define('DEFAULT_LOCALE', 'pl');
define('ATS_CV_INDEXING_INFO_URL', 'http://www.catsone.com/resumeIndexingSoftware.php?');
define('ATS_DB_BACKUP_FILENAME', 'catsbackup.bak');
define('ATS_DEMO_FICT_COMPANY','MyCompany.NET');
define('ATS_DEMO_FICT_USER','john@mycompany.net');
define('ATS_DEMO_FICT_PASS','john99');
define('ATS_CT_DOMAIN','catsone.com');
define('ATS_FORUM_URL','http://www.opencats.org/forums/');
define('ATS_APPLOGO_HREF','https://assunto.pl/templates/assunto/images/presets/preset6/logo.svg');
define('ATS_VERSION','106');
include_once('./locale/lang.php');	
<?php	
}

function cntKrok1($args){
?>
<h4>Dostępne wersje:</h4> <br/>
<form action="upgrade.php?krok=2" method="post" onSubmit="upgOnSubmit('Proszę czekać ...');">
<?php foreach($args['files'] as $k =>$val){ ?>
  <input type="radio" name="file" value="<?php echo $val;?>"> <?php echo $val;?><br>
<?php } ?>
<br/><br/>
<input type="submit" class="button" value="Dalej"/>
</form>
<?php	
}

function cntKrok2($args){
?>
<?php if (evStrEmpty($args['warning'])){?>
<h4>
Plik <?php echo $args['file'];?> został pobrany do <?php echo $args['filePath'];?>.<br/> <br/>
Kliknij "dalej" aby go rozpakować.</h4><br/>
<?php } ?>
<form action="upgrade.php?krok=3" method="post" onSubmit="upgOnSubmit('Proszę czekać ...');">
<input type="hidden" name="file" value="<?php echo $args['file'];?>"/>
<input type="hidden" name="filePath" value="<?php echo $args['filePath'];?>"/>
<br/><br/>
<input id="button" type="submit" class="button" value="Dalej"/>
</form>
<?php	
}

function cntKrok3($args){
	$actual_link = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
	$pos = strpos($actual_link,'upgrade.php');
	$catsUrl = substr($actual_link,0,$pos);
?>
<?php if (evStrEmpty($args['warning'])){?><h4>
Wstępna instalacja zakończona pomyślnie.</h4>
<?php } ?>
<p>
Teraz trzeba przejść do <b><?php echo $args['atsDirBase'];?></b> i:<br/><br/>
<ul>
<li>Zmienić nazwę folderu <b>"ats"</b> na np <b>"ats.<?php echo date('Ymd');?>"</b> (stara wersja)</li>
<li>Zmienić nazwę folderu <b>"OpenATS"</b> na <b>"ats"</b> (nowa wersja)</li><br/>
</ul>
</p><br/><br/>

<form action="upgrade.php?krok=4" method="post" onSubmit="upgOnSubmit('Proszę czekać ...');">
<input type="hidden" name="file" value="<?php echo $args['file'];?>"/>
<input type="hidden" name="filePath" value="<?php echo $args['filePath'];?>"/>
<br/><br/>
<input id="button" type="submit" class="button" value="Dalej"/>
</form>
<?php	
}

function cntKrok4($args){
	$actual_link = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
	$pos = strpos($actual_link,'upgrade.php');
	$catsUrl = substr($actual_link,0,$pos);
?>
<?php if (evStrEmpty($args['warning'])){?><h4>
Nowa wersja ATS jest już dostępna.</h4>
<p>
Teraz trzeba kliknąć na <strong><a style="font-size:18px;" href="<?php echo $catsUrl; ?>"><?php echo $catsUrl; ?></a></strong> i kontynuować rekonfigurację</li><br/><br/><br/>
</p>
<?php } ?>
<form action="upgrade.php?krok=4" method="post" onSubmit="upgOnSubmit('Proszę czekać ...');">
<input type="hidden" name="file" value="<?php echo $args['file'];?>"/>
<input type="hidden" name="filePath" value="<?php echo $args['filePath'];?>"/>
<br/><br/>
<input id="button" type="submit" class="button" value="Dalej"/>
</form>
<?php	
}

function evRunBuf($func,$args){
	ob_start();
	$result=call_user_func($func,$args);
	$output=ob_get_contents();
	ob_end_clean();
	return array('result'=>$result,'output'=>$output);
}

function evStrNotEmpty($vr){return ((isset($vr)) && ($vr!=''));}
function evStrEmpty($vr){return !evStrNotEmpty($vr);}
function evEmptyOrZero($vr){ return (evEmpty($vr) || $vr == '0');}

function upgView($args){	
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
    <head>
        <title>OpenATS - Aktualizacja</title>		
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <script type="text/javascript" src="js/lib.js"></script>
        <script type="text/javascript" src="js/install.js"></script>
        <script type="text/javascript" src="js/submodal/subModal.js"></script>
        <style type="text/css" media="all">@import "modules/install/install.css";</style>
		<style type="text/css" media="all">@import "main.css?v=904";</style>
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    </head>
	

    <body>
<script type="text/javascript">
//<![CDATA[


function upgOnSubmit(txt){
	var d=document.getElementById('waitDiv');
	//d.style.visibility = 'visible';
	d.style.display='block';
	
}
//]]>
</script>	
        <div id="headerBlock">
            <span id="mainLogo">OpenATS</span><br />
            <span id="subMainLogo">Skrypt Aktualizacji</span>
        </div>
	

        <div id="contents">
			
            <div id="login">
<?php if (!evStrEmpty($args['warning'])){?>
<div id="warning"><p class="warning">
	<?php echo $args['warning'];?>
</p>
</div>
<?php } ?>			
                <table>
                    <tr>
                        <td style="vertical-align: top; width: 200px;">
                            <table style="vertical-align: top; width: 200px; border: 1px solid #ccc;margin-top:20px;">
                                <tr>
                                    <td>
<?php foreach($args['kda'] as $k =>$val){ ?>
                                        <div id="step1" style="text-align: left;<?php if ($k==$args['krok']){ echo 'font-weight: bold;';}?>">
                                            Krok <?php echo $k;?>: <?php echo $val;?><br /><br />
                                        </div>
<?php } ?>										
                                    </td>
                                </tr>
                            </table>
                            <br />
                        </td>

                        <td style="vertical-align: top; width: 550px; padding-left: 15px;">
                            <table width="100%">
                                <tr>
                                    <td style="vertical-align: top; text-align: left;" id="allSpans">									
										<?php echo $args['content']; ?>
										<div id="waitDiv" style="display:none;">
										<p> <i class="fa fa-circle-o-notch fa-spin" style="font-size:24px"></i> Proszę czekać ...</p>
										</div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </body>
</html>
<?php	
}//upgView

function str_replace_first($from, $to, $subject)
{
	$from = '/'.preg_quote($from, '/').'/';

	return preg_replace($from, $to, $subject, 1);
}

function str_ireplace_first($from, $to, $subject)
{
	$from = '/'.preg_quote($from, '/').'/i';

	return preg_replace($from, $to, $subject, 1);
}

function evStrReplace($subject, $from, $to, $once = false, $caseins = false){
	if ($caseins){
		if ($once){
			return str_ireplace_first($from, $to, $subject);
		} else {
			return str_ireplace($from, $to, $subject);
		}
	} else {
		if ($once){
			return str_replace_first($from, $to, $subject);
		} else {
			return str_replace($from, $to, $subject);
		}
	}
}

function evStrContains($str,$search, $case = false){
	if (!$case){
		$search = strtolower($search);
		$str = strtolower($str);
	}
	return (strpos($str, $search) !== false);
}

function rename_win($oldfile,$newfile) {
    if (!@rename($oldfile,$newfile)) {
        if (recurse_copy ($oldfile,$newfile)) {
            unlink($oldfile);
            return TRUE;
        }
        return FALSE;
    }
    return TRUE;
}

//if( !defined('DS') ) define( 'DS', DIRECTORY_SEPARATOR );

function recurse_copy( $src, $dst ) { 

    $dir = opendir( $src ); 
    @mkdir( dirname( $dst ) );
	@mkdir(  $dst  );
	$ds = '/';

    while( false !== ( $file = readdir( $dir ) ) ) { 
        if( $file != '.' && $file != '..' ) { 
            if( is_dir( $src . $ds . $file ) ) { 
                recurse_copy( $src . $ds . $file, $dst . $ds . $file ); 
            } else { 
                copy( $src . $ds . $file, $dst . $ds . $file ); 
            } 
        } 
    } 
    closedir( $dir ); 
	return true;
}

?>
