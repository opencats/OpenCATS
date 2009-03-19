<?php /* OSATS ABOUT TAB */ ?>

<?php TemplateUtility::printHeader(' - About OSATS', array('main.css')); ?>
<?php 
if (MYTABPOS == 'top') {
	osatutil::TabsAtTop();
	TemplateUtility::printTabs($this->active);
}
?>

    <?php /* <div id="main"> is the main page area. */ ?>
    <div id="main">
        <?php /* Print the quick search / MRU bar. */ ?>
        <?php TemplateUtility::printQuickSearch(); ?>
        <div id="contents">
            <table>
                <tr>
                    <td><img src="images/home.gif" width="24" height="24" border="0" alt="house" style="margin-top: 3px;" />&nbsp;</td>
                    <td><h2>About OSATS</h2></td>
                </tr>
            </table>

            <p class="note">Open Source Applicant Tracking System</p>

            <table>
                <tr>
                    <td>
                        OSATS came about because of a Applicant Tracking System application was made open source at first... then the creator decided to go commercial!<br/>
						So... a community of real open source heros came together to bring about the worlds TRULY open source applicant tracking system. <br/>
						Visit ------ for more info. 
                    </td>
                </tr>
            </table>
            <br />
			<?php
$db = DatabaseConnection::getInstance();
$databaseVersion = $db->getRDBMSVersion();

$installationDirectory = realpath('./');

if (SystemUtility::isWindows()) {
    $OSType = 'Windows';
} else
    if (SystemUtility::isMacOSX()) {
        $OSType = 'Mac OS X';
    } else {
        $OSType = 'UNIX';
    }

    $schemaVersions = ModuleUtility::getModuleSchemaVersions();

echo "* You are running " . $databaseVersion;
echo "<br/>* OSATS is installed in " . $installationDirectory;
echo "<br/>* Your OS is " . $OSType . "(" . php_uname() . ")";
echo "<br /> * The awesome PHP version " . PHP_VERSION . " is running.";
?>
        </div>
<?php
if (MYTABPOS == 'bottom') 
{
    
	TemplateUtility::printTabs($this->active);
	?>
	</div>
    <div id="bottomShadow"></div>
    
    <?php 
	osatutil::TabsAtBottom();
}else{
	?>
	</div>
    <div id="bottomShadow"></div>
    <?php 
}
?>
<?php TemplateUtility::printFooter(); 
		
?>
