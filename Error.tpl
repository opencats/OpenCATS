<?php TemplateUtility::printHeader('Fatal Error'); ?>
<?php TemplateUtility::printHeaderBlock(); ?>

<p />
<p class="fatalError">
    A fatal error has occurred.<br />
    <br />
    <?php $this->_($this->errorMessage); ?>
</p>

<?php TemplateUtility::printFooter(); ?>
