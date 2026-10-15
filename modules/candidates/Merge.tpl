<?php
/*
 * OpenCATS
 *
 * Portions Copyright (C) 2005-2007 Cognizo Technologies, Inc.
 * Originally released as part of CATS Standard Edition under the
 * CATS Public License 1.1a.
 *
 * See LICENSE.md.
 */
 ?>
<?php TemplateUtility::printModalHeader('Candidates', array(), 'Select information to keep in merge duplicates'); ?>
<main class="container-fluid p-2 oc-candidate-merge">

    <?php if (!$this->isFinishedMode): ?>

            <form id="chooseMergeInformation" name="chooseMergeInformationForm" action="<?php echo(CATSUtility::getIndexName()); ?>?m=candidates&amp;a=mergeInfo" method="post">
                <input type="hidden" name="postback" value="postback">
                <input type="hidden" id="oldCandidateID" name="oldCandidateID" value="<?php echo $this->oldCandidateID; ?>">
                <input type="hidden" id="newCandidateID" name="newCandidateID" value="<?php echo $this->newCandidateID; ?>">

<div class="table-responsive"><table class="table table-sm table-striped align-middle"><thead>
                <tr>
                    <th scope="colgroup" colspan="2">Original candidate</th>
                    <th scope="colgroup" colspan="2">Duplicate candidate</th>
                </tr></thead><tbody>

                <tr>
                    <td colspan=4>First Name&nbsp;</td>
                </tr>
                <tr>
                    <td><?php echo($this->rsOld['firstName']); ?></td>
                    <td><input type="radio" name="firstName" value=0 class="form-check-input" aria-label="Keep original firstName"></td>
                    <td><input type="radio" name="firstName" value=1 checked class="form-check-input" aria-label="Keep duplicate firstName"></td>
                    <td><?php echo($this->rsNew['firstName']); ?></td>
                </tr>


                <tr>
                    <td colspan=4>Middle Name&nbsp;</td>
                </tr>
                <tr>
                    <td><?php echo($this->rsOld['middleName']); ?></td>
                    <td><input type="radio" name="middleName" value=0 class="form-check-input" aria-label="Keep original middleName"></td>
                    <td><input type="radio" name="middleName" value=1 checked class="form-check-input" aria-label="Keep duplicate middleName"></td>
                    <td><?php echo($this->rsNew['middleName']); ?></td>
                </tr>

                <tr>
                    <td colspan=4>Last Name&nbsp;</td>
                </tr>
                <tr>
                    <td><?php echo($this->rsOld['lastName']); ?></td>
                    <td><input type="radio" name="lastName" value=0 class="form-check-input" aria-label="Keep original lastName"></td>
                    <td><input type="radio" name="lastName" value=1 checked class="form-check-input" aria-label="Keep duplicate lastName"></td>
                    <td><?php echo($this->rsNew['lastName']); ?></td>
                </tr>


                <tr>
                    <td colspan=4>E-mails (max. 2)&nbsp;</td>
                </tr>
                <tr>
                    <td><?php echo($this->rsOld['email1'] == '') ?  '(none)' : ($this->rsOld['email1']);  ?></td>
                    <td><input type="checkbox" name="email[]" value="<?php echo ($this->rsOld['email1'] == '') ?  '' : ($this->rsOld['email1']); ?>" onclick="return keepCount('email')" class="form-check-input" aria-label="Keep original email"></td>
                    <td><input type="checkbox" name="email[]" value="<?php echo ($this->rsNew['email1'] == '') ?  '' : ($this->rsNew['email1']); ?>" onclick="return keepCount('email')" checked class="form-check-input" aria-label="Keep duplicate email"></td>
                    <td><?php echo($this->rsNew['email1'] == '') ?  '(none)' : ($this->rsNew['email1']);  ?></td>
                </tr>
                <tr>
                    <td><?php echo($this->rsOld['email2'] == '') ?  '(none)' : ($this->rsOld['email2']); ?></td>
                    <td><input type="checkbox" name="email[]" value="<?php echo ($this->rsOld['email2'] == '') ?  '' : ($this->rsOld['email2']); ?>" onclick="return keepCount('email')" class="form-check-input" aria-label="Keep original email"></td>
                    <td><input type="checkbox" name="email[]" value="<?php echo ($this->rsNew['email2'] == '') ?  '' : ($this->rsNew['email2']); ?>" onclick="return keepCount('email')" checked class="form-check-input" aria-label="Keep duplicate email"></td>
                    <td><?php echo($this->rsNew['email2'] == '') ?  '(none)' : ($this->rsNew['email2']); ?></td>
                </tr>
                <tr>
                    <td colspan=4>Cell phone&nbsp;</td>
                </tr>
                <tr>
                    <td><?php echo($this->rsOld['phoneCell'] == '') ? '(none)' : ($this->rsOld['phoneCell']); ?></td>
                    <td><input type="radio" name="phoneCell" value=0 class="form-check-input" aria-label="Keep original phoneCell"></td>
                    <td><input type="radio" name="phoneCell" value=1 checked class="form-check-input" aria-label="Keep duplicate phoneCell"></td>
                    <td><?php echo($this->rsNew['phoneCell'] == '') ? '(none)' : ($this->rsNew['phoneCell']); ?></td>
                </tr>

                <tr>
                    <td colspan=4>Home phone&nbsp;</td>
                </tr>
                <tr>
                    <td><?php echo($this->rsOld['phoneHome'] == '') ? '(none)' : ($this->rsOld['phoneHome']); ?></td>
                    <td><input type="radio" name="phoneHome" value=0 class="form-check-input" aria-label="Keep original phoneHome"></td>
                    <td><input type="radio" name="phoneHome" value=1 checked class="form-check-input" aria-label="Keep duplicate phoneHome"></td>
                    <td><?php echo($this->rsNew['phoneHome'] == '') ? '(none)' : ($this->rsNew['phoneHome']); ?></td>
                </tr>

                <tr>
                    <td colspan=4>Work phone&nbsp;</td>
                </tr>
                <tr>
                    <td><?php echo($this->rsOld['phoneWork'] == '') ? '(none)' : ($this->rsOld['phoneWork']); ?></td>
                    <td><input type="radio" name="phoneWork" value=0 class="form-check-input" aria-label="Keep original phoneWork"></td>
                    <td><input type="radio" name="phoneWork" value=1 checked class="form-check-input" aria-label="Keep duplicate phoneWork"></td>
                    <td><?php echo($this->rsNew['phoneWork'] == '') ? '(none)' : ($this->rsNew['phoneWork']); ?></td>
                </tr>

                <tr>
                    <td colspan=4>Website&nbsp;</td>
                </tr>
                <tr>
                    <td><?php echo($this->rsOld['webSite'] == '') ? '(none)' : ($this->rsOld['webSite']); ?></td>
                    <td><input type="radio" name="website" value=0 class="form-check-input" aria-label="Keep original website"></td>
                    <td><input type="radio" name="website" value=1 checked class="form-check-input" aria-label="Keep duplicate website"></td>
                    <td><?php echo($this->rsNew['webSite'] == '') ? '(none)' : ($this->rsNew['webSite']); ?></td>
                </tr>

                <tr>
                    <td colspan=4>Address&nbsp;</td>
                </tr>
                <tr>
                    <?php if($this->rsOld['address'] == "" && $this->rsOld['city'] == "" && $this->rsOld['state'] == "" && $this->rsOld['zip'] == ""): ?>
                        <td><?php echo "(none)"; ?></td>
                    <?php else: ?>
                        <td>
                            <?php echo($this->rsOld['address']); ?>
                            <?php if (!empty($this->rsOld['address2'])): ?><br><?php echo($this->rsOld['address2']); ?><?php endif; ?>
                            <br><?php echo($this->rsOld['city']." ".$this->rsOld['zip']); ?>
                            <br><?php echo($this->rsOld['state']); ?>
                        </td>
                    <?php endif; ?>
                    <td><input type="radio" name="address" value=0 class="form-check-input" aria-label="Keep original address"></td>
                    <td><input type="radio" name="address" value=1 checked class="form-check-input" aria-label="Keep duplicate address"></td>
                    <?php if($this->rsNew['address'] == "" && $this->rsNew['city'] == "" && $this->rsNew['state'] == "" && $this->rsNew['zip'] == ""): ?>
                        <td><?php echo "(none)";  ?></td>
                    <?php else: ?>
                        <td>
                            <?php echo($this->rsNew['address']); ?>
                            <?php if (!empty($this->rsNew['address2'])): ?><br><?php echo($this->rsNew['address2']); ?><?php endif; ?>
                            <br><?php echo($this->rsNew['city']." ".$this->rsNew['zip']); ?>
                            <br><?php echo($this->rsNew['state']); ?>
                        </td>
                    <?php endif; ?>
                </tr>

                <tr>
                    <td colspan=4><button type="submit" class="btn btn-sm btn-primary" id="mergeInfo" name="mergeInfo" value="Merge">Merge</button></td>
                </tr>

                <tr>
                    <td>&nbsp;</td>
                </tr>
</tbody></table></div>
            </form>
    <?php else: ?>
        <div class="alert alert-success py-2" role="alert">These candidates have been successfully merged.</div>

        <form method="get" action="<?php echo(CATSUtility::getIndexName()); ?>">
            <button type="button" name="close" value="Close" onclick="parentHidePopWinRefresh();" class="btn btn-sm btn-outline-secondary">Close</button>
        </form>
    <?php endif; ?>

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

</main>
    </body>
</html>
