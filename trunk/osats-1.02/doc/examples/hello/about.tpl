<?php /* OSATS ABOUT TAB */ ?>

<?php TemplateUtility::printHeader('Hello (Sample Module)', array('modules/hello/validator.js', 'modules/hello/hello.css')); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
    <?php /* Print the tab bar with the Hello tab selected. */ ?>
    <div id="header">
        <ul id="primary">
            <?php TemplateUtility::printTabs($this->active); ?>
        </ul>
    </div>

    <?php /* <div id="main"> is the main page area. */ ?>
    <div id="main">
        <?php /* Print the quick search / MRU bar. */ ?>
        <?php TemplateUtility::printQuickSearch(); ?>

        <?php /* <div id="contents"> is where the main content is contained. */ ?>
        <div id="contents">
            <?php /* <div id="contents"> is where the main content is contained. */ ?>
            <table>
                <tr>
                    <td><img src="images/home.gif" width="24" height="24" border="0" alt="house" style="margin-top: 3px;" />&nbsp;</td>
                    <td><h2>About OSATS</h2></td>
                </tr>
            </table>

            <p class="note">Open Source Applicant Tracking System</p>

            <table>
                <tr>
                    <td>
                        OSATS came about because of a Applicant Tracking System application was made open source at first... then the creator decided to go commercial!<br/>
						So... a community of real open source heros came together to bring about the worlds TRULY open source applicant tracking system. <br/>
						Visit ------ for more info. 
                    </td>
                </tr>
            </table>
            <br />

            
        </div>
    </div>
    <?php /* Show the shadow at the bottom of the OSATS "window". */ ?>
    <div id="bottomShadow"></div>
<?php /* Print the common footer. */ ?>
<?php TemplateUtility::printFooter(); ?>