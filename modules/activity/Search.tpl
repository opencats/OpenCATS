<?php /* $Id: Search.tpl 1948 2007-02-23 09:49:27Z will $ */ ?>
<?php TemplateUtility::printHeader('Activities', array('js/highlightrows.js', 'modules/activity/validator.js', 'js/sweetTitles.js')); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active); ?>
    <div id="main">
        <?php TemplateUtility::printQuickSearch(); ?>
        <div id="contents">
            <table width="100%">
                <tr>
                    <td width="3%">
                        <img src="images/activities.gif" width="24" height="24" alt="Activities" style="border: none; margin-top: 3px;" />&nbsp;
                    </td>
                    <td><h2>Activities</h2></td>
                    <td align="right">
                      <?php echo($this->quickLinks); ?>
                    </td>
                </tr>
            </table>

            <?php if (!empty($this->rs)): ?>
                <p class="note">Activities on <?php echo($this->startDate['month'].'/'.$this->startDate['day'].'/'.$this->startDate['year'])?></p>

                 <table id="activityTable" class="sortable" width="100%" onmouseover="javascript:trackTableHighlight(event)">
                    <tr>
                        <th align="left" width="60" nowrap="nowrap">
                            <?php $this->pager->printSortLink('dateCreatedSort', 'Date'); ?>
                        </th>
                        <th align="left" width="18"></th>
                        <th align="left" width="60" nowrap="nowrap">
                            <?php $this->pager->printSortLink('firstName', 'First Name'); ?>
                        </th>
                        <th align="left" width="80" nowrap="nowrap">
                            <?php $this->pager->printSortLink('lastName', 'Last Name'); ?>
                        </th>
                        <th align="left" width="160" nowrap="nowrap">
                            <?php $this->pager->printSortLink('regarding', 'Regarding'); ?>
                        </th>
                        <th align="left" width="80" nowrap="nowrap">
                            <?php $this->pager->printSortLink('typeDescription', 'Activity'); ?>
                        </th>
                        <th align="left" width="280" nowrap="nowrap">
                            <?php $this->pager->printSortLink('notes', 'Notes'); ?>
                        </th>
                        <th align="left" width="65" nowrap="nowrap">
                            <?php $this->pager->printSortLink('enteredByLastName', 'Entered By'); ?>
                        </th>
                    </tr>

                    <?php foreach ($this->rs as $rowNumber => $activityData): ?>
                        <tr class="<?php TemplateUtility::printAlternatingRowClass($rowNumber); ?>">
                            <td align="left" valign="top" nowrap="nowrap">
                                <?php echo($activityData['dateCreated']); ?>
                            </td>

                            <td align="left" valign="top">
                               <img width="16" height="16" src="images/<?php echo($activityData['icon']); ?>" alt="" />
                            </td>

                            <td align="left" valign="top" >
                                <a href="<?php $this->_($activityData['activityURL']); ?>" title="<?php $this->_($activityData['itemInfo']); ?>">
                                    <?php $this->_($activityData['firstName']); ?>
                                </a>
                            </td>

                            <td align="left" valign="top" >
                                <a href="<?php $this->_($activityData['activityURL']); ?>" title="<?php $this->_($activityData['itemInfo']); ?>">
                                    <?php $this->_($activityData['lastName']); ?>
                                </a>
                            </td>

                            <td align="left" valign="top" id="activityRegarding<?php echo($activityData['activityID']); ?>">
                                <?php echo($activityData['regarding']); ?>
                            </td>

                            <td align="left" valign="top" id="activityType<?php echo($activityData['activityID']); ?>">
                                <?php $this->_($activityData['typeDescription']); ?>
                            </td>

                            <td align="left" valign="top" >
                                <?php echo($activityData['notes']); ?>
                            </td>

                            <td align="left" valign="top">
                                <?php $this->_($activityData['enteredByAbbrName']); ?>
                            </td>
                        </tr>
                    <?php endforeach ?>
                </table>
                <?php $this->pager->printNavigation(); ?>
            <?php elseif ($this->isResultsMode): ?>
                <p class="note">No activities found on <?php echo($this->startDate['month'] . '/' . $this->startDate['day'] . '/' . $this->startDate['year']); ?></p>
            <?php endif; ?>
        </div>
    </div>
<?php TemplateUtility::printFooter(); ?>
