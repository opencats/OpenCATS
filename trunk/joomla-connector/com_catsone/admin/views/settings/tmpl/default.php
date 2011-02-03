<?php defined('_JEXEC') or die('Restricted access');

jimport( 'joomla.environment.uri' );
$uri =& JURI::getInstance();


$db =& JFactory::getDBO();
$db->setQuery("SELECT * From #__catonesettings limit 1");
$SETTINGS = $db->loadObject();

?>
   <form enctype="multipart/form-data" action="index.php" method="post" name="adminForm">
    <input type="hidden" name="process" value="1">
    <fieldset class="adminform">
    <legend>CATS General Settings</legend>
    <table class="admintable" width="100%" cellspacing="1" cellpadding="0">
       <tr>
        <td class="key">Web Address For Cats</td>
        <td align="left" style="padding:5px"><input class="formBox" type="text" name="Cats_install" maxlength="250" size="30" value="<?php echo $SETTINGS->Cats_install?>"></td>
      </tr>
      <tr>
        <td class="key">Path To Cats Install (Local Only)</td>
        <td align="left" style="padding:5px" width="70%"><input class="formBox" type="text" name="Cats_local" maxlength="250" size="30" value="<?php echo $SETTINGS->Cats_local;?>"></td>
      </tr>
      <tr>
        <td class="key">Cats Email Address</td>
        <td align="left" style="padding:5px" width="70%"><input class="formBox" type="text" name="email" maxlength="250" size="30" value="<?php echo $SETTINGS->email;?>"></td>
      </tr>
    </table>
    </fieldset>
    <fieldset class="adminform">
    <legend>CATS Database Settings</legend>
    <table class="admintable" width="100%" cellspacing="1" cellpadding="0">
       <tr>
        <td class="key">Database Host</td>
        <td align="left" style="padding:5px"><input class="formBox" type="text" name="OC_Database_host" maxlength="250" size="30" value="<?php echo $SETTINGS->OC_Database_host?>"></td>
      </tr>
      <tr>
        <td class="key">Database Name</td>
        <td align="left" style="padding:5px" width="70%"><input class="formBox" type="text" name="OC_Database_Name" maxlength="250" size="30" value="<?php echo $SETTINGS->OC_Database_Name;?>"></td>
      </tr>
      <tr>
      <td class="key">Database Username</td>
        <td align="left" style="padding:5px" width="70%"><input class="formBox" type="text" name="OC_Database_Username" maxlength="250" size="30" value="<?php echo $SETTINGS->OC_Database_Username;?>"></td>
      </tr>
      <tr>
        <td class="key">Database Password</td>
        <td align="left" style="padding:5px"><input class="formBox" type="password" name="OC_Database_password" maxlength="250" size="30" value="<?php echo $SETTINGS->OC_Database_password;?>"></td>
      </tr>

    </table>
    </fieldset>
	
	<fieldset class="adminform">
    <legend>CATS FTP Settings </legend>
    <table class="admintable" width="100%" cellspacing="1" cellpadding="0">
       <tr>
        <td class="key">FTP Host</td>
        <td align="left" style="padding:5px"><input class="formBox" type="text" name="ftp_host" maxlength="250" size="30" value="<?php echo $SETTINGS->ftp_host?>"></td>
      </tr>
      <tr>
        <td class="key">FTP User Name</td>
        <td align="left" style="padding:5px" width="70%"><input class="formBox" type="text" name="ftp_user" maxlength="250" size="30" value="<?php echo $SETTINGS->ftp_user;?>"></td>
      </tr>
      <tr>
      <td class="key">FTP Password</td>
        <td align="left" style="padding:5px" width="70%"><input class="formBox" type="password" name="ftp_password" maxlength="250" size="30" value="<?php echo $SETTINGS->ftp_password;?>"></td>
      </tr>
      <tr>
        <td class="key">Remote Path to Attachments Folder</td>
        <td align="left" style="padding:5px"><input class="formBox" type="text" name="ftp_path" maxlength="250" size="30" value="<?php echo $SETTINGS->ftp_path;?>"></td>
      </tr>
    </table>
    </fieldset>
	<input type="hidden" name="id" value="1" />
	<input type="hidden" name="option" value="com_catsone" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="controller" value="settings" />
    </form>
