<?php
/**
 * @version		$Id: view.html.php 10206 2008-04-17 02:52:39Z instance $
 * @package		Joomla
 * @subpackage	Contact
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.application.component.view');

/**
 * @package		Joomla
 * @subpackage	Contacts
 */
class CatsoneViewdetails extends JView
{
	function display($tpl = null)
	{
		global $mainframe, $option;
		$user		= &JFactory::getUser();
		$pathway	= &$mainframe->getPathway();
		$document	= & JFactory::getDocument();
		$model		= &$this->getModel();

		// Get the parameters of the active menu item
		$menus	= &JSite::getMenu();
		$menu    = $menus->getActive();

		$pparams = &$mainframe->getParams('com_catsone');

		// Push a model into the view
		$modelDetails	= &$this->getModel( 'details' );
		$options['joborder_id'] = Jrequest::getVar('id');
		$details = $modelDetails->getDetails($options);
		//print_r($details);
		?>
		<table cellpadding=0 cellspacing=0 width=100%>
		  <tr>
		    <td width="100%" bgcolor='#efefef' height=30 style='border-bottom:1px solid gray;' colspan=2> 
		        <center>
		           <b>
		              Details of job
		           </b>
		        </center>
		    </td>
		  </tr>	
		  <tr>
		    <td width=100% style='padding-top:20px;' valign="top" style='border-right:1px solid white;'>
		      	<table cellpadding="0" cellspacing="0" width="100%" valign=top>
		      	  <tr>
		      	    <td width=50% valign=top>
		      	    	<table cellpadding=0 cellspacing=0 width=100% valign=top>
		      	    	  <tr>
		      	    	    <td width="100%" bgcolor='#efefef' height=25 style="padding-left:20px;">
		      	    	     <table celllpadding=0 cellspacing=0 width=100%>
		      	    	      <tr>
							   <td width=40%>
		      	    	    	<b>
		      	    	    	   Title : </b> 
		      	    	    	</b>
								</td>
								<td width=60%>
								  <? echo $details->titlejob; ?>
								</td>
							  </tr>
							  </table>
		      	    	    </td>
		      	    	    </tr>
						

							
							  <tr>
		      	    	    <td width="100%" bgcolor='white' height=25 style="padding-left:20px;">
		      	    	     <table celllpadding=0 cellspacing=0 width=100%>
		      	    	      <tr>
							   <td width=40%>
		      	    	    	<b>
		      	    	    	  Location : </b> 
		      	    	    	</b>
								</td>
								<td width=60%>
								 <?=$details->address?> <?=$details->scity?>,<?=$details->sstate?>

								</td>
							  </tr>
							  </table>
		      	    	   
		      	    	    </tr>
							  <tr>
		      	    	    <td width="100%" bgcolor='#efefef' height=25 style="padding-left:20px;">
		      	    	     <table celllpadding=0 cellspacing=0 width=100%>
		      	    	      <tr>
							   <td width=40%>
		      	    	    	<b>
		      	    	    	  Max rate : </b> 
		      	    	    	</b>
								</td>
								<td width=60%>
								  <?=$details->rate_max?>
								</td>
							  </tr>
							  </table>
		      	    	   
		      	    	    </tr>
							
							  <tr>
		      	    	    <td width="100%" bgcolor='white' height=25 style="padding-left:20px;">
		      	    	     <table celllpadding=0 cellspacing=0 width=100%>
		      	    	      <tr>
							   <td width=40%>
		      	    	    	<b>
		      	    	    	  Start date : </b> 
		      	    	    	</b>
								</td>
								<td width=60%>
								  <? 
								  
							  $temp = $details->start_date;
							  $a = explode(" ",$temp);
							  echo $a[0];
							  ?>
								</td>
							  </tr>
							  </table>
		      	    	   
		      	    	    </tr>
		      	    	</table>	
		      	    </td>
					<td width=50% valign=top>
		      	    	<table cellpadding=0 cellspacing=0 width=100% valign=top>
		      	    	  <tr>
		      	    	    <td width="100%" bgcolor='#efefef' height=25 style="padding-left:20px;">
		      	    	     <table celllpadding=0 cellspacing=0 width=100%>
		      	    	      <tr>
							   <td width=40%>
		      	    	    	<b>
		      	    	    	   Duration : </b> 
		      	    	    	</b>
								</td>
								<td width=60%>
								  <?=$details->duration?> 
								</td>
							  </tr>
							  </table>
		      	    	    </td>
		      	    	    </tr>
							
							  <tr>
		      	    	    <td width="100%" bgcolor='white' height=25 style="padding-left:20px;">
		      	    	     <table celllpadding=0 cellspacing=0 width=100%>
		      	    	      <tr>
							   <td width=40%>
		      	    	    	<b>
		      	    	    	  Type : </b> 
		      	    	    	</b>
								</td>
								<td width=60%>
								  <?php
									if($details->type=="H")
									{
										echo "Hire";
									}
									elseif($details->type=="C2H")
									{
										echo "Contact to hire";
									}
									elseif($details->type=="C")
									{
										echo "Contact";
									}
									elseif($details->type=="FL")
									{
										echo "Freelancer";
									}
								  ?>
								</td>
							  </tr>
							  </table></td></tr>
							
							  <tr>
		      	    	    <td width="100%" bgcolor='#efefef' height=25 style="padding-left:20px;">
		      	    	     <table celllpadding=0 cellspacing=0 width=100%>
		      	    	      <tr>
							   <td width=40%>
		      	    	    	<b>
		      	    	    	  Created : </b> 
		      	    	    	</b>
								</td>
								<td width=60%>
								  <?=$details->date_created?>
								</td>
							  </tr>
							  </table>
		      	    	   </td>
		      	    	    </tr>
							  <tr>
		      	    	    <td width="100%" bgcolor='white' height=25 style="padding-left:20px;border-bottom:1px solid #efefef;">
		      	    	     <table celllpadding=0 cellspacing=0 width=100%>
		      	    	      <tr>
							   <td width=40%>
		      	    	    	<b>
		      	    	    	  Salary : </b> 
		      	    	    	</b>
								</td>
								<td width=60%>
								  <?=$details->salary?>
								</td>
							  </tr>
							  </table>
		      	    	   
		      	    	    </tr>
		      	    	</table>	
		      	    </td>
		      	  </tr>
				  <tr>
				    <td colspan=2 style='border:1px solid #efefef;padding:20px;border-top:1px solid gray;border-bottom:1px solid gray;'>
					  <b>
					   Description:
					   </b>
					   <br>
					   <?		
							echo $details->description;		
					  ?>
					</td>
				  </tr>
				  <tr>
				    <td width=100% colspan=2 height=35>
						<center>
						  <a href='javascript:history.go(-1);'>Go back</a>   -  <a href='index.php?option=com_catsone&task=apply&id=<?=$details->joborder_id?>'>Apply job</a>
						</center>
					</td>
				  </tr>
		      	</table>
		    </td>
		  </tr>
		</table>
		<?
		

	//	parent::display($tpl);
	}

	function getItems()
	{

	}
}