<?php /* $Id: ErrorModal.tpl 789 2006-09-08 07:41:24Z will $ */ ?>
<?php TemplateUtility::printModalHeader(__('Candidates')); ?>
    <table>
        <tr>
            <td width="3%">
                <img src="images/candidate.gif" width="24" height="24" border="0" alt="Candidates" style="margin-top: 3px;" />&nbsp;
            </td>
            <td><h2><?php echo __("Candidates");?>: <?php echo __("Error");?></h2></td>
        </tr>
    </table>

    <p class="fatalError">
        <?php echo __("A fatal error has occurred.");?><br />
        <br />
        <?php echo($this->errorMessage); ?>
    </p>
    </body>
</html>

