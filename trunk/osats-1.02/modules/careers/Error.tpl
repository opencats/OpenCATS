<?php /* $Id: Error.tpl 1329 2006-12-22 09:22:41Z will $ */ ?>
<html>
    <body>
    <table>
        <tr>
            <td><h2><?php _e('Careers');?>: <?php _e('Error');?></h2></td>
        </tr>
    </table>

    <p class="fatalError">
        <?php _e('A fatal error has occurred.');?><br />
        <br />
        <?php _e('Error');?>: <?php echo($this->errorMessage); ?>
    </p>
    </body>
</html>