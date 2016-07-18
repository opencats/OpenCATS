<?php /* $Id: Tests.tpl 1930 2007-02-22 08:39:53Z will $ */ ?>
<?php $this->reporter->printHeader(array('modules/tests/validator.js')); ?>
<?php $this->reporter->printHeaderBlock(); ?>
    <br clear="all" />
    <table>
        <tr>
            <td width="3%">
                <img src="images/tests.gif" width="24" height="24" alt="Tests" style="border: none; margin-top: 3px;" />&nbsp;
            </td>
            <td><h2>Tests: Select Tests</h2></td>
        </tr>
    </table>

    <p class="note">Select tests to run.</p>

    <form name="selectTestsForm" id="selectTestsForm" action="<?php echo(CATSUtility::getIndexName()); ?>?m=tests&amp;a=runSelectedTests" method="post">
        <input type="hidden" name="postback" id="postback" value="postback" />

        <table>
            <tr>
                <td valign="top">
                    <table>
                        <tr>
                            <td><span class="bold">Web / System Tests</span></td>
                        </tr>

                        <?php foreach ($this->systemTestCases as $key => $value): ?>
                            <tr>
                                <td>
                                    <input type="checkbox" class="systemTests" name="<?php $this->_($value[0]); ?>" id="<?php $this->_($value[0]); ?>" />
                                    <?php $this->_($value[1]); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                        <tr>
                            <td>
                                <input type="checkbox" name="selectAllSystem" id="selectAllSystem" onclick="selectAllCheckboxesByClassName('selectTestsForm', 'selectAllSystem', 'systemTests');" />
                                <span class="bold">All</span>
                            </td>
                        </tr>
                    </table>
                </td>

                <td>&nbsp;</td>

                <td valign="top">
                    <table>
                        <tr>
                            <td><span class="bold">AJAX Tests</span></td>
                        </tr>

                        <?php foreach ($this->AJAXTestCases as $key => $value): ?>
                            <tr>
                                <td>
                                    <input type="checkbox" class="AJAXTests" name="<?php $this->_($value[0]); ?>" id="<?php $this->_($value[0]); ?>" />
                                    <?php $this->_($value[1]); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                        <tr>
                            <td>
                                <input type="checkbox" name="selectAllAJAX" id="selectAllAJAX" onclick="selectAllCheckboxesByClassName('selectTestsForm', 'selectAllAJAX', 'AJAXTests');" />
                                <span class="bold">All</span>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>

            <tr>
                <td>&nbsp;</td>
            </tr>

            <tr>
                <td>
                    <input type="checkbox" name="selectAll" id="selectAll" onclick="selectAllCheckboxes('selectTestsForm');" />
                    <span class="bold">Select All Tests</span>
                </td>
            </tr>
        </table>
        <input type="submit" tabindex="16" class="button" name="submit" value="Run Selected Tests" />&nbsp;
        <input type="reset"  tabindex="17" class="button" name="reset"  value="Reset" />&nbsp;
    </form>
<?php TemplateUtility::printFooter(); ?>
