  <?php

// Check to ensure this file is included in Joomla!
//defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.application.component.model');

class CatsoneModelapply extends JModel
{
	var $CatsDb;
	var $email, $Cats_install;
	
	function __construct()
	{
		parent::__construct();
		//pull settings from admin
		$db =& JFactory::getDBO();
		$db->setQuery("SELECT * From #__catonesettings limit 1");
		$SETTINGS = $db->loadObject();
		
		jimport('joomla.database.database');
		jimport( 'joomla.database.table' );
		$conf =& JFactory::getConfig();
		// TODO: Cache on the fingerprint of the arguments
		
		$driver 	= $conf->getValue('config.dbtype');
		
		$optionDb	= array ( 'driver' => $driver, 'host' => $SETTINGS->OC_Database_host, 'user' => $SETTINGS->OC_Database_Username, 'password' => $SETTINGS->OC_Database_password, 'database' => $SETTINGS->OC_Database_Name, 'prefix' => "" );
		$this->CatsDb =& JDatabase::getInstance($optionDb);
		$this->email = $SETTINGS->email;
		$this->Cats_install = $SETTINGS->Cats_install;
	
	}
	
	function applyJob($options)
	{


		$query = "Insert into candidate (candidate_id,site_id,last_name,first_name,phone_home,phone_cell,phone_work,address,city,state,zip,key_skills,entered_by,owner,date_created,email1,best_time_to_call) values (NULL,'1','".$options['lastname']."','".$options['firstname']."','".$options['homephone']."','".$options['mobilephone']."','".$options['workphone']."','".$options['mailingaddress']."','".$options['city']."','".$options['state']."','".$options['zip']."','".$options['skill']."','".$user->id."','".$user->id."','".date( 'Y-m-d H:i:s')."','".$options['email_address']."','".$options['besttimetocall']."')";
		$this->CatsDb->setQuery($query);
		$this->CatsDb->query();
		$id = $this->CatsDb->insertID();
		$query1 = "Insert into candidate_joborder (candidate_joborder_id,candidate_id,joborder_id,site_id,status,date_submitted,date_created,rating_value) values (NULL,'".$id."','".$options['joborder_id']."','1','100','".date( 'Y-m-d H:i:s')."','".date( 'Y-m-d H:i:s')."','-1')";
		$this->CatsDb->setQuery($query1);
		$this->CatsDb->query();
		$query2 = "insert into attachment(attachment_id,data_item_id,data_item_type,site_id,title,original_filename,stored_filename,resume,text,date_created,directory_name) values (NULL,'".$id."','100','1','".$options['file_name']."','".$options['file_name']."','".$options['file_name']."','1','".$options['cv_text']."','".date( 'Y-m-d H:i:s')."','site_1/0xxx/8b6db30a4c7e2d71ef54beaad5a9c4e1/')";
		$this->CatsDb->setQuery($query2);
		$this->CatsDb->query();
		
		//Inset vao bang activity
		$this->CatsDb->setQuery("Insert into activity (activity_id,data_item_id,data_item_type,joborder_id,site_id,entered_by,date_created,type,notes,date_modified) values (NULL,'".$id."','100','".$options['joborder_id']."','1','1250','".date( 'Y-m-d H:i:s')."','400','User applied through candidate portal','".date( 'Y-m-d H:i:s')."')");
		$this->CatsDb->query();


		//Send mail to the manager job is to inform new people apply
		//fetch recruiter
		$this->CatsDb->setQuery("Select recruiter,title from joborder where joborder_id = '".$options['joborder_id']."'");
		$r = $this->CatsDb->loadObjectList();
		$r = $r[0];
		$this->CatsDb->setQuery("Select * from user where user_id = '".$r->recruiter."'");
		$userRow = $this->CatsDb->loadObjectList();
		$userRow = $userRow[0];
		
		$htmlMessage = "<html><head><title>Recruiter Notification</title></head><body>";
		$htmlMessage.="<table cellpadding=0 cellspacing=0 width=100% style='border:1px solid #cccccc;padding:20px;'>";
		$htmlMessage.="<tr><td width=100%>";
		$htmlMessage.="<b>Dear ".$userRow->last_name."</b>";
		$htmlMessage.="<br>This e-mail is a confirmation that new candidates have applied to your vacancy through our online recruitment system ..";
		$htmlMessage.="<br><Br><b>Vacancy name:</b>&nbsp;".$r->title;
		$htmlMessage.="<br><b>Applicant Name :&nbsp;</b>".$options['firstname']." ".$options['lastname'].".";
		$htmlMessage.="<br><b>Candidate URL :&nbsp;</b> <a href='".$this->Cats_install."index.php?m=candidates&a=show&candidateID=".$id."'>".$this->Cats_install."index.php?m=candidates&a=show&candidateID=".$id."</a>.";
		$htmlMessage.="<br><b>Job Order URL :&nbsp;</b> <a href='".$this->Cats_install."index.php?m=joborders&a=show&jobOrderID=".$options['joborder_id']."'>".$this->Cats_install."index.php?m=joborders&a=show&jobOrderID=".$options['joborder_id']."</a>."		;
		$htmlMessage.="<br><br>Administrator<br>Recruiter notification.";
		$htmlMessage.="</td></tr></table></body></html>";
		
		//Gui mail
		JUtility::sendMail($this->email,'Recruiter notification',$userRow->email,'Administrator - Candidate applied to an OpenCATS vacancy.',$htmlMessage,1);


		$this->CatsDb->setQuery("Select questionnaire_id from joborder where joborder_id = '".$options['joborder_id']."'");
		$question =  $this->CatsDb->loadResult();
		$q['question'] = $question;
		$q['id'] = $id;
		return $q;
	}
	function getCatsone( $options )
	{
		$query = "Select joborder.*,extra_field.*,user.user_id,user.last_name,company.company_id,company.name from joborder,extra_field,user,company where extra_field.data_item_id = joborder.joborder_id and joborder.status = 'active' and joborder.entered_by = user.user_id  and joborder.company_id = company.company_id ";
		if($options['jobType']!="")
		{
			$query.=" and extra_field.value like '".$options['jobType']."'";
		}
		$this->CatsDb->setQuery($query,@$options['limitstart'], @$options['limit']);
	
		$result = $this->CatsDb->loadObjectList();
	
		return $result;
	}
}