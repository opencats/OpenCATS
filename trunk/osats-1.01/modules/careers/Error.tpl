<?php /* $Id: Error.tpl 1329 2006-12-22 09:22:41Z will $ */ ?>
<html>
    <body>
    <table>
        <tr>
            <td><h2>Careers: Error</h2></td>
        </tr>
    </table>

    <p class="fatalError">
        A fatal error has occurred.<br />
        <br />
        Error: <?php echo($this->errorMessage); ?>
    </p>
    </body>
</html>

