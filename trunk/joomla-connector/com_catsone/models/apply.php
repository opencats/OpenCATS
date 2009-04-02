  <?php

// Check to ensure this file is included in Joomla!
//defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.application.component.model');

class CatsoneModelapply extends JModel
{
	function applyJob($options)
	{
		jimport('joomla.database.database');
		jimport( 'joomla.database.table' );
		$conf =& JFactory::getConfig();
		// TODO: Cache on the fingerprint of the arguments
		//$db			=& JFactory::getDBO();
		$driver 	= $conf->getValue('config.dbtype');
		$host 		= $conf->getValue('config.host');
		/*
		$userid 		= $conf->getValue('config.user');
		$password 	= $conf->getValue('config.password');
		$database	= $conf->getValue('config.db');
		$prefix 	= $conf->getValue('config.dbprefix');
		
		$debug 		= $conf->getValue('config.debug');
		*/
		$optionDb = array ( 'driver' => $driver, 'host' => 'yourcatsservername', 'user' => 'yourcatsdbusername', 'password' => '$yourcatsdbpassword', 'database' => 'yourcatsdbname', 'prefix' => "" );
		//$optionDb	= array ( 'driver' => $driver, 'host' => $host, 'user' => $userid, 'password' => $password, 'database' => 'chasejob_cats', 'prefix' => "" );
		$db =& JDatabase::getInstance( $optionDb );
		$query = "Insert into candidate (candidate_id,site_id,last_name,first_name,phone_home,phone_cell,phone_work,address,city,state,zip,key_skills,entered_by,owner,date_created,email1,best_time_to_call) values (NULL,'1','".$options['lastname']."','".$options['firstname']."','".$options['homephone']."','".$options['mobilephone']."','".$options['workphone']."','".$options['mailingaddress']."','".$options['city']."','".$options['state']."','".$options['zip']."','".$options['skill']."','".$user->id."','".$user->id."','".date( 'Y-m-d H:i:s')."','".$options['email_address']."','".$options['besttimetocall']."')";
		$db->setQuery($query);
		$db->query();
		$id = $db->insertID();
		$query1 = "Insert into candidate_joborder (candidate_joborder_id,candidate_id,joborder_id,site_id,status,date_submitted,date_created,rating_value) values (NULL,'".$id."','".$options['joborder_id']."','1','100','".date( 'Y-m-d H:i:s')."','".date( 'Y-m-d H:i:s')."','-1')";
		$db->setQuery($query1);
		$db->query();
//		$query2 = "insert into attachment(attachment_id,data_item_id,data_item_type,site_id,title,original_filename,stored_filename,resume,text,date_created,directory_name) values (NULL,'".$id."','100','1','".$options['file_name']."','".$options['file_name']."','".$options['file_name']."','1','".$options['cv_text']."','".date( 'Y-m-d H:i:s')."','site_1/0xxx/8b6db30a4c7e2d71ef54beaad5a9c4e1/')";

		$query2 = "insert into attachment(attachment_id,data_item_id,data_item_type,site_id,title,original_filename,stored_filename,resume,text,date_created,directory_name) values (NULL,'".$id."','100','1','".$options['file_name']."','".$options['file_name']."','".$options['file_name']."','1','".$options['cv_text']."','".date( 'Y-m-d H:i:s')."','cv/')";
		
		$db->setQuery($query2);
		$db->query();
		
		//Inset vao bang activity
		$db->setQuery("Insert into activity (activity_id,data_item_id,data_item_type,joborder_id,site_id,entered_by,date_created,type,notes,date_modified) values (NULL,'".$id."','100','".$options['joborder_id']."','1','1250','".date( 'Y-m-d H:i:s')."','400','User applied through candidate portal','".date( 'Y-m-d H:i:s')."')");
		$db->query();


		//Gui mail cho nguoi quan ly job de thong bao co nguoi moi apply
		//lay ve recruiter
		$db->setQuery("Select recruiter,title from joborder where joborder_id = '".$options['joborder_id']."'");
		$r = $db->loadObjectList();
		$r = $r[0];
		$db->setQuery("Select * from user where user_id = '".$r->recruiter."'");
		$userRow = $db->loadObjectList();
		$userRow = $userRow[0];
		
		$htmlMessage = "<html><head><title>Chasejobs</title></head><body>";
		$htmlMessage.="<table cellpadding=0 cellspacing=0 width=100% style='border:1px solid #cccccc;padding:20px;'>";
		$htmlMessage.="<tr><td width=100%>";
		$htmlMessage.="<b>Dear ".$userRow->last_name."</b>";
		$htmlMessage.="<br>This e-mail is a notification that a candidate has applied to your job order through the online candidate portal.";
		$htmlMessage.="<br><Br><b>Job order :</b>&nbsp;".$r->title;
		$htmlMessage.="<br><b>Candidate Name :&nbsp;</b>".$options['firstname']." ".$options['lastname'].".";
		$htmlMessage.="<br><b>Candidate URL:&nbsp;</b> <a href='http://www.opencats.org/index.php?m=candidates&a=show&candidateID=".$id."'>http://www.opencats.org/index.php?m=candidates&a=show&candidateID=".$id."</a>.";
		$htmlMessage.="<br><b>Job Order URL:&nbsp;</b> <a href='http://www.opencats.org/index.php?m=joborders&a=show&jobOrderID=".$options['joborder_id']."'>http://www.opencats.org/index.php?m=joborders&a=show&jobOrderID=".$options['joborder_id']."</a>."		;
		$htmlMessage.="<br><br>CATS<br>ChaseJobs.";
		$htmlMessage.="</td></tr></table></body></html>";
		
		//Gui mail
		JUtility::sendMail('russellh@ysmail.net','ChaseJobs',$userRow->email,'CATS - A Candidate Has Applied to Your Job',$htmlMessage,1);


		$db->setQuery("Select questionnaire_id from joborder where joborder_id = '".$options['joborder_id']."'");
		$question =  $db->loadResult();
		$q['question'] = $question;
		$q['id'] = $id;
		return $q;
	}
	function getCatsone( $options )
	{
		jimport('joomla.database.database');
		jimport( 'joomla.database.table' );
		$conf =& JFactory::getConfig();
		// TODO: Cache on the fingerprint of the arguments
		//$db			=& JFactory::getDBO();\/
		/*
		$user 		= $conf->getValue('config.user');
		$password 	= $conf->getValue('config.password');
		$database	= $conf->getValue('config.db');
		$prefix 	= $conf->getValue('config.dbprefix');
		$debug 		= $conf->getValue('config.debug');
		*/
		$driver 	= $conf->getValue('config.dbtype');
		$host 		= $conf->getValue('config.host');
		//$options	= array ( 'driver' => $driver, 'host' => $host, 'user' => $user, 'password' => $password, 'database' => 'chasejob_cats', 'prefix' => "" );
	$options	= array ( 'driver' => $driver, 'host' => 'yourcatsservername', 'user' => 'yourcatsdbusername', 'password' => '$yourcatsdbpassword', 'database' => 'yourcatsdbname', 'prefix' => "" );
		$db =& JDatabase::getInstance( $options );
		$query = "Select joborder.*,extra_field.*,user.user_id,user.last_name,company.company_id,company.name from joborder,extra_field,user,company where extra_field.data_item_id = joborder.joborder_id and joborder.status = 'active' and joborder.entered_by = user.user_id  and joborder.company_id = company.company_id ";
		if($options['jobType']!="")
		{
			$query.=" and extra_field.value like '".$options['jobType']."'";
		}
		$db->setQuery($query,@$options['limitstart'], @$options['limit']);
	
		$result = $db->loadObjectList();
	
		return $result;
	}
}