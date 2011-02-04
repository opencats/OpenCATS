<?php

/**

 * $Id: default.php 10094 2008-03-02 04:35:10Z instance $

 */

defined( '_JEXEC' ) or die( 'Restricted access' );

?>

<link rel="stylesheet" href="components/com_catsone/views/catsone/tmpl/style/style.css" type="text/css" />



<form action="<?php echo JRoute::_('index.php?option=com_catsone', false); ?>" method="post" name="adminForm">

<table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">

	<thead>

	</thead>

<table cellpadding=0 cellspacing=0 width=100%>

   <tr>

     <td width=100% class="catsone_title">



          List of jobs follow :



     </td>

   </tr>

   <tr>

			<td align="right" colspan="6">

			<?php

				echo JText::_('Display Num') .'&nbsp;';

				echo $this->pagination->getLimitBox();

		    ?>

			</td>

		</tr>

		<?

			$jobType = Jrequest::getVar('jobType');

			if($jobType!="")

			{

				$url = "index.php?option=com_catsone&jobType=".$jobType;

			}

			else

			{

				$url = "index.php?option=com_catsone";

			}

		?>

   <tr>

      <td class="catsone_content">

         <table class="catsone_inner" cellpadding=0 cellspacing=0 >

           <tr>

             <td width=5% class="rank_title">

             	<b>#</b>

             </td>

             <td class="name_title">

                 <b>Name of job</b>

             </td>

             <td class="date_title">

                 <b>Start date</b>

             </td>

             <td class="city_title">

                 <a href='<?php echo JRoute::_($url."&orderby=city"); ?>'><b>City</b></a>

             </td>

             <td class="state_title">

                 <a href='<?php echo JRoute::_($url."&orderby=state"); ?>'><b>State</b></a>

             </td>

             <td class="type_title">

                 <b>Type</b>

             </td>

             <td class="apply_title" width=7%>

                 <b>Apply</b>

             </td>

           </tr>

           <?php

           $catsone = $this->catsone;

           for($i=0;$i<count($catsone);$i++)

           {

           $user =& JFactory::getUser();

			if($user->guest)

				$link = JRoute::_('index.php?option=com_user&view=login&return='.base64_encode('index.php?option=com_catsone&task=apply&id='.$catsone[$i]->joborder_id));

			else

				$link = JRoute::_('index.php?option=com_catsone&task=apply&id='.$catsone[$i]->joborder_id);



           	if($i % 2 ==0)

           	{

           		$bgcolor = "white";

           	}

           	else {

           		$bgcolor = "#eeeeee";

           	}

           	?>

           	  <tr class="sectiontableentry<?=$i%2+1;?>">

           	    <td class="catsone_rank" bgcolor='<?=$bgcolor?>'>

           	      <center>

           	       <?=$i+1?>

           	      </center>

           	    </td>

           	    <td class="catsone_name" bgcolor='<?=$bgcolor?>'>

           	      <a href='<?php echo JRoute::_( 'index.php?option=com_catsone&task=details&id='. $catsone[$i]->joborder_id ); ?>'><?=$catsone[$i]->title?></a>

           	    </td>

           	    <td class="catsone_date" bgcolor='<?=$bgcolor?>'>

           	      <?=$catsone[$i]->start_date?>

           	    </td>

           	    <td class="catsone_city" bgcolor='<?=$bgcolor?>'>

           	      <?=$catsone[$i]->city?>

           	    </td>

           	    <td class="catsone_state" bgcolor='<?=$bgcolor?>'>

           	      <?=$catsone[$i]->state?>

           	    </td>

				<td class="catsone_type" bgcolor='<?=$bgcolor?>'>

					 <?php

									if($catsone[$i]->type=="H")

									{

										echo "Permanent";

									}

									elseif($catsone[$i]->type=="C2H")

									{

										echo "Temp to Perm";

									}

									elseif($catsone[$i]->type=="C")

									{

										echo "Temp";

									}

									elseif($catsone[$i]->type=="FL")

									{

										echo "Freelancer";

									}

								  ?>

           	    </td>

           	    <td class="catsone_apply" bgcolor='<?=$bgcolor?>'>

           	  	  <center><a href="<?=$link?>">Apply</a></center>

           	    </td>

           	  </tr>

           	<?

           }

           ?>

         </table>

      </td>

   </tr>

		<tr>

			<td align="center" colspan="6" class="sectiontablefooter" height=20 width=100%>

				<?php

					echo $this->pagination->getPagesLinks();

				?>

			</td>

		</tr>

		<tr>

			<td colspan="6" align="right" height=20 width=100%>

				<?php echo $this->pagination->getPagesCounter(); ?>

			</td>

		</tr>

</table>

<input type="hidden" name="option" value="com_catsone" />

<input type="hidden" name="jobType" value="<?php echo $this->jobType;?>" />

</form>

