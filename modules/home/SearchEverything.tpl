<?php TemplateUtility::printHeader('Quick Search', array('js/sorttable.js')); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active); ?>
<?php TemplateUtility::printQuickSearch($this->wildCardQuickSearch); ?>
    <main id="main" class="container-fluid py-2">
        <div id="contents">
            <header class="oc-page-header mb-2">
                <h1 class="h5 fw-semibold mb-0">Quick Search</h1>
            </header>

            <!-- JO -->
            <section class="card mb-2">
            <h2 class="card-header bg-secondary-subtle h6 py-1 px-2 fw-semibold mb-0">Job Orders Results</h2>
            <?php if (!empty($this->jobOrdersRS)): ?>
                <div class="table-responsive">
                <table class="table table-sm table-striped sortable mb-0">
                    <thead>
                    <tr>
                        <th>Title</th>
                        <th>Company</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Start</th>
                        <th>Recruiter</th>
                        <th>Owner</th>
                        <th>Created</th>
                        <th>Modified</th>

                    </tr>
                    </thead>
                    <tbody>

                    <?php foreach ($this->jobOrdersRS as $rowNumber => $jobOrdersData): ?>
                        <tr class="<?php TemplateUtility::printAlternatingRowClass($rowNumber); ?>">
                            <td>
                                <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=joborders&amp;a=show&amp;jobOrderID=<?php $this->_($jobOrdersData['jobOrderID']) ?>" class="<?php $this->_($jobOrdersData['linkClass']) ?>">
                                    <?php $this->_($jobOrdersData['title']) ?>
                                </a>
                            </td>
                            <td>
                                <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=companies&amp;a=show&amp;companyID=<?php $this->_($jobOrdersData['companyID']) ?>">
                                    <?php $this->_($jobOrdersData['companyName']) ?>
                                </a>
                            </td>
                            <td><?php $this->_($jobOrdersData['type']) ?></td>
                            <td><?php $this->_($jobOrdersData['status']) ?></td>
                            <td><?php $this->_($jobOrdersData['startDate']) ?></td>
                            <td><?php $this->_($jobOrdersData['recruiterAbbrName']) ?></td>
                            <td><?php $this->_($jobOrdersData['ownerAbbrName']) ?></td>
                            <td><?php $this->_($jobOrdersData['dateCreated']) ?></td>
                            <td><?php $this->_($jobOrdersData['dateModified']) ?></td>

                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
            <?php else: ?>
                <div class="card-body p-2 small text-body-secondary">No matching entries found.</div>
            <?php endif; ?>
            </section>
            <!-- /JO -->

            <!-- Candidates -->
            <section class="card mb-2">
            <h2 class="card-header bg-secondary-subtle h6 py-1 px-2 fw-semibold mb-0">Candidates Results</h2>
            <?php if (!empty($this->candidatesRS)): ?>
                <div class="table-responsive">
                <table class="table table-sm table-striped sortable mb-0">
                    <thead>
                    <tr>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Home</th>
                        <th>Cell</th>
                        <th>Owner</th>
                        <th>Created</th>
                        <th>Modified</th>
                    </tr>
                    </thead>
                    <tbody>

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
                            <td><?php $this->_($candidatesData['phoneHome']); ?></td>
                            <td><?php $this->_($candidatesData['phoneCell']); ?></td>
                            <td><?php $this->_($candidatesData['ownerAbbrName']) ?></td>
                            <td><?php $this->_($candidatesData['dateCreated']) ?></td>
                            <td><?php $this->_($candidatesData['dateModified']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
            <?php else: ?>
                <div class="card-body p-2 small text-body-secondary">No matching entries found.</div>
            <?php endif; ?>
            </section>
            <!-- /Candidates -->

            <!-- Companies -->
            <section class="card mb-2">
            <h2 class="card-header bg-secondary-subtle h6 py-1 px-2 fw-semibold mb-0">Companies Results</h2>
            <?php if (!empty($this->companiesRS)): ?>
                <div class="table-responsive">
                <table class="table table-sm table-striped sortable mb-0">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Primary Phone</th>
                            <th>Owner</th>
                            <th>Created</th>
                            <th>Modified</th>
                        </tr>
                    </thead>
                    <tbody>

                    <?php foreach ($this->companiesRS as $rowNumber => $companiesData): ?>
                        <tr class="<?php TemplateUtility::printAlternatingRowClass($rowNumber); ?>">
                            <td>
                                <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=companies&amp;a=show&amp;companyID=<?php $this->_($companiesData['companyID']) ?>">
                                    <?php $this->_($companiesData['name']) ?>
                                </a>
                            </td>
                            <td><?php $this->_($companiesData['phone1']) ?></td>
                            <td><?php $this->_($companiesData['ownerAbbrName']) ?></td>
                            <td><?php $this->_($companiesData['dateCreated']) ?></td>
                            <td><?php $this->_($companiesData['dateModified']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
            <?php else: ?>
                <div class="card-body p-2 small text-body-secondary">No matching entries found.</div>
            <?php endif; ?>
            </section>
            <!-- /Companies -->

            <!-- Contacts -->
            <section class="card mb-2">
            <h2 class="card-header bg-secondary-subtle h6 py-1 px-2 fw-semibold mb-0">Contacts Results</h2>
            <?php if (!empty($this->contactsRS)): ?>
                <div class="table-responsive">
                <table class="table table-sm table-striped sortable mb-0">
                    <thead>
                    <tr>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Title</th>
                        <th>Company</th>
                        <th>Work</th>
                        <th>Cell</th>
                        <th>Owner</th>
                        <th>Created</th>
                        <th>Modified</th>

                    </tr>
                    </thead>
                    <tbody>

                    <?php foreach ($this->contactsRS as $rowNumber => $contactsData): ?>
                        <tr class="<?php TemplateUtility::printAlternatingRowClass($rowNumber); ?>">
                            <td>
                                <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=contacts&amp;a=show&amp;contactID=<?php $this->_($contactsData['contactID']) ?>" class="<?php $this->_($contactsData['linkClassContact']); ?>">
                                    <?php $this->_($contactsData['firstName']) ?>
                                </a>
                            </td>
                            <td>
                                <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=contacts&amp;a=show&amp;contactID=<?php $this->_($contactsData['contactID']) ?>" class="<?php $this->_($contactsData['linkClassContact']); ?>">
                                    <?php $this->_($contactsData['lastName']) ?>
                                </a>
                            </td>
                            <td><?php $this->_($contactsData['title']) ?></td>
                            <td>
                                <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=companies&amp;a=show&amp;companyID=<?php $this->_($contactsData['companyID']) ?>" class="<?php $this->_($contactsData['linkClassCompany']); ?>">
                                    <?php $this->_($contactsData['companyName']) ?>
                                </a>
                            </td>
                            <td><?php $this->_($contactsData['phoneWork']) ?></td>
                            <td><?php $this->_($contactsData['phoneCell']) ?></td>
                            <td><?php $this->_($contactsData['ownerAbbrName']) ?></td>
                            <td><?php $this->_($contactsData['dateCreated']) ?></td>
                            <td><?php $this->_($contactsData['dateModified']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
            <?php else: ?>
                <div class="card-body p-2 small text-body-secondary">No matching entries found.</div>
            <?php endif; ?>
            </section>
            <!-- /Contacts -->
        </div>
    </main>
<?php TemplateUtility::printFooter(); ?>
