<?php

/* Install Mechanism that makes it almost seamless on the individual!
* OSATS
*/

ob_start();
include_once ('include/functions.php');
//---------------------------------------------------------------------------------------//

/* check to see if the php.ini is set for auto.start on the sessions. */
if (ini_get('session.auto_start') !== '0' &&
    ini_get('session.auto_start') !== 'Off')
{
    die('We cant have session.auto_start running. Please set it to 0 in php.ini.');
}


function dbdata() 
{
	//get db user and password first!
?>
	<div>
	<form action="<?=$_SERVER['PHP_SELF'];?>" method="post">
	Enter the username to access the mySQL Server <input type="text" name="dbusername" />
	<br />
	Enter the password to access the mySQL Server <input type="password" name="dbpwd" />
	<br /><br />
	<input type="submit" class="button" name="op" value="Next" />
	</form></div>
<?php
	
}

/*OSATS MAIN INSTALLATION ROUTINE  */
function welcome()
{
	global $_POST, $dbname, $_COOKIE;
	$dbhost      = "localhost";
	$dbpass      = $_POST['dbpwd'];
	$dbname      = "osats";
	$dbuname     = $_POST['dbusername'];
		
	//test db before continuing.
	if (!$link = mysql_pconnect($dbhost, $dbuname, $dbpass))
	{
		Echo "<h3>Please check your Username and Password for access to the mySQL DB</h3>";
		?>
		<div>
		<form action="<?=$_SERVER['PHP_SELF'];?>" method="post">
		Username to access the mySQL Server: <input type="text" name="dbusername" />
		<br />
		Password to access the mySQL Server: <input type="password" name="dbpwd" />
		<br /><br />
		<input type="submit" class="button" name="op" value="Next" />
		</form></div>
		<?php
	}
	else
	{
    	$lang = $_COOKIE['lang'] ? $_COOKIE['lang'] : 'english';
		$checks = array();
    	@chmod("dbconfig.php", 0666);
    	@is_writable("dbconfig.php") ? $checks[] = "<h3>The Config.php is writable - GOOD!</h3>" : 
		$checks[] = "<h3>The dbconfig.php file in the root is not Writable. Please Fix and refresh this page.</h3>";
    
		if (!function_exists('mysql_get_client_info'))
    	{
        	$checks[] = sprintf("<h3>mySQL is not properly installed. Please fix and then refresh this page.</h3>");
    	}
    	else
    	{
        	version_check('3.23', mysql_get_client_info()) ? $checks[] = sprintf("mySQL is installed - GOOD!",
            mysql_get_client_info()) : $checks[] = printf("mySQL is not properly installed. Please fix and then refresh this page.",
            mysql_get_client_info());
    	}

    	version_check('4.11', phpversion()) ? $checks[] = sprintf("PHP is installed and acceptable version - GOOD!",
        phpversion()) : $checks[] = printf("Your PHP installation is not new enough or not Installed. Please correct and then Refresh this page.",
        phpversion());

    	$root_path   = OSATS_ROOT_PATH;
   		$xoops_url   = OSATS_URL;
		$prefix      = "runcms";
		$database    = "mysql";    
		$content = '<?php
			/** OSATS Database Config file */
			// OSATS DB UserID
			define(\'DATABASE_USER\', \'' . $dbuname . '\');
			// OSATS DB Password
			define(\'DATABASE_PASS\', \'' . $dbpass . '\');
			// OSATS DB Host
			define(\'DATABASE_HOST\', \'' . $dbhost . '\');
			// OSATS DB Name
			define(\'DATABASE_NAME\', \'' . $dbname . '\');
				?>';
				
    	if (!$file = fopen('dbconfig.php', 'w'))
    	{
        	echo "<h3>Error Writing to Config.php  -  Go to the file and make sure the permissions are set to READ & WRITE - Then REFRESH this page!</h3>";
        	exit();
    	}

    	if (fwrite($file, $content) == -1)
    	{
        	echo "<h3>I can open dbconfig.php but cant write to it! -  Go to the file and make sure the permissions are set to READ & WRITE - Then REFRESH this page!</h3>";
        fclose($file);
        	exit();
    	}   
    	echo "<br/><h3>We successfully updated the dbconfig.php file!</h3>";
    
    
		?>
		<div>
		<form action="<?=$_SERVER['PHP_SELF'];?>" method="post">
		OK. Now we are going to test and setup the database called osats.
		<br />This process can take some time. Be patient and watch the progress bar!
		<br />
		<br /><h3>WARNING!<br />
		IF YOU ALREADY HAVE A DATABASE CALLED 'OSATS', THIS PROCESS WILL DELETE ALL DATA!<br />
		WARNING! </h3> 
		<br /><input type="submit" class="button" name="op" value="Setup-DB" />	
		</form></div>
		<?php
	}
}
//---------------------------------------------------------------------------------------//
function setup_db() 
{
include_once(OSATS_ROOT_PATH). "/dbconfig.php";
$dbname = DATABASE_NAME;
$dbuname = DATABASE_USER;
$dbpass = DATABASE_PASS;
$dbhost = DATABASE_HOST;

		
$link = mysql_pconnect($dbhost, $dbuname, $dbpass);

if (!$link) 
	{
    die('Could not connect: ' . mysql_error());
	}

	$sql = 'CREATE DATABASE osats';

if (mysql_query($sql, $link)) 
	{
	echo "<br/><br/><h3>Database created successfully\n</h3>";
	$myDBGood = true;
	} 
else 
	{
	//DB already exists - drop and recreate
    $sql = 'DROP DATABASE osats';
    if (mysql_query($sql, $link))
    {
    	$sql = 'CREATE DATABASE osats';
    	if (mysql_query($sql, $link))
    	{
			echo "<br/><br/><h3>Database was dropped and recreated to empty the contents.\n</h3>";
    		$myDBGood = true;
 		}
    }
	//echo '<br/><br/>Error creating database: ' . mysql_error() . "\n";
    }

if ($myDBGood = true)
	{
		//load the tables
		load_tables();
	}

else
	{
		echo "<h3>Something didnt go right with the db setup!</h3>";
	}

?>
	<div>
	<form action="<?=$_SERVER['PHP_SELF'];?>" method="post">
	<br />
	If no errors at all... the DB has been created or already exists!
	<br />
	We will now finalize the database.
	<br />
	Choose a password for the OSATS Admin account: <input type="password" name="adminpwd" />
	<br />
	Enter the email address that OSATS will reply from: <input type="text" name="adminemail" />
	<br />
	Give your Site a name: <input type="text" name="sitename" />
	<br /><br />
	After entering the information above - Press Finsih
	<br />
	<br /><input type="submit" class="button" name="op" value="Finish" />
	</form></div>
<?php 

}

//---------------------------------------------------------------------------------------//

function load_tables()
{
	$installdb = file_get_contents(OSATS_ROOT_PATH . '/db/osatsdb.sql');
		$mySQL1 = explode(';', $installdb);
		mysql_connect(DATABASE_HOST, DATABASE_USER, DATABASE_PASS);
		@mysql_select_db(DATABASE_NAME) or die( "Unable to select database");
		//$link = mysql_pconnect($dbhost, $dbuname, $dbpass);
		foreach ($mySQL1 as $SQL2)
		{
			$SQL2 = trim($SQL2);
			if (empty($SQL2))
        		{
					continue;
        		}
		mysql_query($SQL2);
		//mysql_free_result(mysql_query);
		//echo "<br/>" . $SQL2;
		
		//echo $SQL2;
        		
		}
		mysql_close();
}

function mark_installed()
{
	$myPassword = $_POST['adminpwd'];
	
	mysql_connect(DATABASE_HOST, DATABASE_USER, DATABASE_PASS);
	@mysql_select_db(DATABASE_NAME) or die( "Unable to select database");
	if(MySQL_query("UPDATE system SET Installed = 1"))
	{
		echo "<br/><h3>Installed setting has been set to 1</h3>";
	}
	else
	{
		echo "<br/><h3>Installed setting did NOT set correctly!</h3>";
	}
	//MySQL_query("UPDATE user SET userid = 1");
	if(MySQL_query("INSERT INTO user (user_name) VALUES ('admin')"))
	{
		MySQL_query("UPDATE user SET last_name = 'Admin' WHERE user_name = 'admin'");
		MySQL_query("UPDATE user SET first_name = 'OSATS' WHERE user_name = 'admin'");
		MySQL_query("UPDATE user SET access_level = 500 WHERE user_name = 'admin'");
		MySQL_query("UPDATE user SET password = '". $_POST['adminpwd'] . "' WHERE user_name = 'admin'");
		MySQL_query("UPDATE user SET email = '". $_POST['adminemail'] . "' WHERE user_name = 'admin'");
		mysql_query("UPDATE site SET name = '" . $_POST['sitename'] . "' WHERE site_id = 1");
		mysql_query("UPDATE user SET site_id = 1 WHERE user_name = 'admin'");
	}
	else
	{
		echo "<br/><h3>Default USER values did NOT set correctly!</h3>";
	}
	
	mysql_close();
	
	?>
	<div>
	<form action="<?php OSATS_ROOT_PATH . '/index.php'?>" method="post">
	Done!  You may now login. <br /> 
	Username = admin<br />
	Password = <?php echo $_POST['adminpwd']; ?><br />
	<br /><input type="submit" class="button" name="op" value="Go to Login" />
	</form></div>
<?php 
	
}
//---------------------------------------------------------------------------------------//
/**
 * Description
 *
 * @param type $var description
 * @return type description
 */
function do_chmod($file, $value, $type = 'chmod')
{

    switch ($type)
    {
        case 'ftp':
            break;

        default:
            @chmod($file, $value);
    }

    if ($value == 0666 || $value == 0777)
    {
        if (!is_writable($file))
        {
            return false;
        }
    }

    return true;
}


//---------------------------------------------------------------------------------------//
/** The routine to roll through setup when hitting the next button...
 */
switch ($_POST['op'])
{

    case "setupcheck":
        setcookie("lang", $_POST['lang'], time() + 1800, "/");
        setup_check();
        break;

    case "dbdata":
        dbdata();
        break;

    case "dbconfirm":
        dbconfirm();
        break;

    case "Next":
			welcome();
        	break;	
        
	case "Setup-DB":
		setup_db();
		break;
	
    case "Finish":
        mark_installed();
        break;

    case "Install":
		install_tables();
        break;

    default:
        dbdata();
        break;
}

ob_end_flush();

?>
