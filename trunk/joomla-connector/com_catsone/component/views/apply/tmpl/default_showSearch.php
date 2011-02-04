<?php // no direct access
defined('_JEXEC') or die('Restricted access'); ?>
<form action="index.php?option=com_row" method="post" name="adminForm">
<table cellpadding=0 cellspacing=0 width=100%>
  <tr>
    <td width=100%>
	  <center>
	    <h2>
		 <b> Search results : <?=$this->keyword?></b>
		</h2>
	  </center>
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
   <tr>
      <td width=100% style='padding:10px;'>
         <table cellpadding=0 cellspacing=0 width=100% style='border:1px solid #efefef;padding:10px;'>
           <tr>
             <td width=5% style='text-align:center;font-weight:bold;border-right:1px solid white;height:25px;' bgcolor='#efefef'>
             	<b>#</b>
             </td>
             <td style='text-align:center;border-right:1px solid white;' bgcolor='#efefef'>
                 <b>Position</b>
             </td>
             <td style='text-align:center;border-right:1px solid white;' bgcolor='#efefef'>
                 <b>Start Date</b>
             </td>
             <td style='text-align:center;border-right:1px solid white;' bgcolor='#efefef'>
                 <b>Location</b>
             </td>      
             <td style='text-align:center;border-right:1px solid white;' bgcolor='#efefef'>
                 <b>City</b>
             </td>  
             <td style='text-align:center;border-right:1px solid white;' bgcolor='#efefef'>
                 <b>Job Type</b>
             </td>  			 
             <td style='text-align:center;' bgcolor='#efefef' width=7%>
                 <b>Apply</b>
             </td>         
           </tr>
           <?php
           $row = $this->row;
           for($i=0;$i<count($row);$i++)
           {
           	if($i % 2 ==0)
           	{
           		$bgcolor = "white";
           	}
           	else {
           		$bgcolor = "#eeeeee";
           	}
           	?>
           	  <tr>
           	    <td bgcolor='<?=$bgcolor?>' height=20 style='border-right:1px solid white;'>
           	      <center>
           	       <?=$i+1?>
           	      </center>
           	    </td>
           	    <td bgcolor='<?=$bgcolor?>' style='padding-left:10px;border-right:1px solid white;'>
           	      <a href='<? echo Jroute::_('index.php?option=com_catsone&task=details&id='.$row[$i]->joborder_id);?>'><?=$row[$i]->title?></a>
           	    </td>
           	    <td bgcolor='<?=$bgcolor?>' style='padding-left:20px;border-right:1px solid white;'>
           	      <?=$row[$i]->start_date?>
           	    </td>
           	    <td bgcolor='<?=$bgcolor?>' style='text-align:center;border-right:1px solid white;'>
           	      <?=$row[$i]->city?>
           	    </td>
           	    <td bgcolor='<?=$bgcolor?>' style='text-align:center;border-right:1px solid white;'>
           	      <?=$row[$i]->state?>
           	    </td>
				<td bgcolor='<?=$bgcolor?>' style='text-align:center;border-right:1px solid white;'>
					 <?php
									if($row[$i]->type=="H")
									{
										echo "Permanent";
									}
									elseif($row[$i]->type=="C2H")
									{
										echo "Contract to Hire";
									}
									elseif($row[$i]->type=="C")
									{
										echo "Contract";
									}
									elseif($row[$i]->type=="FL")
									{
										echo "Freelancer";
									}
								  ?>
           	    </td>
           	    <td bgcolor='<?=$bgcolor?>' style='text-align:center;'>
           	  	  <center><a href='index.php?option=com_row&task=apply&id=<?=$row[$i]->joborder_id?>'>Apply</a></center>
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
<input type="hidden" name="option" value="com_row" />
<input type="hidden" name="keyword" value="<?php echo $this->keyword;?>" />
<input type="hidden" name="task" value="apply" />
<input type="hidden" name="subpage" value="search" />
</form>