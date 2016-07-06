<?php /* $Id: SearchEverything.tpl 1948 2007-02-23 09:49:27Z will $ */ ?>
<?php TemplateUtility::printHeader('Quick Search', array('js/sorttable.js')); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active); ?>
    <div id="main">
        <?php TemplateUtility::printQuickSearch($this->wildCardQuickSearch); ?>
        <div id="contents">
            <table>
                <tr>
                    <td width="3%">
                        <img src="images/search.gif" width="24" height="24" border="0" alt="Quick Search" style="margin-top: 3px;" />&nbsp;
                    </td>
                    <td><h2>Quick Search</h2></td>
                </tr>
            </table>
            <br />

            <!-- JO -->
            <p class="note">Job Orders Results</p>
            <?php if (!empty($this->jobOrdersRS)): ?>
                <table class="sortable" width="100%">
                    <tr>
                        <th align="left">Title</th>
                        <th align="left">Company</th>
                        <th align="left">Type</th>
                        <th align="left">Status</th>
                        <th align="left">Start</th>
                        <th align="left">Recruiter</th>
                        <th align="left">Owner</th>
                        <th align="left">Created</th>
                        <th align="left">Modified</th>

                    </tr>

                    <?php foreach ($this->jobOrdersRS as $rowNumber => $jobOrdersData): ?>
                        <tr class="<?php TemplateUtility::printAlternatingRowClass($rowNumber); ?>">
                            <td valign="top">
                                <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=joborders&amp;a=show&amp;jobOrderID=<?php $this->_($jobOrdersData['jobOrderID']) ?>" class="<?php $this->_($jobOrdersData['linkClass']) ?>">
                                    <?php $this->_($jobOrdersData['title']) ?>
                                </a>
                            </td>
                            <td valign="top">
                                <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=companies&amp;a=show&amp;companyID=<?php $this->_($jobOrdersData['companyID']) ?>">
                                    <?php $this->_($jobOrdersData['companyName']) ?>
                                </a>
                            </td>
                            <td valign="top"><?php $this->_($jobOrdersData['type']) ?></td>
                            <td valign="top"><?php $this->_($jobOrdersData['status']) ?></td>
                            <td valign="top"><?php $this->_($jobOrdersData['startDate']) ?></td>
                            <td valign="top"><?php $this->_($jobOrdersData['recruiterAbbrName']) ?></td>
                            <td valign="top"><?php $this->_($jobOrdersData['ownerAbbrName']) ?></td>
                            <td valign="top"><?php $this->_($jobOrdersData['dateCreated']) ?></td>
                            <td valign="top"><?php $this->_($jobOrdersData['dateModified']) ?></td>

                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php else: ?>
                <p>No matching entries found.</p>
            <?php endif; ?>
            <br />
            <!-- /JO -->

            <!-- Candidates -->
            <p class="note">Candidates Results</p>
            <?php if (!empty($this->candidatesRS)): ?>
                <table class="sortable" width="100%">
                    <tr>
                        <th align="left" nowrap="nowrap">First Name</th>
                        <th align="left" nowrap="nowrap">Last Name</th>
                        <th align="left" width="160">Home</th>
                        <th align="left" width="160">Cell</th>
                        <th align="left" width="65">Owner</th>
                        <th align="left" width="60">Created</th>
                        <th align="left" width="60">Modified</th>
                    </tr>

                    <?php foreach ($this->candidatesRS as $rowNumber => $candidatesData): ?>
                        <tr class="<?php TemplateUtility::printAlternatingRowClass($rowNumber); ?>">
                            <td>
                                <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=candidates&amp;a=show&amp;candidateID=<?php $this->_($candidatesData['candidateID']) ?>">
                                    <?php $this->_($candidatesData['firstName']) ?>
                                </a>
                            </td>
                            <td>
                                <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=candidates&amp;a=show&amp;candidateID=<?php $this->_($candidatesData['candidateID']) ?>">
                                    <?php $this->_($candidatesData['lastName']) ?>
                                </a>
                            </td>
                            <td valign="top" align="left"><?php $this->_($candidatesData['phoneHome']); ?></td>
                            <td valign="top" align="left"><?php $this->_($candidatesData['phoneCell']); ?></td>
                            <td nowrap="nowrap"><?php $this->_($candidatesData['ownerAbbrName']) ?></td>
                            <td><?php $this->_($candidatesData['dateCreated']) ?></td>
                            <td><?php $this->_($candidatesData['dateModified']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php else: ?>
                <p>No matching entries found.</p>
            <?php endif; ?>
            <br />
            <!-- /Candidates -->

            <!-- Companies -->
            <p class="note">Companies Results</p>
            <?php if (!empty($this->companiesRS)): ?>
                <table class="sortable" width="100%">
                    <thead>
                        <tr>
                            <th align="left">Name</th>
                            <th align="left" width="160" nowrap="nowrap">Primary Phone</th>
                            <th align="left" width="65">Owner</th>
                            <th align="left" width="60">Created</th>
                            <th align="left" width="60">Modified</th>
                        </tr>
                    </thead>

                    <?php foreach ($this->companiesRS as $rowNumber => $companiesData): ?>
                        <tr class="<?php TemplateUtility::printAlternatingRowClass($rowNumber); ?>">
                            <td valign="top" align="left">
                                <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=companies&amp;a=show&amp;companyID=<?php $this->_($companiesData['companyID']) ?>">
                                    <?php $this->_($companiesData['name']) ?>
                                </a>
                            </td>
                            <td valign="top" align="left"><?php $this->_($companiesData['phone1']) ?></td>
                            <td valign="top" align="left" nowrap="nowrap"><?php $this->_($companiesData['ownerAbbrName']) ?></td>
                            <td valign="top" align="left"><?php $this->_($companiesData['dateCreated']) ?></td>
                            <td valign="top" align="left"><?php $this->_($companiesData['dateModified']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php else: ?>
                <p>No matching entries found.</p>
            <?php endif; ?>
            <br />
            <!-- /Companies -->

            <!-- Contacts -->
            <p class="note">Contacts Results</p>
            <?php if (!empty($this->contactsRS)): ?>
                <table class="sortable" width="100%">
                    <tr>
                        <th align="left" nowrap="nowrap">First Name</th>
                        <th align="left" nowrap="nowrap">Last Name</th>
                        <th align="left">Title</th>
                        <th align="left">Company</th>
                        <th align="left">Work</th>
                        <th align="left">Cell</th>
                        <th align="left">Owner</th>
                        <th align="left">Created</th>
                        <th align="left">Modified</th>

                    </tr>

                    <?php foreach ($this->contactsRS as $rowNumber => $contactsData): ?>
                        <tr class="<?php TemplateUtility::printAlternatingRowClass($rowNumber); ?>">
                            <td valign="top" align="left">
                                <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=contacts&amp;a=show&amp;contactID=<?php $this->_($contactsData['contactID']) ?>" class="<?php $this->_($contactsData['linkClassContact']); ?>">
                                    <?php $this->_($contactsData['firstName']) ?>
                                </a>
                            </td>
                            <td valign="top" align="left">
                                <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=contacts&amp;a=show&amp;contactID=<?php $this->_($contactsData['contactID']) ?>" class="<?php $this->_($contactsData['linkClassContact']); ?>">
                                    <?php $this->_($contactsData['lastName']) ?>
                                </a>
                            </td>
                            <td valign="top" align="left"><?php $this->_($contactsData['title']) ?></td>
                            <td valign="top" align="left">
                                <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=companies&amp;a=show&amp;companyID=<?php $this->_($contactsData['companyID']) ?>" class="<?php $this->_($contactsData['linkClassCompany']); ?>">
                                    <?php $this->_($contactsData['companyName']) ?>
                                </a>
                            </td>
                            <td valign="top" align="left"><?php $this->_($contactsData['phoneWork']) ?></td>
                            <td valign="top" align="left"><?php $this->_($contactsData['phoneCell']) ?></td>
                            <td valign="top" align="left" nowrap="nowrap"><?php $this->_($contactsData['ownerAbbrName']) ?></td>
                            <td valign="top" align="left"><?php $this->_($contactsData['dateCreated']) ?></td>
                            <td valign="top" align="left"><?php $this->_($contactsData['dateModified']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php else: ?>
                <p>No matching entries found.</p>
            <?php endif; ?>
            <!-- /Contacts -->
        </div>
    </div>
<?php TemplateUtility::printFooter(); ?>
