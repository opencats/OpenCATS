<?php /* $Id: NoCookiesModal.tpl 1927 2007-02-22 06:03:24Z will $ */ ?>
<?php TemplateUtility::printModalHeader('Login'); ?>
        <div style="text-align: center;">
            <p style="background-image: url('images/orange_gradient.jpg'); background-repeat: repeat-x; padding: 4px; margin-top: 0px; margin-bottom: 8px; width: 100%; font: normal normal bold  12px/120% Verdana, Tahoma, sans-serif; color: #F6F6F6;">
                 CATS Warning
            </p>
            <br />
            <div style="font: normal normal 12px Arial, Tahoma, sans-serif">
                Cookies are not enabled on your browser.  <br />CATS requires cookies in order to login.<br />
                <br />
                Please enable cookies within your web <br />
                browser, then revisit the CATS page.
                <br />
            </div>
            <br />
            <br />
            <input type="button" class="button" value="Retry" onclick="parentGoToURL(parent.document.location.href);">
        </div>
    </body>
</html>
