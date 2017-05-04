<?php /* $Id: ErrorModal.tpl 1889 2007-02-20 05:21:54Z will $ */ ?>
<?php TemplateUtility::printModalHeader(__('Companies')); ?>
    <table>
        <tr>
            <td width="3%">
                <img src="images/companies.gif" width="24" height="24" border="0" alt="<?php echo __("Companies");?>" style="margin-top: 3px;" />&nbsp;
            </td>
            <td><h2><?php echo __("Companies");?>: <?php echo __("Error");?></h2></td>
        </tr>
    </table>

    <p class="fatalError">
        <?php echo __("A fatal error has occurred.");?><br />
        <br />
        <?php echo($this->errorMessage); ?>
    </p>
    </body>
</html>

