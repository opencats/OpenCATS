<?php
/* Running this will set the Installed value to 0
* and start the installation process. All data will be deleted. - Jamin
* OSATS
*/
?>

<?php TemplateUtility::printHeader('Settings', array('modules/settings/validator.js')); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active, $this->subActive); ?>
<?php
	
	define('_PHP_SELF', $_SERVER['PHP_SELF']);
	!empty($_SERVER['HTTP_HOST'])       ? define('_HTTP_HOST'      , $_SERVER['HTTP_HOST'])       : define('_HTTP_HOST'      , $_ENV['HTTP_HOST']);
	
	if ( !defined('OSATS_URL') && OSATS_URL != '' ) 
	{
		$root_url = "http://" . _HTTP_HOST. preg_replace("'index.php$'i", "", _PHP_SELF);
		$base_path = str_replace('\\', '/', getcwd());
		if ( substr($base_path, -1) == '/') 
		{
			$base_path = substr($base_path, 0, -1);
		}
		define("OSATS_URL", $root_url);
	}
	$installpath = OSATS_URL . "_install/resetbit.php";	
?>

    <div id="main">
        <?php TemplateUtility::printQuickSearch(); ?>

        <div id="contents">
            <table>
                <tr>
                    <td width="3%">
                        <img src="images/settings.gif" width="24" height="24" border="0" alt="Settings" style="margin-top: 3px;" />&nbsp;
                    </td>
                    <td><h2>Settings: Administration</h2></td>
                </tr>
            </table>

            <p class="note">Installation Reinstall</p>

            <table class="searchTable" width="100%">
                <tr>
                    <td>
                        <table class="editTable" width="700">
                            <tr>
                                <td class="tdVertical" style="width:320px;">
                                    WARNING -  Choosing Re-Install will WIPE all data!!!!
                                </td>
                                
                            </tr>
                        </table>
                        <input type="button" name="install" class = "button" value="Re-Install" onclick="document.location.href='<?php echo $installpath;?>'"/>
                        <input type="button" name="back" class = "button" value="Back" onclick="document.location.href='<?php echo(osatutil::getIndexName()); ?>?m=settings&amp;a=administration';" />
                    </td>
                </tr>
            </table>
        </div>
    </div>
    <div id="bottomShadow"></div>
<?php TemplateUtility::printFooter(); ?>