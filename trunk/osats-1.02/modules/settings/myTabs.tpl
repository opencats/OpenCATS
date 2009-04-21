<?php
/* This lets you hide/show and rearrange the order of the tabs programatically - Jamin
* OSATS
*/
?>

<?php TemplateUtility::printHeader(' - Settings'); ?>
<?php 
if (MYTABPOS == 'top') {
	osatutil::TabsAtTop();
	TemplateUtility::printTabs($this->active);
}
?>

    <div id="main">
        <?php TemplateUtility::printQuickSearch(); ?>

        	<div id="contents">
            <table>
                <tr>
                    <td width="3%">
                        <img src="images/settings.gif" width="24" height="24" border="0" alt="Settings" style="margin-top: 3px;" />&nbsp;
                    </td>
                    <td><h2>Settings: Tab Works</h2></td>
                </tr>
            </table>

            <p class="note">Manage my Tabs</p>

            <table class="adminTable" width="100%">
                    <td><form action='<?php echo(osatutil::getIndexName()); ?>?m=settings&amp;a=administration&s=myTabs' method='post'>
						<?php
							// This is temporary. I will write a class that does all of the sql calls.
							// but for now, this will do the work. Jamin.
                    		include('./dbconfig.php');
						
							$myServer = mysql_connect(DATABASE_HOST, DATABASE_USER, DATABASE_PASS);
							$myDB = mysql_select_db(DATABASE_NAME);
							$sql = mysql_query("SELECT * FROM moduleinfo WHERE tabtext <> '' ORDER BY ordernum ASC");
							$num_rows = mysql_num_rows($sql);
							
						
						if (!$_POST['update']) 
						{  
                    		//get modules order from database and then save to db and modulesList.php
   	                		if ($myrow = mysql_fetch_array($sql))
				  			 {
								echo "<table border=1>\n";
								echo "<tr><td><b>Module Name</b></td><td><b>Order</b></td><td><b>Visible</b></td></tr>\n";						
								do 
								{
									/*
									$myHome = $myrow["name"];
									if ($myHome == "Home")
									{
										echo "<tr><td>Home (Dashboard)</td><td><input type='text' name='" . $myrow["name"] . "' id='" . $myrow["name"] . "' value='" . $myrow["ordernum"] . "' style='width:25px'/></td></tr>\n";	
									}
									else
									{ 
										*/
										echo "<tr><td>". $myrow["name"] . "</td><td><input type='text' name='" . $myrow["name"] . "' id='" . $myrow["name"] . "' value='" . $myrow["ordernum"] . "' style='width:25px'/></td><td><input type='text' name='" . $myrow["name"] . "visible" . "' id='" . $myrow["name"] . "visible" . "' value='" . $myrow["visible"] . "' style='width:25px'/></td></tr>\n";
										
									//}
								} 
								while ($myrow = mysql_fetch_array($sql));
									echo "</table>\n";
							} 
							else 
							{
								echo "$ref: That record appears to be unavailable"; 
							} 
							mysql_free_result($sql);
							mysql_close();
							?>
                       
						<br>
                    	<input type='submit' name='update' class = 'button' value='Update'/>
						<input type="button" name="back" class = "button" value="Back" onclick="document.location.href='<?php echo(osatutil::getIndexName()); ?>?m=settings';" />
						</form>
						<?php
						} 
						else 
						{ 
							// Update was called. Grab the info and update the DB
							while ($myrow = mysql_fetch_array($sql))
							{ 
								$mystring[] = $myrow['name']; 	
							} 
							echo "<br/>";
							foreach ($mystring as $myVal)
							{
								//update the record with new values
								$myVisible = $myVal . "visible";
								if (mysql_query ("UPDATE moduleinfo SET ordernum = '" . $_POST[$myVal] . "' WHERE name = '" . $myVal . "'"))
								{
									mysql_query ("UPDATE moduleinfo SET visible = '" . $_POST[$myVisible] . "' WHERE name = '" . $myVal . "'");
									echo $myVal . " is now set to :" . $_POST[$myVal] . " and Visible set to: " . $_POST[$myVisible] . "<br/>";
									$content2 = $content2 . "'" . strtolower($myVal) . "'=>'" . $_POST[$myVal] . "',";
								}
							}
							echo "<br/><h2>You must <a href='".osatutil::getIndexName()."?m=logout'>logout</a> and back in again for changes to take affect!</h2>";
							//now write the data to the taborder.php file.
							mysql_free_result($sql);
							mysql_close();
							// Log user out.
							/*
							$_SESSION['OSATS']->logout();
                			unset($_SESSION['OSATS']);
                			osatutil::transferRelativeURI('?m=settings&a=administration&messageSuccess=true&message='.urlencode());
                			*/
							 	
						}
                    	?>
						
                    </td>
                </tr>
            </table>
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