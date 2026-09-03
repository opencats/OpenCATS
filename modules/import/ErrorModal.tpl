<?php TemplateUtility::printModalHeader('Import'); ?>
    <table>
        <tr>
            <td><h2>Import: Error</h2></td>
        </tr>
    </table>

    <p class="fatalError">
        A fatal error has occurred.<br />
        <br />
        <?php echo($this->errorMessage); ?>
    </p>
    </body>
</html>

