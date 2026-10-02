<?php TemplateUtility::printHeader('Settings', array('modules/settings/validator.js')); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active, $this->subActive); ?>
    <?php $longFields = array('description', 'notes'); ?>

    <?php TemplateUtility::printQuickSearch(); ?>
<main id="main" class="container-fluid py-2">

        <div id="contents">
            <header class="oc-page-header mb-2">
                <h1 class="h5 fw-semibold mb-0">Item History (Administrator View)</h1>
            </header>

            <p class="bg-secondary-subtle rounded p-2 mb-2 fw-semibold">Item History</p>

            <div class="card card-body p-2 mb-2">
                <div class="row g-2 mb-2">
                    <div class="col-12 col-sm">
                        <div class="table-responsive"><table class="table table-sm align-middle" id="historyTable">
                            <?php $counter=0; ?>
                            <?php foreach ($this->data as $field => $value): ?>
                              <?php if (!in_array($field, $longFields)): ?>
                                <?php $counter++; if ($counter == 2): ?>
                                    <?php $counter = 0; ?>
                                        <td class="tdVertical">
                                            <?php $this->_($field); ?>
                                        </td>
                                        <td class="tdData">
                                            <div id="databaseValue<?php $this->_($field); ?>"><?php echo(nl2br(htmlspecialchars($value))); ?></div>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <tr>
                                        <td class="tdVertical">
                                            <?php $this->_($field); ?>
                                        </td>
                                        <td class="tdData">
                                            <div id="databaseValue<?php $this->_($field); ?>"><?php echo(nl2br(htmlspecialchars($value))); ?></div>
                                        </td>
                                <?php endif; ?>
                              <?php endif; ?>
                            <?php endforeach; ?>
                            <?php if ($counter == 1) echo('</td>'); ?>
                        </table></div>
                    </div>
                    <div class="col-12 col-sm">
                        <!-- revisions go here -->
                        <div id="selectHistoryDiv" class="border rounded p-2 overflow-auto" style="height:300px;">
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
                    </div>
                </div>
                <div class="row g-2 mb-2">
                    <div class="col-12 col-sm">
                        <div class="table-responsive"><table class="table table-sm align-middle" id="historyTable">
                            <tr>
                                <?php foreach ($this->data as $field => $value): ?>
                                  <?php if (in_array($field, $longFields)): ?>
                                    <?php $counter=0; ?>
                                        <td class="tdVertical">
                                            <?php $this->_($field); ?>
                                        </td>
                                        <td class="tdData">
                                            <div id="databaseValue<?php $this->_($field); ?>"><?php echo(nl2br(htmlspecialchars($value))); ?></div>
                                        </td>
                                    </tr>
                                  <?php endif; ?>
                                <?php endforeach; ?>
                            </tr>
                        </table></div>
                    </div>
                </div>
            </div>

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

            <p class="bg-secondary-subtle rounded p-2 mb-2 fw-semibold">Other History</p>

            <div class="card card-body p-2 mb-2">
                <?php foreach ($this->revisionRS as $revisionID => $revision): ?>
                    <?php if ($revision['description'] != '' && $revision['theField'] == strtoupper($revision['theField'])): ?>
                        <?php $description = str_replace('(USER)', $revision['enteredByFullName'], $revision['description']); ?>
                        <div class="row g-2 mb-2">
                            <div class="col-12 col-sm">
                                <span style="font-size:10px;"><?php $this->_($revision['dateModified'].': '.$description); ?></span>
                            </div>
                            <div class="col-12 col-sm">
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
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </main>
<?php TemplateUtility::printFooter(); ?>
