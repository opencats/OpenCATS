<?php /* $Id: ErrorModal.tpl 789 2006-09-08 07:41:24Z will $ */ ?>
<?php TemplateUtility::printModalHeader('Job Orders'); ?>
    <table>
        <tr>
            <td width="3%">
                <img src="images/job_orders.gif" width="24" height="24" border="0" alt="Job Orders" style="margin-top: 3px;" />&nbsp;
            </td>
            <td><h2>Job Orders: Error</h2></td>
       </tr>
    </table>

    <p class="fatalError">
        A fatal error has occurred.<br />
        <br />
        <?php echo($this->errorMessage); ?>
    </p>
    </body>
</html>

