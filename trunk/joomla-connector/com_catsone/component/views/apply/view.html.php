<?php

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.application.component.view');
class CatsoneViewapply extends JView
{
	var $CatsDb;
	var $ftp_host, $ftp_user, $ftp_password, $ftp_path, $Cats_local;

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
		$this->CatsDb =& JDatabase::getInstance( $optionDb );

		$this->ftp_host = $SETTINGS->ftp_host;
		$this->ftp_user = $SETTINGS->ftp_user;
		$this->ftp_password = $SETTINGS->ftp_password;
		$this->ftp_path = $SETTINGS->ftp_path;
		$this->Cats_local = $SETTINGS->Cats_local;
	}

	function display($tpl = null)
	{
		global $mainframe, $option;
		$user		= &JFactory::getUser();
		$db = JFactory::getDBO();
		$db->setQuery("Select * from #__name where user_id = '".$user->id."'");
		$row = $db->loadObjectList();
		$row = $row[0];
		//print_r($user);
		$pathway	= &$mainframe->getPathway();
		$document	= & JFactory::getDocument();
		//$model		= &$this->getModel();
		
		$job_id = Jrequest::getVar('id');
		if(!isset($job_id)){
			$job_id = Jrequest::getVar('catid');
		}

		// Get the parameters of the active menu item
		$menus	= &JSite::getMenu();
		$menu    = $menus->getActive();

		$pparams = &$mainframe->getParams('com_catsone');

		// Push a model into the view
		$modelDetails	= &$this->getModel( 'apply' );
		
		$options['joborder_id'] = $job_id;
		//$details = $modelDetails->getDetails($options);
		//print_r($details);
		?>
		<?
		if($user->id!="")
		{
			?>
<table cellpadding=0 cellspacing=0 width=100%>
	<tr>
		<td width=100% valign=top height=35><b> 
		<?

		$this->CatsDb->setQuery("Select * from joborder where joborder_id = '$job_id'");
		$r = $this->CatsDb->loadObjectList();
		$r = $r[0];
		?> Apply for this role: <?=$r->title?> </b></td>
		<tr>
			<form action='index.php?option=com_catsone'
				enctype="multipart/form-data" action="_URL_" method="post"
				id='uploadForm'><input type="hidden" name="MAX_FILE_SIZE"
				value="50000000">
			<tr>
				<td width=100% valign=top>
				<table cellpadding=0 cellspacing=0 width=100%>
					<tr>
						<td width=40% valign=top>
						<table cellpadding=0 cellspacing=0 width=100%
							style='border: 1px solid gray;' bgcolor='#efefef'>
							<tr>
								<td style='border-bottom: 1px solid gray; padding: 10px;'><b> 1.
								Attach CV (format: doc, pdf, txt) and fill the form </b></td>
							</tr>
							<tr>
								<td width=100% bgcolor='#f3f6f6' style='padding: 10px;'><input
									type='file' name='cv' size=32> <br>
								<br>
								<textarea name='cv_text' cols=33 rows=5></textarea></td>
							</tr>
							<tr>
								<td
									style='border-bottom: 1px solid gray; padding: 10px; border-top: 1px solid gray;'>
								<b> 2. Personal Data </b></td>
							</tr>
							<tr>
								<td width=100% bgcolor='#f3f6f6' style='padding: 10px;'><font
									color='gray'><i>All fields marked with (*) are mandatory.</i></font>
								<br>
								<table cellpadding=0 cellspacing=0 width=100%>
									<tr>
										<td width=40%
											style='padding-top: 5px; padding-bottom: 5px; text-align: right; padding-right: 10px;'>
										<b>*First Name:</b></td>
										<td width=60% style='padding-top: 5px; padding-bottom: 5px;'>
										<input name='firstname' size=25 id='firstname'
											value="<?=isset($row->firstname)? $row->firstname:''?>"></td>
									</tr>
									<tr>
										<td width=40%
											style='padding-top: 5px; padding-bottom: 5px; text-align: right; padding-right: 10px;'>
										<b>*Last Name:</b></td>
										<td width=60% style='padding-top: 5px; padding-bottom: 5px;'>
										<input name='lastname' size=25 id='lastname'
											value='<?=isset($row->lastname) ? $row->lastname:'' ?>'></td>
									</tr>
									<tr>
										<td width=40%
											style='padding-top: 5px; padding-bottom: 5px; text-align: right; padding-right: 10px;'>
										<b>*e-mail:</b></td>
										<td width=60% style='padding-top: 5px; padding-bottom: 5px;'>
										<input name='email_address' size=25 id='email_address'
											value="<?=isset($user->email) ? $user->email: '' ?>"></td>
									</tr>
									<tr>
										<td width=40%
											style='padding-top: 5px; padding-bottom: 5px; text-align: right; padding-right: 10px;'>
										<b>*Comfirm e-mail:</b></td>
										<td width=60% style='padding-top: 5px; padding-bottom: 5px;'>
										<input name='confirm_email_address' size=25
											id='confirm_email_address' value="<?=isset($user->email) ? $user->email: ''?>"></td>
									</tr>
								</table>
								</td>
							</tr>
						</table>

					</tr>
					<tr>
						<td width=100% valign=top>
						<table cellpadding=0 cellspacing=0 width=100%
							style='border: 1px solid gray;' bgcolor='#efefef'>
							<tr>
								<td style='border-bottom: 1px solid gray; padding: 10px;'><b> 3.
								Contact details: </b></td>
							</tr>
							<tr>
								<td width=100% bgcolor='#f3f6f6' style='padding: 10px;'>
								<table cellpadding=0 cellspacing=0 width=100%>
									<tr>
										<td width=40%
											style='padding-top: 5px; padding-bottom: 5px; text-align: right; padding-right: 10px;'>
										<b>Home telephone:</b></td>
										<td width=60% style='padding-top: 5px; padding-bottom: 5px;'>
										<input name='homephone' size=28></td>
									</tr>
									<tr>
										<td width=40%
											style='padding-top: 5px; padding-bottom: 5px; text-align: right; padding-right: 10px;'>
										<b>Mobile telephone:</b></td>
										<td width=60% style='padding-top: 5px; padding-bottom: 5px;'>
										<input name='mobilephone' size=28></td>
									</tr>
									<tr>
										<td width=40%
											style='padding-top: 5px; padding-bottom: 5px; text-align: right; padding-right: 10px;'>
										<b>Work telephone:</b></td>
										<td width=60% style='padding-top: 5px; padding-bottom: 5px;'>
										<input name='workphone' size=28></td>
									</tr>
									<tr>
										<td width=40%
											style='padding-top: 5px; padding-bottom: 5px; text-align: right; padding-right: 10px;'>
										<b>*Best time to call:</b></td>
										<td width=60% style='padding-top: 5px; padding-bottom: 5px;'>
										<input name='besttimetocall' size=28 id='besttimetocall'></td>
									</tr>
									<tr>
										<td width=40%
											style='padding-top: 5px; padding-bottom: 5px; text-align: right; padding-right: 10px;'>
										<b>Postal Address:</b></td>
										<td width=60% style='padding-top: 5px; padding-bottom: 5px;'>
										<textarea cols=25 rows=3 name='mailingaddress'></textarea></td>
									</tr>
									<tr>
										<td width=40%
											style='padding-top: 5px; padding-bottom: 5px; text-align: right; padding-right: 10px;'>
										<b>*City:</b></td>
										<td width=60% style='padding-top: 5px; padding-bottom: 5px;'>
										<input name='city' size=28 id='city'></td>
									</tr>
									<tr>
										<td width=40%
											style='padding-top: 5px; padding-bottom: 5px; text-align: right; padding-right: 10px;'>
										<b>*State:</b></td>
										<td width=60% style='padding-top: 5px; padding-bottom: 5px;'>
										<input name='state' size=28 id='state'></td>
									</tr>
									<tr>
										<td width=40%
											style='padding-top: 5px; padding-bottom: 5px; text-align: right; padding-right: 10px;'>
										<b>*Zipcode:</b></td>
										<td width=60% style='padding-top: 5px; padding-bottom: 5px;'>
										<input name='zip' size=28 id='zip'></td>
									</tr>
								</table>
								</td>
							</tr>
							<tr>
								<td
									style='border-bottom: 1px solid gray; padding: 10px; border-top: 1px solid gray;'>
								<b> 4. Additional Information </b></td>
							</tr>
							<tr>
								<td width=100% bgcolor='#f3f6f6' style='padding: 10px;'>
								<table cellpadding=0 cellspacing=0 width=100%>
									<tr>
										<td width=40%
											style='padding-top: 5px; padding-bottom: 5px; text-align: right; padding-right: 10px;'>
										<b>*Key skills (separated by commas):</b></td>
										<td width=60% style='padding-top: 5px; padding-bottom: 5px;'>
										<input name='skill' size=40 id='skill'></td>
									</tr>
									<tr>
										<td colspan=2>
										<center><input type='button' class='button'
											value='Submit Application now'
											onClick='javascript:validateForm();'></center>
										</td>
									</tr>
								</table>
								</td>
							</tr>
						</table>
						</td>
					</tr>
				</table>
				</td>
			</tr>

</table>
<input type='hidden' name='task' value='apply'>
<input type='hidden' name='subpage' value='save'>
<input
	type='hidden' name='user_id' value='<?=$user->id?>'>
<input
	type='hidden' name='joborder_id' value='<?=$options['joborder_id']?>'>
</form>
		<?
		}
		else {
			?>
<table cellpadding="0" cellspacing="0" width=100% height=50
	style='border: 1px solid gray;' bgcolor='#efefef'>
	<tr>
		<td width=100%>
		<center><b> <font color='red'><b>To submit an application you must be
		registered! <br>
		Click <a href='index.php?option=com_user&task=register'>here</a> to
		register, or click <a href='index.php'>here</a> to login. </b></font>
		</b></center>
		</td>
	</tr>
</table>
			<?
		}
		?>
<br>
<script language='javascript'>
		function validateForm()
		{
			var firstname=document.getElementById("firstname");
			var lastname = document.getElementById("lastname");
			var email_address = document.getElementById("email_address");
			var confirm_email_address = document.getElementById("confirm_email_address");
			var besttimetocall = document.getElementById("besttimetocall");
			var city = document.getElementById("city");
			var state = document.getElementById("state");
			var zip = document.getElementById("zip");
			var skill = document.getElementById("skill");
			var uploadForm = document.getElementById("uploadForm");
			if(firstname.value=="")
			{
				alert("Please enter first name");
				return false;
			}
			else if(lastname.value=="")
			{
				alert("Please enter last name");
				return false;
			}
			else if(email_address.value=="")
			{
				alert("Please enter email address");
				return false;
			}
			else if(confirm_email_address.value=="")
			{
				alert("Please enter confirm email address");
				return false;
			}
			else if(email_address.value!=confirm_email_address.value)
			{
				alert("Email address and confirm email address should be the same");
				return false;
			}
			else if(besttimetocall.value =="")
			{
				alert("Please enter time to call");return false;
			}
			else if(city.value=="")
			{
				alert("Please enter city");
				return false;
			}
			else if(state.value=="")
			{
				alert("Please enter state");
				return false;
			}
			else if(zip.value=="")
			{
				alert("Please enter zip");
				return false;
			}
			else if(skill.value=="")
			{
				alert("Please enter the skill");
				return false;
			}
			else
			{
				uploadForm.submit();
			}

		}
		</script>
		<?
		//	parent::display($tpl);
	}
	function displaySubpage($tpl=null)
	{
		global $mainframe, $option;
		$user_id = Jrequest::getVar('user_id');
		$user		= &JFactory::getUser($user_id);
		//print_r($user);
		$firstname = &Jrequest::getVar('firstname');
		$lastname = &Jrequest::getVar('lastname');
		$email_address = &Jrequest::getVar('email_address');
		$cv_text = &Jrequest::getVar('cv_text');
		$homephone = &Jrequest::getVar('homephone');
		$mobilephone = &Jrequest::getVar('mobilephone');
		$workphone = &Jrequest::getVar('workphone');
		$besttimetocall = &Jrequest::getVar('besttimetocall');
		$mailingaddress = &Jrequest::getVar('mailingaddress');
		$city = &Jrequest::getVar('city');
		$state = &Jrequest::getVar('state');
		$zip = &Jrequest::getVar('zip');
		$skill = &Jrequest::getVar('skill');
		$joborder_id = &Jrequest::getVar('joborder_id');
		/* Using new attachment class in models/apply.php.  Moving the ftp code to the attachment class.
		if(is_uploaded_file($_FILES['cv']['tmp_name']))
		{
			$file_name = md5(time()).$_FILES['cv']['name'];

			if($this->ftp_host != '' && $this->ftp_user = '' && $this->ftp_password){

				$conn_id = ftp_connect($this->ftp_host);
				$login_result = ftp_login($conn_id, $this->ftp_user, $this->ftp_password);
				ftp_put($conn_id, $file_name, $file, FTP_ASCII);
			}else{
				move_uploaded_file($_FILES['cv']['tmp_name'],$this->Cats_local.DS.$file_name);
			}
		}*/

		$options['firstname'] = $firstname;
		$options['lastname'] = $lastname;
		$options['email_address'] = $email_address;
		$options['cv_text'] = $cv_text;
		$options['homephone'] = $homephone;
		$options['mobilephone'] = $mobilephone;
		$options['workphone'] = $workphone;
		$options['besttimetocall'] = $besttimetocall;
		$options['mailingaddress'] = $mailingaddress;
		$options['city'] = $city;
		$options['state'] = $state;
		$options['zip'] = $zip;
		$options['skill'] = $skill;
		$options['file_name'] = $file_name;
		$options['joborder_id'] = $joborder_id;
		$modelApply	= &$this->getModel( 'apply' );
		$applyJob = $modelApply->applyJob($options);
		$question = $applyJob['question'];
		//Co cau hoi
		if($question!="")
		{
			//$user		= &JFactory::getUser();
			jimport('joomla.database.database');
			jimport( 'joomla.database.table' );
			$conf =& JFactory::getConfig();
			// TODO: Cache on the fingerprint of the arguments
			//$db			=& JFactory::getDBO();
			/*
			$host 		= $conf->getValue('config.host');
			$user 		= $conf->getValue('config.user');
			$password 	= $conf->getValue('config.password');
			$database	= $conf->getValue('config.db');
			$prefix 	= $conf->getValue('config.dbprefix');
			$driver 	= $conf->getValue('config.dbtype');
			$debug 		= $conf->getValue('config.debug');
			*/
			//$optionDb	= array ( 'driver' => $driver, 'host' => $host, 'user' => $user, 'password' => $password, 'database' => 'hronline_Vope302', 'prefix' => "" );
			/*$host 		= $conf->getValue('config.host');
			$driver 	= $conf->getValue('config.dbtype');
			$optionDb	= array ( 'driver' => $driver, 'host' => 'localhost', 'user' => 'xxxxxxxxxx', 'password' => 'xxxxxxxxxxx', 'database' => 'xxxxxxxxxxx', 'prefix' => "" );
			$db =& JDatabase::getInstance( $optionDb );*/
			//Tao moi cau tra loi
			//Tieu de cau hoi
			$sql = "Select * from career_portal_questionnaire where career_portal_questionnaire_id='".$question."'";
			$this->CatsDb->setQuery($sql);
			$r1 = $this->CatsDb->loadObjectList();
			$r1 = $r1[0];
			?>
<table cellpadding=0 cellspacing=0 width=100%
	style='border: 1px solid gray; padding: 20px;'>
	<tr>
		<td width=100%><?
		echo "<b>Question : ".$r1->title."</b>";
		?></td>
	</tr>
	<form method='post'
		action='index.php?option=com_catsone&task=saveQuestion'>
	<tr>
		<td width=100%><?
		$sql = "Select * from career_portal_questionnaire_question where career_portal_questionnaire_id='".$question."'";
		$this->CatsDb->setQuery($sql);
		$r2 = $this->CatsDb->loadObjectList();
		$temp = "";
		for($i=0;$i<count($r2);$i++)
		{

			$order = $i + 1;
			echo "<br><b>".$order." /</b>";
			echo $r2[$i]->text;
			echo "&nbsp;&nbsp;&nbsp;";
			//1 : text box
			//2 : select box
			//3 : check box
			//4 : radio button
			//Thu tu ten cua cac the la
			// tencuachude_tencauhoi_phuongantraloi
			$sqlTemp  =  "Select * from career_portal_questionnaire_answer where career_portal_questionnaire_question_id='".$r2[$i]->career_portal_questionnaire_question_id."'";
			//echo $sqlTemp;
			echo "<br>";
			$this->CatsDb->setQuery($sqlTemp);
			$r3 = $this->CatsDb->loadObjectList();

			if($r2[$i]->type=="1")
			{
				$r3 = $r3[0];
				?> <textarea
			name='<?=$question."_".$r2[$i]->career_portal_questionnaire_question_id?>'
			cols=30 rows=3></textarea> <?
			$temp.= $question."_".$r2[$i]->career_portal_questionnaire_question_id." | ";
			}
			elseif($r2[$i]->type=="2")
			{
				$temp.= $question."_".$r2[$i]->career_portal_questionnaire_question_id." | ";
				$t = "<select name='".$question."_".$r2[$i]->career_portal_questionnaire_question_id."'>";
				for($j=0;$j<count($r3);$j++)
				{
					$t.= "<option value='".$r3[$j]->text."'>".$r3[$j]->text."</option>";
				}
				$t .= "</select>";
				echo $t;
			}
			elseif($r2[$i]->type=="3")
			{
				for($j=0;$j<count($r3);$j++)
				{
					$temp.= $question."_".$r2[$i]->career_portal_questionnaire_question_id."_".$r3[$j]->career_portal_questionnaire_answer_id." | ";
					?> <input type='checkbox'
			name='<?=$question."_".$r2[$i]->career_portal_questionnaire_question_id."_".$r3[$j]->career_portal_questionnaire_answer_id?>'
			id='<?=$question."_".$r2[$i]->career_portal_questionnaire_question_id."_".$r3[$j]->career_portal_questionnaire_answer_id?>'
			value=''
			onClick='javascript:updateCheckbox("<?=$r3[$j]->text?>","<?=$question."_".$r2[$i]->career_portal_questionnaire_question_id."_".$r3[$j]->career_portal_questionnaire_answer_id?>")'>
		&nbsp; <?=$r3[$j]->text?> <br>
		<?
				}
			}
			elseif($r2[$i]->type=="4")
			{
				$temp.= $question."_".$r2[$i]->career_portal_questionnaire_question_id." | ";
				for($j=0;$j<count($r3);$j++)
				{
					?> <input type='radio'
			name='<?=$question."_".$r2[$i]->career_portal_questionnaire_question_id?>'
			value='<?=$r3[$j]->text?>'> &nbsp;<?=$r3[$j]->text?> <br>
			<?
				}
			}
		}
		?> <input type='hidden' name='task' value='apply'> <input
			type='hidden' name='subpage' value='saveAnswer'> <input type='submit'
			value='Submit'> <input type='hidden' name='joborder_id'
			value='<?=$options['joborder_id']?>'> <input type='hidden'
			name='answer' value='<?=$temp?>'> <input type='hidden'
			name='candicate' value='<?=$applyJob['id']?>'> <input type='hidden'
			name='questionnaire_title' value='<?=$r1->title?>'> <input
			type='hidden' name='questionnaire_description'
			value='<?=$r1->description?>'>

	</form>
	<script language='javascript'>
					function updateCheckbox(str,id)
					{
						var temp = document.getElementById(id);
						if(temp!=null)
						{
							if(temp.value=="")
							{
								temp.value = str;
							}
							else
							{
								temp.value = "";
							}
						}
					}
					</script>
	</td>
	</tr>
</table>
		<?php
		}else
		{
			global $mainframe;
			$mainframe->redirect("index.php?option=com_catsone&u=".$user->id,"Your application has been sent");
		}

	}
	function saveAnswer()
	{
		jimport('joomla.database.database');
		jimport( 'joomla.database.table' );
		$conf =& JFactory::getConfig();
		/*
		 $host 		= $conf->getValue('config.host');
		 $username 		= $conf->getValue('config.user');
		 $password 	= $conf->getValue('config.password');
		 $database	= $conf->getValue('config.db');
		 $prefix 	= $conf->getValue('config.dbprefix');
		 $driver 	= $conf->getValue('config.dbtype');
		 $debug 		= $conf->getValue('config.debug');
		 $optionDb	= array ( 'driver' => $driver, 'host' => $host, 'user' => $username, 'password' => $password, 'database' => 'hronline_Vope302', 'prefix' => "" );
		 */
		/*$host 		= $conf->getValue('config.host');
		$driver 	= $conf->getValue('config.dbtype');

		$optionDb	= array ( 'driver' => $driver, 'host' => 'localhost', 'user' => 'xxxxxxxxxx', 'password' => 'xxxxxxxxxxx', 'database' => 'xxxxxxxxxxx', 'prefix' => "");
		$db =& JDatabase::getInstance( $optionDb );*/
		$answer = Jrequest::getVar('answer');
		$candicate = Jrequest::getVar('candicate');
		$joborder_id = Jrequest::getVar('joborder_id');
		$questionnaire_title = Jrequest::getVar('questionnaire_title');
		$questionnaire_description = Jrequest::getVar('questionnaire_description');

		$a = array();
		$a = explode("|",$answer);
		for($i=0;$i<count($a);$i++)
		{
			$temp = Jrequest::getVar(trim($a[$i]));
			if($temp!="")
			{
				$b = explode("_",$a[$i]);
				//Question $b[0];
				//Cau hoi $b[1];
				//Phuong an tra loi $b[2] (Co the co hay khong);
				//Xu ly phuong an tra loi
				$question_type = $b[0];
				$question = $b[1];
				$this->CatsDb->setQuery("Select * from career_portal_questionnaire_question where career_portal_questionnaire_question_id='".$question."'");
				$row = $this->CatsDb->loadObjectList();
				$row = $row[0];
				$this->CatsDb->setQuery("Insert into career_portal_questionnaire_history(career_portal_questionnaire_history_id,site_id,candidate_id,question,answer,questionnaire_title,questionnaire_description,date) values (NULL,'1','".$candicate."','".$row->text."','".$temp."','".$questionnaire_title."','".$questionnaire_description."','".date( 'Y-m-d H:i:s' )."')");
				$this->CatsDb->query();
			}
		}
		global $mainframe;
		$mainframe->redirect("index.php?option=com_catsone","Your application has been sent");
	}
	function search()
	{
		global $mainframe;
		jimport('joomla.database.database');
		jimport( 'joomla.database.table' );
		$conf =& JFactory::getConfig();
		/*
		 $host 		= $conf->getValue('config.host');
		 $user 		= $conf->getValue('config.user');
		 $password 	= $conf->getValue('config.password');
		 $database	= $conf->getValue('config.db');
		 $prefix 	= $conf->getValue('config.dbprefix');
		 $driver 	= $conf->getValue('config.dbtype');
		 $debug 		= $conf->getValue('config.debug');
		 $optionDb	= array ( 'driver' => $driver, 'host' => $host, 'user' => $user, 'password' => $password, 'database' => 'hronline_Vope302', 'prefix' => "" );
		 */
		/*$host 		= $conf->getValue('config.host');
		$driver 	= $conf->getValue('config.dbtype');

		$optionDb	= array ( 'driver' => $driver, 'host' => 'localhost', 'user' => 'xxxxxxxxxx', 'password' => 'xxxxxxxxxxx', 'database' => 'xxxxxxxxxxx', 'prefix' => "" );
		$db =& JDatabase::getInstance( $optionDb );*/
		$keyword = Jrequest::getVar('keyword');
		if($keyword!="")
		{
			$query = "Select joborder.*,extra_field.*,user.user_id,user.last_name,company.company_id,company.name from joborder,extra_field,user,company where extra_field.data_item_id = joborder.joborder_id and joborder.status = 'active' and joborder.entered_by = user.user_id  and joborder.company_id = company.company_id and joborder.public = '1' and joborder.title like '%".$keyword."%'";
		}
		$this->CatsDb->setQuery($query);
		$total = count($this->CatsDb->loadObjectList());
		$limit				= JRequest::getVar('limit',				$mainframe->getCfg('list_limit'),	'', 'int');
		$limitstart			= JRequest::getVar('limitstart',		0,				'', 'int');
		$options['limit'] = $limit;
		$options['limitstart'] = $limitstart;
		$this->CatsDb->setQuery($query,$options['limitstart'], $options['limit']);
		$row = $this->CatsDb->loadObjectList();
		$pathway	= &$mainframe->getPathway();
		jimport('joomla.html.pagination');
		$pagination = new JPagination($total, $limitstart, $limit);
		$this->assignRef('keyword', $keyword);
		$this->assignRef('row',$row);
		$this->assignRef('pagination',	$pagination);
		parent::display("showSearch");
	}
}