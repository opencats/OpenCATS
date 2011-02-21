<div class="stepContainer">
    <div class="infoBar">
        <table cellpadding="0" cellspacing="0" border="0" width="100%">
            <tr>
                <td align="left" valign="middle" class="infoBarText">
                    Review
                </td>
                <td align="right" valign="middle" class="infoFileText">
                    <b><?php echo LicenseUtility::isParsingEnabled() ? 'Parsed and ' : ''; ?>Ready to Import</b> <?php echo number_format($cnt = count($this->documents), 0); ?>
                    resume document<?php echo $cnt != 1 ? 's' : ''; ?>
                </td>
            </tr>
        </table>
    </div>

    <div class="resultsScroller">
        <table cellpadding="0" cellspacing="0" border="0" width="100%">
            <tr>
                <td class="dataColumnTitle" width="30%">File</td>
                <td class="dataColumnTitle" width="20%">Name</td>
                <td class="dataColumnTitle" width="23%">Location</td>
                <td class="dataColumnTitle" width="27%">E-mail</td>
                <td align="middle" width="16" style="background-color: #DDECFE;"><img src="images/scrollTop.jpg" border="0" width="16" height="16" onmousedown="startGridScrollUp();" onmouseup="endScrolling();" style="cursor: pointer;" /></td>
            </tr>
        <?php for($row=0; $row<8; $row++): ?>
            <tr>
                <?php for($col=0; $col<4; $col++): ?>
                <td id="grid_row_<?php echo $row; ?>_column_<?php echo $col; ?>" class="dataColumnEven">&nbsp;</td>
                <?php endfor; ?>
                <?php if ($row == 7): ?>
                <td width="16" align="middle" style="background-color: #DDECFE;"><img src="images/scrollBottom.jpg" border="0" width="16" height="16" onmousedown="startGridScrollDown();" style="cursor: pointer;" onmouseup="endScrolling();" /></td>
                <?php else: ?>
                <td width="16" style="background-color: #DDECFE;">&nbsp;</td>
                <?php endif; ?>
            </tr>
        <?php endfor; ?>
        </table>
    </div>

    <div style="font-size: 14px;">
        <?php if (LicenseUtility::isParsingEnabled()): ?>
            CATS has attempted to extract relevant information like contact data, education and skill sets automatically.
            This process is <b>not</b> an exact science and can sometimes guess incorrectly (or not at all). Click on a
            row above to find out more information.
            <br />
            <b>Please review these results carefully and make any necessary changes before importing them!</b>
        <?php else: ?>
            By default, these documents will be imported as <u>searchable</u> resume files and <b>not</b> as candidates.
            <br /><br />
            <b>To import candidates: </b>Click on a document and manually enter information like name and
            e-mail address. The document will then be imported as a candidate.
        <?php endif; ?>
    </div>

    <div style="text-align: right; margin-top: 20px;">
        <input type="button" name="nextStep" id="nextStep" onclick="goStep4();" value="Import <?php if (LicenseUtility::isParsingEnabled()): ?>Candidates<?php else: ?>Resumes<?php endif; ?> ->" style="cursor: pointer;" />
    </div>

    <?php
    for ($i=0; $i<($rows = count($this->documents)); $i++)
    {
        $doc = $this->documents[$i];
        ?>
        <span id="data_<?php echo $i; ?>_column_0" class="hiddenDataColumn">
            <a href="?m=import&a=massImportEdit&documentID=<?php echo $doc['id']; ?>">
            <i><?php echo strlimit($doc['realName'], 25); ?></i> <font style="font-size: 11px;">(<?php echo number_format(@filesize($doc['name'])/1024); ?> KB)</font>
            </a>
        </span>
        <span id="data_<?php echo $i; ?>_column_1" class="hiddenDataColumn"><?php echo isset($doc['firstName']) ? strlimit($doc['firstName'], 10) : ''; ?> <?php echo isset($doc['lastName']) ? strlimit($doc['lastName'], 10) : '&nbsp;'; ?>&nbsp;</span>
        <span id="data_<?php echo $i; ?>_column_2" class="hiddenDataColumn"><?php echo isset($doc['city']) ? strlimit($doc['city'], 10) : ''; ?><?php echo (isset($doc['state']) && strlen($doc['state']) > 0) ? ', ' . strlimit($doc['state'], 5) : ''; ?><?php echo isset($doc['zipCode']) ? '  ' . strlimit($doc['zipCode'], 5) : '&nbsp;'; ?>&nbsp;</span>
        <span id="data_<?php echo $i; ?>_column_3" class="hiddenDataColumn"><?php echo isset($doc['email']) ? strlimit($doc['email'], 20) : '&nbsp;'; ?></span>
        <?php
    }
    ?>
</div>

<script>
var totalRows = <?php echo $rows; ?>;
gridBrowse();
</script>

<?php
function strlimit($txt, $sz)
{
    if (strlen($txt) <= $sz) return $txt;
    else return substr($txt, 0, $sz) . '<span color="#c0c0c0;">...</span>';
}
?>
