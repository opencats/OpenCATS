<?php /* $Id: Show.tpl 3444 2007-11-06 23:16:27Z will $
*/
include_once('./vendor/autoload.php');
use OpenCATS\UI\QuickActionMenu;
?>
<?php TemplateUtility::printHeader('Contact - '.$this->data['firstName'].' '.$this->data['lastName'], array( 'js/activity.js', 'js/attachment.js')); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active); ?>
    <div id="main">
        <?php TemplateUtility::printQuickSearch(); ?>

        <div id="contents">
            <table>
                <tr>
                    <td width="3%">
                        <img src="images/contact.gif" width="24" height="24" border="0" alt="Contacts" style="margin-top: 3px;" />&nbsp;
                    </td>
                    <td><h2>Contacts: Contact Details</h2></td>
                </tr>
            </table>

            <p class="note">Contact Details</p>

            <table class="detailsOutside">
                <tr style="vertical-align:top;">
                    <td width="50%" height="100%">
                        <table class="detailsInside" height="100%">
                            <tr>
                                <td class="vertical">Name:</td>
                                <td class="data">
                                    <span class="bold">
                                        <span class="<?php echo($this->data['titleClassContact']);?>">
                                            <?php $this->_($this->data['firstName']); ?>
                                            <?php $this->_($this->data['lastName']); ?>
                                            <?php TemplateUtility::printSingleQuickActionMenu(new QuickActionMenu(DATA_ITEM_CONTACT, $this->contactID, $_SESSION['CATS']->getAccessLevel('contacts.edit'))); ?>
                                        </span>
                                        &nbsp;
                                        <a id="vCard" href="<?php echo(CATSUtility::getIndexName()); ?>?m=contacts&amp;a=downloadVCard&amp;contactID=<?php echo($this->contactID); ?>">
                                            <img src="images/vcard.gif" class="absmiddle" alt="vCard" border="0" />
                                        </a>
                                    </span>
                                </td>
                            </tr>

                            <tr>
                                <td class="vertical">Company:</td>
                                <td class="data">
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=companies&amp;a=show&amp;companyID=<?php echo($this->data['companyID']); ?>">
                                        <span class="<?php echo($this->data['titleClassCompany']);?>">
                                            <?php echo($this->data['companyName']); ?>
                                        </span>
                                    </a>
                                    <?php if ($this->data['leftCompany']): ?>
                                        &nbsp;(no longer associated with company)
                                    <?php endif; ?>
                                </td>
                            </tr>

                            <tr>
                                <td class="vertical">Title:</td>
                                <td class="data"><?php $this->_($this->data['title']); ?></td>
                            </tr>

                            <tr>
                                <td class="vertical">Department:</td>
                                <td class="data"><?php $this->_($this->data['department']); ?></td>
                            </tr>

                            <tr>
                                <td class="vertical">Work Phone:</td>
                                <td class="data"><?php $this->_($this->data['phoneWork']); ?></td>
                            </tr>

                            <tr>
                                <td class="vertical">Cell Phone:</td>
                                <td class="data"><?php $this->_($this->data['phoneCell']); ?></td>
                            </tr>

                            <tr>
                                <td class="vertical">Other Phone:</td>
                                <td class="data"><?php $this->_($this->data['phoneOther']); ?></td>
                            </tr>

                            <?php for ($i = 0; $i < intval(count($this->extraFieldRS)/2); $i++): ?>
                               <tr>
                                    <td class="vertical"><?php $this->_($this->extraFieldRS[$i]['fieldName']); ?>:</td>
                                    <td class="data"><?php echo($this->extraFieldRS[$i]['display']); ?></td>
                              </tr>
                            <?php endfor; ?>
                        </table>
                    </td>

                    <td width="50%" height="100%">
                        <table class="detailsInside" height="100%">
                            <tr>
                                <td class="vertical">Reports To:</td>
                                <td class="data">
                                    <?php if($this->data['reportsTo'] == -1 || $this->data['reportsTo'] == 0 || $this->data['reportsToTitle'] == ''): ?>
                                        (None)
                                    <?php else: ?>
                                        <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=contacts&amp;a=show&amp;contactID=<?php echo($this->data['reportsTo']); ?>">
                                            <img src="images/contact_small.gif" border="0" />&nbsp;
                                            <?php $this->_($this->data['reportsToFirstName']); ?>&nbsp;<?php $this->_($this->data['reportsToLastName']); ?>
                                        </a>
                                        &nbsp;(<?php $this->_($this->data['reportsToTitle']); ?>)
                                    <?php endif; ?>
                                </td>
                            </tr>

                            <tr>
                                <td class="vertical">E-Mail:</td>
                                <td class="data">
                                    <a href="mailto:<?php $this->_($this->data['email1']); ?>"><?php $this->_($this->data['email1']); ?></a>
                                </td>
                            </tr>

                            <tr>
                                <td class="vertical">2nd E-Mail:</td>
                                <td class="data">
                                    <a href="mailto:<?php $this->_($this->data['email2']); ?>"><?php $this->_($this->data['email2']); ?></a>
                                </td>
                            </tr>

                            <tr>
                                <td class="vertical">Address:</td>
                                <td class="data"><?php echo(nl2br(htmlspecialchars($this->data['address']))); ?></td>
                            </tr>

                            <tr>
                                <td class="vertical">&nbsp;</td>
                                <td class="data">
                                    <?php $this->_($this->data['cityAndState']); ?>
                                    <?php $this->_($this->data['zip']); ?>
                                </td>
                            </tr>

                            <tr>
                                <td class="vertical">Created:</td>
                                <td class="data"><?php $this->_($this->data['dateCreated']); ?> (<?php $this->_($this->data['enteredByFullName']); ?>)</td>
                            </tr>

                            <tr>
                                <td class="vertical">Owner:</td>
                                <td class="data"><?php $this->_($this->data['ownerFullName']); ?></td>
                            </tr>

                            <?php for ($i = (intval(count($this->extraFieldRS))/2); $i < (count($this->extraFieldRS)); $i++): ?>
                                <tr>
                                    <td class="vertical"><?php $this->_($this->extraFieldRS[$i]['fieldName']); ?>:</td>
                                    <td class="data"><?php echo($this->extraFieldRS[$i]['display']); ?></td>
                                </tr>
                            <?php endfor; ?>
                        </table>
                    </td>
                </tr>
            </table>

            <table class="detailsOutside">
                <tr>
                    <td>
                        <table class="detailsInside">
                            <tr>
                                <td valign="top" class="vertical">Misc. Notes:</td>
                                <?php if ($this->isShortNotes): ?>
                                    <td id="shortNotes" style="display:block;" class="data">
                                        <?php echo($this->data['shortNotes']); ?><span class="moreText">...</span>&nbsp;
                                        <p><a href="#" class="moreText" onclick="toggleNotes(); return false;">[More]</a></p>
                                    </td>
                                    <td id="fullNotes" style="display:none;" class="data">
                                        <?php echo($this->data['notes']); ?>&nbsp;
                                        <p><a href="#" class="moreText" onclick="toggleNotes(); return false;">[Less]</a></p>
                                    </td>
                                <?php else: ?>
                                    <td id="shortNotes" style="display:block;" class="data">
                                        <?php echo($this->data['notes']); ?>
                                    </td>
                                <?php endif; ?>
                            </tr>
                            <tr>
                                <td valign="top" class="vertical">Upcoming Events:</td>
                                <td id="shortNotes" style="display:block;" class="data">
                                <?php foreach ($this->calendarRS as $rowNumber => $calendarData): ?>
                                    <div>
                                        <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=calendar&amp;view=DAYVIEW&amp;month=<?php echo($calendarData['month']); ?>&amp;year=20<?php echo($calendarData['year']); ?>&amp;day=<?php echo($calendarData['day']); ?>&amp;showEvent=<?php echo($calendarData['eventID']); ?>">
                                            <img src="<?php $this->_($calendarData['typeImage']) ?>" alt="" border="0">
                                            <?php $this->_($calendarData['dateShow']) ?>:
                                            <?php $this->_($calendarData['title']); ?>
                                        </a>
                                    </div>
                                <?php endforeach; ?>
                                <?php if ($this->getUserAccessLevel('contacts.addActivityScheduleEvent') >= ACCESS_LEVEL_EDIT): ?>
                                    <a href="#" onclick="showPopWin('<?php echo(CATSUtility::getIndexName()); ?>?m=contacts&amp;a=addActivityScheduleEvent&amp;contactID=<?php echo($this->contactID); ?>&amp;onlyScheduleEvent=true', 600, 200, null); return false;">
                                        <img src="images/calendar_add.gif" width="16" height="16" border="0" alt="Schedule Event" class="absmiddle" />&nbsp;Schedule Event
                                    </a>
                                <?php endif; ?>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
            <?php if ($this->getUserAccessLevel('contacts.edit') >= ACCESS_LEVEL_EDIT): ?>
                <a id="edit_link" href="<?php echo(CATSUtility::getIndexName()); ?>?m=contacts&amp;a=edit&amp;contactID=<?php echo($this->contactID); ?>">
                    <img src="images/actions/edit.gif" width="16" height="16" class="absmiddle" alt="edit" border="0" />&nbsp;Edit
                </a>
                &nbsp;&nbsp;&nbsp;&nbsp;
            <?php endif; ?>
            <?php if ($this->getUserAccessLevel('contacts.delete') >= ACCESS_LEVEL_DELETE): ?>
                <a id="delete_link" href="<?php echo(CATSUtility::getIndexName()); ?>?m=contacts&amp;a=delete&amp;contactID=<?php echo($this->contactID); ?>" onclick="javascript:return confirm('Delete this candidate?');">
                    <img src="images/actions/delete.gif" width="16" height="16" class="absmiddle" alt="delete" border="0" />&nbsp;Delete
                </a>
                &nbsp;&nbsp;&nbsp;&nbsp;
            <?php endif; ?>
            <?php if ($this->privledgedUser): ?>
                <a id="history_link" href="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=viewItemHistory&amp;dataItemType=300&amp;dataItemID=<?php echo($this->contactID); ?>">
                    <img src="images/icon_clock.gif" width="16" height="16" class="absmiddle"  border="0" />&nbsp;View History
                </a>
                &nbsp;&nbsp;&nbsp;&nbsp;
            <?php endif; ?>

            <br clear="all" />
            <br />

            <p class="note">Job Orders</p>
            <table class="sortable">
                <tr>
                    <th align="left" width="200">Title</th>
                    <th align="left" width="15">Type</th>
                    <th align="left" width="15">Status</th>
                    <th align="left" width="60">Created</th>
                    <th align="left" width="60">Modified</th>
                    <th align="left" width="60">Start</th>
                    <th align="right" width="15">Age</th>
                    <th align="right" width="10">S</th>
                    <th align="right" width="10">P</th>
                    <th align="left" width="65">Recruiter</th>
                    <th align="left" width="68">Owner</th>
                </tr>

                <?php foreach ($this->jobOrdersRS as $rowNumber => $jobOrdersData): ?>
                    <tr class="<?php TemplateUtility::printAlternatingRowClass($rowNumber); ?>">
                        <td valign="top">
                            <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=joborders&amp;a=show&amp;jobOrderID=<?php $this->_($jobOrdersData['jobOrderID']) ?>">
                                <?php $this->_($jobOrdersData['title']) ?>
                            </a>
                        </td>
                        <td valign="top" align="left"><?php $this->_($jobOrdersData['type']) ?></td>
                        <td valign="top" align="left"><?php $this->_($jobOrdersData['status']) ?></td>
                        <td valign="top" align="left"><?php $this->_($jobOrdersData['dateCreated']) ?></td>
                        <td valign="top" align="left"><?php $this->_($jobOrdersData['dateModified']) ?></td>
                        <td valign="top" align="left"><?php $this->_($jobOrdersData['startDate']) ?></td>
                        <td valign="top" align="right"><?php $this->_($jobOrdersData['daysOld']) ?></td>
                        <td valign="top" align="right"><?php $this->_($jobOrdersData['submitted']); ?></td>
                        <td valign="top" align="right"><?php $this->_($jobOrdersData['pipeline']); ?></td>
                        <td valign="top" align="left"><?php $this->_($jobOrdersData['recruiterAbbrName']); ?></td>
                        <td valign="top" align="left"><?php $this->_($jobOrdersData['ownerAbbrName']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
            <br clear="all" />
            <br />

            <p class="note">Activity</p>
            <table id="activityTable" class="sortable">
                <tr>
                    <th align="left" width="125">Date</th>
                    <th align="left" width="90">Type</th>
                    <th align="left" width="90">Entered</th>
                    <th align="left" width="250">Regarding</th>
                    <th align="left">Notes</th>
                    <th align="left" width="40">Action</th>
                </tr>

                <?php foreach ($this->activityRS as $rowNumber => $activityData): ?>
                    <tr class="<?php TemplateUtility::printAlternatingRowClass($rowNumber); ?>">
                        <td align="left" valign="top" id="activityDate<?php echo($activityData['activityID']); ?>"><?php $this->_($activityData['dateCreated']) ?></td>
                        <td align="left" valign="top" id="activityType<?php echo($activityData['activityID']); ?>"><?php $this->_($activityData['typeDescription']) ?></td>
                        <td align="left" valign="top"><?php $this->_($activityData['enteredByAbbrName']) ?></td>
                        <td align="left" valign="top" id="activityRegarding<?php echo($activityData['activityID']); ?>"><?php $this->_($activityData['regarding']) ?></td>
                        <td align="left" valign="top" id="activityNotes<?php echo($activityData['activityID']); ?>"><?php $this->_($activityData['notes']) ?></td>
                        <td align="center" >
                            <?php if ($this->getUserAccessLevel('contacts.editActivity') >= ACCESS_LEVEL_EDIT): ?>
                                <a href="#" id="editActivity<?php echo($activityData['activityID']); ?>" onclick="Activity_editEntry(<?php echo($activityData['activityID']); ?>, <?php echo($this->contactID); ?>, <?php echo(DATA_ITEM_CONTACT); ?>, '<?php echo($this->sessionCookie); ?>'); return false;">
                                    <img src="images/actions/edit.gif" width="16" height="16" alt="" class="absmiddle" border="0" title="Edit"/>
                                </a>
                            <?php endif; ?>
                            <?php if ($this->getUserAccessLevel('contacts.deleteActivity') >= ACCESS_LEVEL_EDIT): ?>
                                <a href="#" id="deleteActivity<?php echo($activityData['activityID']); ?>" onclick="Activity_deleteEntry(<?php echo($activityData['activityID']); ?>, '<?php echo($this->sessionCookie); ?>'); return false;">
                                    <img src="images/actions/delete.gif" width="16" height="16" alt="" class="absmiddle" border="0" title="Delete"/>
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
            <div id="addActivityDiv">
                <?php if ($this->getUserAccessLevel('contacts.logActivityScheduleEvent') >= ACCESS_LEVEL_EDIT): ?>
                    <a href="#" id="addActivityLink" title="Log an Activity / Schedule Event" onclick="showPopWin('<?php echo(CATSUtility::getIndexName()); ?>?m=contacts&amp;a=addActivityScheduleEvent&amp;contactID=<?php echo($this->contactID); ?>', 600, 375, null); return false;">
                        <img src="images/new_activity_inline.gif" width="16" height="16" class="absmiddle" title="Log an Activity / Schedule Event" alt="Log an Activity / Schedule Event" border="0" />&nbsp;Log an Activity / Schedule Event
                    </a>
                <?php endif; ?>
                <img src="images/indicator2.gif" id="addActivityIndicator" alt="" style="visibility: hidden; margin-left: 5px;" height="16" width="16" />
            </div>
        </div>
    </div>
<?php TemplateUtility::printFooter(); ?>
