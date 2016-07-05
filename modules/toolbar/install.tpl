<?php /* $Id: install.tpl 3225 2007-10-17 23:26:21Z brian $ */ ?>
<?php TemplateUtility::printHeader('Install Toolbar'); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active); ?>
    <div id="main">
        <?php TemplateUtility::printQuickSearch(''); ?>
        <div id="contents" style="text-align:center;">
            <!-- CATS FIREFOX TOOLBAR INSTALLER -->
            <h2>
                <img src="images/search.gif" width="24" height="24" border="0" alt="Firefox Toolbar" style="margin-top: 3px;" />
                &nbsp;&nbsp;CATS Firefox Toolbar Install
            </h2>
            <br />
            <br />
            <span style="font-weight: bold; font-size:14px;">CATS Toolbar (Patent Pending) - Web based recruiting taken to the next level.</span>
            <br />
            <br />
            <a href="javascript:void(0);" onclick="tryInstall();" style="font-weight: bold;">
                <img src="images/toolbarPreview.jpg" alt="preview" style="border: none;" />
            </a>
            <br />
            <br />
            <div id="queryInstall">
                <!--<a href="javascript:void(0);" onclick="tryInstall();" style="font-weight: bold; font-size: 16px;">Install CATS Toolbar</a>-->
                <span style="font-weight: bold;">To upgrade CATS toolbar, you must first uninstall the existing toolbar and restart Firefox.  Press tools, Add-ons, pick CATS Toolbar, and press Uninstall.</span>
            </div>

            <div id="divInstalling" style="display:none;">
                CATS is trying to install the CATS Firefox Toolbar.<br />
                Please accept any warnings that Firefox displays while installing.<br />
                <br />
                <a href="javascript:void(0);" onclick="tryInstall();">Retry Installation</a>
            </div>

            <div id="badBrowser" style="display:none;">
                CATS Toolbar is designed for Mozilla Firefox. Please download Mozilla Firefox to use the CATS Toolbar.
                <br />
                <br />
                <a href="http://www.mozilla.com/en-US/firefox/features.html">
                    <img src="images/firefox-spread-btn-2.png" alt="Firefox 2" title="Firefox 2" style="border: none;" />
                </a>
                <br />
                <br />
                <a href="http://www.mozilla.com/en-US/firefox/features.html">Learn more about Mozilla Firefox</a>
            </div>

            <div id="toolbarInstalled" style="display:none;">
                <span style="font-weight: bold;">To upgrade CATS toolbar, you must first uninstall the existing toolbar and restart Firefox.  Press tools, Add-ons, pick CATS Toolbar, and press Uninstall.  Once uninstalled, you can find the new toolbar installer under the settings tab of CATS.</span>
            </div>

            <script type="text/javascript">
                function tryInstall()
                {
                    if (document.getElementById('badBrowser').style.display == '')
                    {
                        return;
                    }

                    document.getElementById('divInstalling').style.display = '';
                    document.getElementById('queryInstall').style.display = 'none';
                    xpi = new Object();
                    xpi['CATS ToolBar'] = 'modules/toolbar/catstoolbar.xpi';
                    setTimeout('InstallTrigger.install(xpi)', '1000');
                }

                var isFirefox = false;

                /* Browser Detection */
                if (navigator.userAgent.indexOf('Firefox') != -1)
                {
                    var versionIndex = navigator.userAgent.indexOf('Firefox') + 8
                    if (parseInt(navigator.userAgent.charAt(versionIndex)) >= 1)
                    {
                       isFirefox = true;
                    }
                }

                if (!isFirefox)
                {
                    document.getElementById('queryInstall').style.display = 'none';
                    document.getElementById('badBrowser').style.display = 'block';
                }
            </script>
        </div>
    </div>
<?php TemplateUtility::printFooter(); ?>
