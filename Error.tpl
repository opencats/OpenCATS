<?php /* $Id: Error.tpl 770 2006-09-06 19:04:57Z will $ */ ?>
<?php TemplateUtility::printHeader('Fatal Error'); ?>
<?php TemplateUtility::printHeaderBlock(); ?>

<p />
<p class="fatalError">
    A fatal error has occurred.<br />
    <br />
    <?php echo($this->errorMessage); ?>
</p>

<?php TemplateUtility::printFooter(); ?>
