<?php TemplateUtility::printModalHeader('Candidates'); ?>
    <table>
        <tr>
            <td width="3%">
                <img src="images/candidate.gif" width="24" height="24" border="0" alt="Candidates" style="margin-top: 3px;" />&nbsp;
            </td>
            <td><h2>Candidates: Error</h2></td>
        </tr>
    </table>

    <p class="fatalError">
        A fatal error has occurred.<br />
        <br />
        <?php echo($this->errorMessage); ?>
    </p>
    </body>
</html>

