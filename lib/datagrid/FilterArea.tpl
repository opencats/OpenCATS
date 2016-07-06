<script type="text/javascript">
	newFilterCounter<?=$this->md5InstanceName?> = 0;
    function showNewFilter<?=$this->md5InstanceName?>() {
    	newFilterCounter<?=$this->md5InstanceName?>++;
        showNewFilter(
            newFilterCounter<?=$this->md5InstanceName?>, 
            'filterResultsAreaTable<?=$this->md5InstanceName?>',
            '<?=$this->arrayKeysString?>',
            '<?=$this->md5InstanceName?>',
            'submitFilter<?=$this->md5InstanceName?>()'
        );
    };
</script>
</fieldset>

