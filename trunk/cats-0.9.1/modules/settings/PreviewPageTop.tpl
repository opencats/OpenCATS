<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=<?php echo(HTML_ENCODING); ?>">
        <style type="text/css" media="all">@import "main.css";</style>
        <style type="text/css" media="all">
            body
            {
                padding: 8px 18px 8px 18px;
                margin: 0px;
                font: normal normal normal 14px Verdana, Tahoma, sans-serif;
            }
        </style>
        <title>CATS - Page Preview</title>
    </head>

    <body>
        <div style="text-align: center;">
            <?php $this->_($this->previewMessage); ?>
            <br />
            <input type="button" class="button" value="Close Preview" style="position: relative; top: 6px;" onclick="parent.window.close();" />
        </div>
    </body>
</html>
