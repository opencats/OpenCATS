<style>
input.licenseKey { border: 1px solid #0C519D; padding: 5px; width: 500px; }
</style>
<table cellpadding="0" cellspacing="0" border="0">
    <tr>
        <td align="left" valign="top" style="padding-right: 10px;">
            <img src="images/wizard/professional.jpg" border="0" />
        </td>

        <td align="left" valign="top">
            Your license key has expired.
            <br /><br />
            Please enter your new CATS Professional or Open Source license key to continue
            using this software.
            <br /><br />
            Consider purchasing a CATS Professional license key to unlock all the features of
            CATS like our Monster toolbar, resume import, careers website, and more!
            <br /><br />

            <b>Need a license key?</b> <a href="http://www.catsone.com/?a=license_key" target="_blank">Register here</a>.

            <p />

            <b>Enter your open source or professional license key:</b>
            <p />
            <input type="text" name="key" id="key" value="<?php if (defined('LICENSE_KEY') && LICENSE_KEY != '') echo LICENSE_KEY; ?>" class="licenseKey" />
            <p />
        </td>
    </tr>
</table>
