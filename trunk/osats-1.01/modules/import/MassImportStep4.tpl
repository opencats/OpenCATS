<div class="stepContainer">
    <table cellpadding="0" cellspacing="0" width="100%">
        <tr>
            <?php if (LicenseUtility::isParsingEnabled()): ?>
            <td width="45%" valign="top">
                <span style="font-size: 18px; font-weight: bold;">
                <font color="green"><?php echo count($this->importedCandidates); ?></font> Candidate<?php echo count($this->importedCandidates) != 1 ? 's' : ''; ?> Imported
                </span>
                <p />
                A candidate has all applicable information (such as name, address, skill set) on file
                and can be added to pipelines and included in reports and actions. The uploaded resume
                documents are attached to the candidate record for later viewing and searches.
                <p />
                <b>Imported Candidates:</b>
                <table cellpadding="0" cellspacing="0" border="0" width="100%" style="background-color: #F8FAFF; padding: 10px; border: 1px solid #A5BAE9;">
                    <?php $col = false; for ($i=0; $i<count($this->importedCandidates) && $i<=10; $i++): $candidate = $this->importedCandidates[$i]; ?>
                    <tr>
                        <td nowrap="nowrap"<?php echo (($col = !$col) ? ' style="background-color: #EDF3FF;"' : ''); ?>>
                            <a href="<?php echo $candidate['url']; ?>"><?php echo $candidate['name']; ?></a> -
                            <?php echo $candidate['location']; ?>
                        </td>
                    </tr>
                    <?php if ($i == 10 && count($this->importedCandidates) > 10): ?>
                    <tr>
                        <td nowrap="nowrap"<?php echo (($col = !$col) ? ' style="background-color: #EDF3FF;"' : ''); ?>>
                            ... <span style="color: #666666; font-style: italic;"><?php echo number_format(count($this->importedCandidates)-10,0); ?> candidates not shown</span>
                        </td>
                    </tr>
                    <?php endif; ?>
                    <?php endfor; ?>
                </table>
                <?php if (count($this->importedDuplicates)): ?>
                <br />
                <span style="font-weight: bold; color: #800000;">
                <?php echo number_format(count($this->importedDuplicates), 0); ?> candidates were duplicates and not added.
                </span>
                <?php endif; ?>
            </td>
            <td width="10%">&nbsp;</td>
            <?php else: ?>
            <td colpan="2" width="1">&nbsp;</td>
            <?php endif; ?>
            <td <?php if (LicenseUtility::isParsingEnabled()): ?>width="45%" <?php endif; ?>valign="top">
                <span style="font-size: 18px; font-weight: bold;">
                <font color="blue"><?php echo count($this->importedDocuments); ?></font> Resume Document<?php echo count($this->importedDocuments) != 1 ? 's' : ''; ?> Saved
                </span>
                <p />
                A resume document is a file that cannot be converted into a candidate because it's missing key
                information (like the candidate's name). These files have been saved and are full-text searchable. They
                must be converted into candidates manually.
                <p />
                <b>Resume Documents:</b>
                <table cellpadding="0" cellspacing="0" border="0" width="100%" style="background-color: #F8FAFF; padding: 10px; border: 1px solid #A5BAE9;">
                    <?php $col = false; for ($i=0; $i<count($this->importedDocuments) && $i<=10; $i++): $document = $this->importedDocuments[$i]; ?>
                    <tr>
                        <td nowrap="nowrap"<?php echo (($col = !$col) ? ' style="background-color: #EDF3FF;"' : ''); ?>>
                            <?php echo $document['name']; ?>
                        </td>
                    </tr>
                    <?php if ($i == 10 && count($this->importedDocuments) > 10): ?>
                    <tr>
                        <td nowrap="nowrap"<?php echo (($col = !$col) ? ' style="background-color: #EDF3FF;"' : ''); ?>>
                            ... <span style="color: #666666; font-style: italic;"><?php echo number_format(count($this->importedDocuments)-10,0); ?> documents not shown</span>
                        </td>
                    </tr>
                    <?php endif; ?>
                    <?php endfor; ?>
                </table>
            </td>
        </tr>
        <tr>
            <td colspan="3" valign="top" style="padding-top: 20px;">
                <br /><br />
                <span style="font-size: 18px; font-weight: bold;">
                <font color="red"><?php echo count($this->importedFailed); ?></font> Document<?php echo count($this->importedFailed) != 1 ? 's' : ''; ?> Failed to be Imported
                </span>
                <p />
                A document fails to import because it's either corrupt or CATS doesn't know how to open it. You
                could try to convert these files to CATS-friendly formats like Microsoft Word, Adobe PDF or as
                plain text files using the appropriate application.
                <p />
                <b>Failed Documents:</b>
                <table cellpadding="0" cellspacing="0" border="0" width="100%" style="background-color: #F8FAFF; padding: 10px; border: 1px solid #A5BAE9;">
                    <?php $col = false; for ($i=0; $i<count($this->importedFailed); $i++): $failed = $this->importedFailed[$i]; ?>
                    <tr>
                        <td nowrap="nowrap"<?php echo (($col = !$col) ? ' style="background-color: #EDF3FF;"' : ''); ?>>
                            <?php echo $failed['name']; ?>
                        </td>
                    </tr>
                    <?php endfor; ?>
                </table>
            </td>
        </tr>
    </table>

</div>