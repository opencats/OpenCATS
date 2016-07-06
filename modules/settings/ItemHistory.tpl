<?php /* $Id: ItemHistory.tpl 1528 2007-01-22 00:51:45Z will $ */ ?>
<?php TemplateUtility::printHeader('Settings', array('modules/settings/validator.js')); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active, $this->subActive); ?>
    <?php $longFields = array('description', 'notes'); ?>

    <div id="main">
        <?php TemplateUtility::printQuickSearch(); ?>

        <div id="contents">
            <table width="100%">
                <tr>
                    <td width="3%">
                        <img src="images/settings.gif" width="24" height="24" border="0" alt="Settings" style="margin-top: 3px;" />&nbsp;
                    </td>
                    <td align="left"><h2>Item History (Administrator View)</h2></td>
                </tr>
            </table>

            <p class="note">Item History</p>

            <table>
                <tr>
                    <td>
                        <table class="editTable" id="historyTable" width="600">
                            <?php $counter=0; ?>
                            <?php foreach ($this->data as $field => $value): ?>
                              <?php if (!in_array($field, $longFields)): ?>
                                <?php $counter++; if ($counter == 2): ?>
                                    <?php $counter = 0; ?>
                                        <td class="tdVertical" style="width:130px;">
                                            <?php $this->_($field); ?>
                                        </td>
                                        <td class="tdData">
                                            <div id="databaseValue<?php $this->_($field); ?>"><?php echo(nl2br(htmlspecialchars($value))); ?></div>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <tr>
                                        <td class="tdVertical" style="width:130px;">
                                            <?php $this->_($field); ?>
                                        </td>
                                        <td class="tdData">
                                            <div id="databaseValue<?php $this->_($field); ?>"><?php echo(nl2br(htmlspecialchars($value))); ?></div>
                                        </td>
                                <?php endif; ?>
                              <?php endif; ?>
                            <?php endforeach; ?>
                            <?php if ($counter == 1) echo('</td>'); ?>
                        </table>
                    </td>
                    <td>
                        <!-- revisions go here -->
                        <div id="selectHistoryDiv" style="clear:both; border: 1px solid #963; height: 300px; overflow: auto; width: 310px;">
                            <!--<a href="javascript:void(0);" onclick="gotoRevision(-10);">
                                <span style="font-size:10px;">--Newest revision--</span><br /><br />
                            </a>-->
                            <?php foreach ($this->revisionRS as $revisionID => $revision): ?>
                                <?php if ($revision['description'] != '' && $revision['theField'] != strtoupper($revision['theField'])): ?>
                                    <?php $description = str_replace('(USER)', $revision['enteredByFullName'], $revision['description']); ?>
                                    <a href="javascript:void(0);" onclick="gotoRevision(<?php echo($revisionID) ?>);">
                                        <span style="font-size:10px;"><?php $this->_($revision['dateModified'].': '.$description); ?></span><br /><br />
                                    </a>
                                <?php endif; ?>
                            <?php endforeach; ?>
                            <!--<a href="javascript:void(0);" onclick="gotoRevision(<?php echo(count($this->revisionRS)+10) ?>);">
                                <span style="font-size:10px;">--Oldest revision--</span><br /><br />
                            </a>-->
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <table class="editTable" id="historyTable" width="600">
                            <tr>
                                <?php foreach ($this->data as $field => $value): ?>
                                  <?php if (in_array($field, $longFields)): ?>
                                    <?php $counter=0; ?>
                                        <td class="tdVertical" style="width:130px;">
                                            <?php $this->_($field); ?>
                                        </td>
                                        <td class="tdData">
                                            <div id="databaseValue<?php $this->_($field); ?>"><?php echo(nl2br(htmlspecialchars($value))); ?></div>
                                        </td>
                                    </tr>
                                  <?php endif; ?>
                                <?php endforeach; ?>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>

            <script type="text/javascript">
                var currentRevision = -1;

                function gotoRevision(desiredRevision)
                {
                <?php foreach ($this->data as $field => $value): ?>
                    document.getElementById('databaseValue<?php $this->_($field); ?>').style.fontWeight = '';
                    document.getElementById('databaseValue<?php $this->_($field); ?>').style.color = '#000000';
                <?php endforeach; ?>
                    desiredRevision--;
                    if (desiredRevision > currentRevision)
                    {
                        gotoPastRevision(desiredRevision);
                    }
                    else if (desiredRevision < currentRevision)
                    {
                        gotoFutureRevision(desiredRevision);
                    }
                    currentRevision = desiredRevision;
                }

                function gotoPastRevision(desiredRevision)
                {
                    <?php foreach ($this->revisionRS as $revisionID => $revision): ?>
                        <?php if (isset($this->data[$revision['theField']])): ?>
                            if (currentRevision <= <?php echo($revisionID); ?> && desiredRevision >= <?php echo($revisionID); ?>)
                            {
                                document.getElementById("databaseValue<?php $this->_($revision['theField']); ?>").style.fontWeight = 'bold';
                                document.getElementById("databaseValue<?php $this->_($revision['theField']); ?>").style.color='#ff6c00';
                                document.getElementById("databaseValue<?php $this->_($revision['theField']); ?>").innerHTML = "<?php echo(str_replace(chr(13).chr(10), '', nl2br(htmlspecialchars($revision['previousValue'])))); ?>";
                            }
                        <?php endif; ?>
                    <?php endforeach; ?>
                }

                function gotoFutureRevision(desiredRevision)
                {
                    <?php $reverseOrder = array(); ?>
                    <?php foreach ($this->revisionRS as $revisionID => $revision): ?>
                        <?php $reverseOrder[] = $revisionID; ?>
                    <?php endforeach; ?>
                    <?php for ($i = count($reverseOrder) - 1; $i >= 0; $i--): ?>
                        <?php $revisionID = $reverseOrder[$i]; ?>
                        <?php $revision = $this->revisionRS[$revisionID]; ?>

                        <?php if (isset($this->data[$revision['theField']])): ?>
                            if (currentRevision >= <?php echo($revisionID); ?> && desiredRevision <= <?php echo($revisionID); ?>)
                            {
                                document.getElementById("databaseValue<?php $this->_($revision['theField']); ?>").style.fontWeight = 'bold';
                                document.getElementById("databaseValue<?php $this->_($revision['theField']); ?>").style.color='#ff6c00';
                                document.getElementById("databaseValue<?php $this->_($revision['theField']); ?>").innerHTML = "<?php echo(str_replace(chr(13).chr(10), '', nl2br(htmlspecialchars($revision['newValue'])))); ?>";
                            }
                        <?php endif; ?>
                    <?php endfor; ?>
                }
                document.getElementById('selectHistoryDiv').style.height = document.getElementById('historyTable').offsetHeight + 'px';
            </script>

            <p class="note">Other History</p>

            <table>
                <?php foreach ($this->revisionRS as $revisionID => $revision): ?>
                    <?php if ($revision['description'] != '' && $revision['theField'] == strtoupper($revision['theField'])): ?>
                        <?php $description = str_replace('(USER)', $revision['enteredByFullName'], $revision['description']); ?>
                        <tr>
                            <td style="vertical-align:top;">
                                <span style="font-size:10px;"><?php $this->_($revision['dateModified'].': '.$description); ?></span>
                            </td>
                            <td style="vertical-align:top;">
                                    <?php if ($revision['previousValue'] != '' && $revision['previousValue'] != '(NEW)' && $revision['previousValue'] != '(ADD)'): ?>
                                                <span style="font-size:10px;">
                                                    <?php if ($revision['theField'] == 'ACTIVITY'): ?>
                                                        Old Value: <?php echo($revision['previousValue']); ?><br />
                                                    <?php else: ?>
                                                        Old Value: <?php $this->_($revision['previousValue']); ?><br />
                                                    <?php endif; ?>
                                                </span>
                                    <?php endif; ?>
                                    <?php if ($revision['newValue'] != '' && $revision['newValue'] != '(DELETE)'): ?>
                                                <span style="font-size:10px;">
                                                    <?php if ($revision['theField'] == 'ACTIVITY'): ?>
                                                        New Value: <?php echo($revision['newValue']); ?>
                                                    <?php else: ?>
                                                        New Value: <?php $this->_($revision['newValue']); ?>
                                                    <?php endif; ?>
                                                </span>
                                    <?php endif; ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                <?php endforeach; ?>
            </table>
        </div>
    </div>
<?php TemplateUtility::printFooter(); ?>
