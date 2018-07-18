<?php 
/**
* Candidate merge duplicates template
* @package OpenCATS
* @subpackage modules/candidates
* @copyright (C) OpenCats
* @license GNU/GPL, see license.txt
* OpenCATS is free software; you can redistribute it and/or
* modify it under the terms of the GNU General Public License 2
* as published by the Free Software Foundation.
*/
 ?>
<?php TemplateUtility::printModalHeader('Candidates', array(), 'Select information to keep in merge duplicates'); ?>

    <?php if (!$this->isFinishedMode): ?>

        <table class="searchTable">
            <form id="chooseMergeInformation" name="chooseMergeInformationForm" action="<?php echo(CATSUtility::getIndexName()); ?>?m=candidates&amp;a=mergeInfo" method="post">
                <input type="hidden" id="oldCandidateID" name="oldCandidateID" value=<?php echo $this->oldCandidateID; ?> />
                <input type="hidden" id="newCandidateID" name="newCandidateID" value=<?php echo $this->newCandidateID; ?> />
                
                <tr>
                    <td colspan=2 align="right">Original candidate&nbsp;</td>
                    <td colspan=2 align="left">Duplicate candidate&nbsp;</td>
                </tr>
                
                <tr>
                    <td colspan=4 align="center">First Name&nbsp;</td>
                </tr>
                <tr>
                    <td align="right"><?php echo($this->rsOld['firstName']); ?></td>
                    <td align="center"><input type="radio" name="firstName" value=0 /></td>
                    <td align="center"><input type="radio" name="firstName" value=1 checked/></td>
                    <td align="left"><?php echo($this->rsNew['firstName']); ?></td>
                </tr>
                
                
                <tr>
                    <td colspan=4 align="center">Middle Name&nbsp;</td>
                </tr>
                <tr>
                    <td align="right"><?php echo($this->rsOld['middleName']); ?></td>
                    <td align="center"><input type="radio" name="middleName" value=0 /></td>
                    <td align="center"><input type="radio" name="middleName" value=1 checked/></td>
                    <td align="left"><?php echo($this->rsNew['middleName']); ?></td>
                </tr>
                
                <tr>
                    <td colspan=4 align="center">Last Name&nbsp;</td>
                </tr>
                <tr>
                    <td align="right"><?php echo($this->rsOld['lastName']); ?></td>
                    <td align="center"><input type="radio" name="lastName" value=0 /></td>
                    <td align="center"><input type="radio" name="lastName" value=1 checked/></td>
                    <td align="left"><?php echo($this->rsNew['lastName']); ?></td>
                </tr>
                
                
                <tr>
                    <td colspan=4 align="center">E-mails (max. 2)&nbsp;</td>
                </tr>
                <tr>
                    <td align="right"><?php echo($this->rsOld['email1'] == '') ?  '(none)' : ($this->rsOld['email1']);  ?></td>
                    <td align="center"><input type="checkbox" name="email[]" value="<?php echo ($this->rsOld['email1'] == '') ?  '' : ($this->rsOld['email1']); ?>" onclick="return keepCount('email')"/></td>
                    <td align="center"><input type="checkbox" name="email[]" value="<?php echo ($this->rsNew['email1'] == '') ?  '' : ($this->rsNew['email1']); ?>" onclick="return keepCount('email')" checked/></td>
                    <td align="left"><?php echo($this->rsNew['email1'] == '') ?  '(none)' : ($this->rsNew['email1']);  ?></td>
                </tr>
                <tr>
                    <td align="right"><?php echo($this->rsOld['email2'] == '') ?  '(none)' : ($this->rsOld['email2']); ?></td>
                    <td align="center"><input type="checkbox" name="email[]" value="<?php echo ($this->rsOld['email2'] == '') ?  '' : ($this->rsOld['email2']); ?>" onclick="return keepCount('email')"/></td>
                    <td align="center"><input type="checkbox" name="email[]" value="<?php echo ($this->rsNew['email2'] == '') ?  '' : ($this->rsNew['email2']); ?>" onclick="return keepCount('email')" checked/></td>
                    <td align="left"><?php echo($this->rsNew['email2'] == '') ?  '(none)' : ($this->rsNew['email2']); ?></td>
                </tr>
                <tr>
                    <td colspan=4 align="center">Cell phone&nbsp;</td>
                </tr>
                <tr>
                    <td align="right"><?php echo($this->rsOld['phoneCell'] == '') ? '(none)' : ($this->rsOld['phoneCell']); ?></td>
                    <td align="center"><input type="radio" name="phoneCell" value=0 /></td>
                    <td align="center"><input type="radio" name="phoneCell" value=1 checked/></td>
                    <td align="left"><?php echo($this->rsNew['phoneCell'] == '') ? '(none)' : ($this->rsNew['phoneCell']); ?></td>
                </tr>
                
                <tr>
                    <td colspan=4 align="center">Home phone&nbsp;</td>
                </tr>
                <tr>
                    <td align="right"><?php echo($this->rsOld['phoneHome'] == '') ? '(none)' : ($this->rsOld['phoneHome']); ?></td>
                    <td align="center"><input type="radio" name="phoneHome" value=0 /></td>
                    <td align="center"><input type="radio" name="phoneHome" value=1 checked/></td>
                    <td align="left"><?php echo($this->rsNew['phoneHome'] == '') ? '(none)' : ($this->rsNew['phoneHome']); ?></td>
                </tr>
                
                <tr>
                    <td colspan=4 align="center">Work phone&nbsp;</td>
                </tr>
                <tr>
                    <td align="right"><?php echo($this->rsOld['phoneWork'] == '') ? '(none)' : ($this->rsOld['phoneWork']); ?></td>
                    <td align="center"><input type="radio" name="phoneWork" value=0 /></td>
                    <td align="center"><input type="radio" name="phoneWork" value=1 checked/></td>
                    <td align="left"><?php echo($this->rsNew['phoneWork'] == '') ? '(none)' : ($this->rsNew['phoneWork']); ?></td>
                </tr>
                
                <tr>
                    <td colspan=4 align="center">Website&nbsp;</td>
                </tr>
                <tr>
                    <td align="right"><?php echo($this->rsOld['webSite'] == '') ? '(none)' : ($this->rsOld['webSite']); ?></td>
                    <td align="center"><input type="radio" name="website" value=0 /></td>
                    <td align="center"><input type="radio" name="website" value=1 checked/></td>
                    <td align="left"><?php echo($this->rsNew['webSite'] == '') ? '(none)' : ($this->rsNew['webSite']); ?></td>
                </tr>
                
                <tr>
                    <td colspan=4 align="center">Address&nbsp;</td>
                </tr>
                <tr>
                    <?php if($this->rsOld['address'] == "" && $this->rsOld['city'] == "" && $this->rsOld['state'] == "" && $this->rsOld['zip'] == ""): ?>
                        <td align="right"><?php echo "(none)"; ?></td>
                    <?php else: ?>
                        <td align="right"><?php echo($this->rsOld['address'].'<br/>'.$this->rsOld['city']." ".$this->rsOld['zip'].'<br/>'.$this->rsOld['state']); ?></td>
                    <?php endif; ?>
                    <td align="center"><input type="radio" name="address" value=0 /></td>
                    <td align="center"><input type="radio" name="address" value=1 checked/></td>
                    <?php if($this->rsNew['address'] == "" && $this->rsNew['city'] == "" && $this->rsNew['state'] == "" && $this->rsNew['zip'] == ""): ?>
                        <td align="left"><?php echo "(none)";  ?></td>
                    <?php else: ?>
                        <td align="left"><?php echo($this->rsNew['address'].'<br/>'.$this->rsNew['city']." ".$this->rsNew['zip'].'<br/>'.$this->rsNew['state']);  ?></td>
                    <?php endif; ?>
                </tr>
                
                <tr>
                    <td colspan=4 align="center"><input type="submit" class="button" id="mergeInfo" name="mergeInfo" value="Merge" /></td>
                </tr>
                
                <tr>
                    <td>&nbsp;</td>
                </tr>
            </form>
        </table>
    <?php else: ?>
        <p>These candidates have been successfully merged.</p>

        <form method="get" action="<?php echo(CATSUtility::getIndexName()); ?>">
            <input type="button" name="close" value="Close" onclick="parentHidePopWinRefresh();" />
        </form>
    <?php endif; ?>

    </body>
</html>
<script>
    function keepCount()
    {
        var checkboxes = document.getElementsByName('email[]');
        var count = 0;
        for(var i = 0; i < checkboxes.length; ++i)
        {
            if(checkboxes[i].checked)
                {
                    count++;
                }
        }
        if(count > 2){
            return false;
        }
        else{
            return true;
        }
    }
</script>
