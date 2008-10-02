<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
    <title>CATS Applicant Tracking System</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <link rel="icon" href="images/favicon.ico" type="image/x-icon" />
    <link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon" />
    <link rel="stylesheet" href="modules/wizard/style.css" type="text/css" />
    <script type="text/javascript" src="modules/wizard/wizard.js"></script>
    <?php if ($this->jsInclude != ''): ?>
    <script type="text/javascript" src="<?php echo $this->jsInclude; ?>"></script>
    <?php endif; ?>
    <script type="text/javascript">
        <?php echo $this->js; ?>
    </script>
</head>
<body onload="current();">

<div id="loadingBar">
    Loading <span id="loading1Dot">.</span><span id="loading2Dot">.</span><span id="loading3Dot">.</span>
</div>

<div id="pageContainer">
<center>
    <div id="wizardContainer">
        <table cellpadding="0" cellspacing="0" border="0" width="770">
            <tr>
            <?php for ($i=0; $i<count($this->pages); $i++): ?>
                <td id="section<?php echo $i+1; ?>" class="sectionTitle<?php if ($i == $this->currentPageIndex): ?>Current<?php endif; ?>"><?php echo $this->pages[$i]['title']; ?></td>
            <?php endfor; ?>
            </tr>
            <tr>
                <td colspan="<?php echo count($this->pages); ?>">
                    <table cellpadding="0" cellspacing="0" border="0" width="100%">
                        <tr>
                            <td width="18" height="18" align="left" valign="top"><img src="images/wizard/sectionTopLeft.jpg" alt="topLeft" border="0" /></td>
                            <td style="background-color: #FFB400;">&nbsp;</td>
                            <td width="18" height="18" align="left" valign="top"><img src="images/wizard/sectionTopRight.jpg" alt="topRight" border="0" /></td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <br />

        <table cellpadding="0" cellspacing="0" border="0" width="770">
            <tr>
                <td width="100%">
                    <table border="0" cellspacing="0" cellpadding="0" width="100%">
                        <tr>
                            <td align="right" valign="bottom" width="49" height="66"><img src="images/wizard/topLeft.jpg" alt="topLeft" border="0" /></td>
                            <td id="pageTitle" align="left" valign="middle" width="100%" height="66" class="wizardHeader"><?php echo $this->currentPage['title']; ?></td>
                            <td align="left" valign="bottom" width="137" height="66"><img src="images/wizard/topRight.jpg" alt="topRight" border="0" /></td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td align="left" valign="top" colspan="3" class="wizardBody">
                    <div id="wizardContainerBody">&nbsp;</div>
                </td>
            </tr>
            <tr>
                <td align="left" valign="top" colspan="3">
                    <table cellpadding="0" cellspacing="0" border="0" width="100%">
                        <tr>
                            <td align="left" valign="bottom" width="33" height="33"><img src="images/wizard/bottomLeft.jpg" border="0" alt="bottomLeft" /></td>
                            <td align="left" valign="middle" width="100%" height="33" class="wizardFooter">&nbsp;</td>
                            <td align="right" valign="bottom" width="33" height="33"><img src="images/wizard/bottomRight.jpg" border="0" alt="bottomRight" /></td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>

    <div id="wizardNavigator">
        <?php if ($this->enableSkip): ?>
            <input type="button" class="button" id="skip" value="Skip this Wizard" onclick="skip();" />
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <?php endif; ?>
            <input type="button" class="button" id="previous" value="Previous" onclick="previous();" />
        <?php if ($this->enableNext): ?>
            <input type="button" class="button" id="next" value="Next" onclick="next();" />
        <?php endif; ?>
    </div>
    </center>
</div>

</body>
</html>
