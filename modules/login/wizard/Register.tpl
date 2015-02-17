<style>
input.licenseKey { border: 1px solid #0C519D; padding: 5px; width: 500px; }
</style>
<table cellpadding="0" cellspacing="0" border="0">
    <tr>
        <td align="left" valign="top" style="padding-right: 10px;">
            <img src="images/wizard/professional.jpg" border="0" />
        </td>

        <td align="left" valign="top">
            <b>OpenCATS</b> users get everything.</b>
            <ul>
                <li>Freeform text-search across resume's.</li>
                <li>A careers website where applicants can view public jobs and apply.</li>
                <li>Import your resume documents directly as candidates.</li>
                <li>Plug-ins and add-ons to make recruiting even easier.</li>
                <li>Forum and e-mail support.</li>
            </ul>

            <p />

            <b>Enter your open source or professional license key:</b>
            <p />
            <input type="text" name="key" id="key" value="<?php if (defined('LICENSE_KEY') && LICENSE_KEY != '') echo LICENSE_KEY; ?>" class="licenseKey" />
            <p />
        </td>
    </tr>
</table>
