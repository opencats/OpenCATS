        <?php if ($this->counterFilters > 0): ?>
            <br /><br />
        <?php endif; ?>
    </td>
    <td style="width:140px; vertical-align:top;">
        <input class="button" style="width:120px; margin-left:5px;" type="button" name="addFilterButton<?=$this->md5InstanceName?>" onclick="showNewFilter<?=$this->md5InstanceName?>();" value="Add New"  /><br />
        <input class="button" style="width:120px; margin-left:5px;" type="button" name="clearFilterButton<?=$this->md5InstanceName?>" onclick="clearFilter('filterArea<?=$this->md5InstanceName?>'); submitFilter<?=$this->md5InstanceName?>(false);" value="Remove All"  /><br />
        <input class="button" style="width:120px; margin-left:5px;" type="button" name="applyFilterButton<?=$this->md5InstanceName?>" onclick="submitFilter<?=$this->md5InstanceName?>();" value="Apply" />
    </td>
</tr>
</table>

<script type="text/javascript">
    newFilterCounter<?=$this->md5InstanceName?> = 0;
    function showNewFilter<?=$this->md5InstanceName?>() {
        newFilterCounter<?=$this->md5InstanceName?>++;
        showNewFilter(
            newFilterCounter<?=$this->md5InstanceName?>, 
            'filterResultsAreaTable<?=$this->md5InstanceName?>',
            <?=$this->arrayKeysString?>,
            '<?=$this->md5InstanceName?>'
        );
    };
</script>
</fieldset>

