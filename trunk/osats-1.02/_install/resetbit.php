<?php

/* Running this page will set the Installed value to 0
* This is for programmers only. Do not run unless you want to re-install clean! - Jamin
* OSATS
*/
include_once ('../dbconfig.php');
mysql_connect(DATABASE_HOST, DATABASE_USER, DATABASE_PASS);
mysql_select_db("osats");
if(MySQL_query("UPDATE system SET Installed = NULL"))
{
	
	echo "<h2>Reinstallation Bit has been set!</h2>";
	//echo "If you did NOT want to reinstall, you can safely change the bit back. Choose Go Back below."
	echo "<h3>If you DONT want to reinstall. FOLLOW THIS STEP CAREFULLY!</h3>";
	echo "- Manually go into the mySql db and change the 'Installed' value from NULL to a '1'.";
	echo "  then you can reload the main OSATS login in your browser.";
	
	echo "<h3>If you are sure you want to reinstall: </h3>"; 
	echo "Press the 'Lets Do It!' button below.";
}
else
{
	echo "<h3>Unable to reset!</h3>";
}
mysql_close();

?>
		<div>
		<form action="../index.php" method="post">
		<br /><input type="submit" class="button" name="op" value="Lets Do It!" />	
		</form></div>