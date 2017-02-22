    <div class="footerBlock">
      <p id="footerText"><?php print oc_version() ?> <span id="toolbarVersion"></span>Powered by <a href="http://www.catsone.com/"><strong>CATS</strong></a>.</p>
      <span id="footerResponse"><?php print oc_response_time() ?></span><br />
      <span id="footerCopyright"><?php print oc_copyright() ?></span>
      <?php if (!eval(Hooks::get('TEMPLATEUTILITY_SHOWPRIVACYPOLICY'))) return; ?>
    </div>
    <?php eval(Hooks::get('TEMPLATE_UTILITY_PRINT_FOOTER')); ?>
  </body>
</html>
<?php
        if ((!file_exists('modules/asp') || (defined('CATS_TEST_MODE') && CATS_TEST_MODE)) && LicenseUtility::isProfessional() && !rand(0,10))
        {
            if (!LicenseUtility::validateProfessionalKey(LICENSE_KEY))
            {
                CATSUtility::changeConfigSetting('LICENSE_KEY', "''");
            }
        }
?>