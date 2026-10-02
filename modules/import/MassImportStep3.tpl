<div class="mt-3">
    <div class="d-flex flex-wrap justify-content-between gap-2 bg-body-tertiary rounded p-2">
        <h2 class="h6 mb-0">Review</h2>
        <span><b>Parsed and Ready to Import</b> <?php echo number_format($cnt = count($this->documents), 0); ?>
        resume document<?php echo $cnt != 1 ? 's' : ''; ?></span>
    </div>

    <div class="table-responsive my-3">
        <table class="table table-sm table-striped align-middle">
            <thead><tr>
                <th scope="col">File</th>
                <th scope="col">Name</th>
                <th scope="col">Location</th>
                <th scope="col">E-mail</th>
                <th scope="col"><button type="button" class="btn btn-sm btn-outline-secondary" aria-label="Scroll review up" onmousedown="startGridScrollUp();" onmouseup="endScrolling();" onmouseleave="endScrolling();" onkeydown="if (event.key === 'Enter' || event.key === ' ') startGridScrollUp();" onkeyup="endScrolling();">&uarr;</button></th>
            </tr></thead>
            <tbody>
        <?php for($row=0; $row<8; $row++): ?>
            <tr>
                <?php for($col=0; $col<4; $col++): ?>
                <td id="grid_row_<?php echo $row; ?>_column_<?php echo $col; ?>" class="dataColumnEven">&nbsp;</td>
                <?php endfor; ?>
                <?php if ($row == 7): ?>
                <td><button type="button" class="btn btn-sm btn-outline-secondary" aria-label="Scroll review down" onmousedown="startGridScrollDown();" onmouseup="endScrolling();" onmouseleave="endScrolling();" onkeydown="if (event.key === 'Enter' || event.key === ' ') startGridScrollDown();" onkeyup="endScrolling();">&darr;</button></td>
                <?php else: ?>
                <td>&nbsp;</td>
                <?php endif; ?>
            </tr>
        <?php endfor; ?>
            </tbody>
        </table>
    </div>

    <div class="alert alert-warning">
        CATS has attempted to extract relevant information like contact data, education and skill sets automatically.
        This process is <b>not</b> an exact science and can sometimes guess incorrectly (or not at all). Click on a
        row above to find out more information.
        <br />
        <b>Please review these results carefully and make any necessary changes before importing them!</b>
    </div>

    <div class="text-end mt-3">
        <input type="button" name="nextStep" id="nextStep" onclick="goStep4();" value="Import Candidates ->" class="btn btn-sm btn-primary" />
    </div>

    <?php
    for ($i=0; $i<($rows = count($this->documents)); $i++)
    {
        $doc = $this->documents[$i];
        ?>
        <span id="data_<?php echo $i; ?>_column_0" class="hiddenDataColumn">
            <a href="?m=import&a=massImportEdit&documentID=<?php echo $doc['id']; ?>">
            <i><?php echo strlimit($doc['realName'], 25); ?></i> <span class="small text-body-secondary">(<?php echo number_format(@filesize($doc['name'])/1024); ?> KB)</span>
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
