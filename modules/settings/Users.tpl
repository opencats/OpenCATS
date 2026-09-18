<?php TemplateUtility::printHeader('Settings', 'js/sorttable.js'); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active, $this->subActive); ?>
    <?php TemplateUtility::printQuickSearch(); ?>
<main id="main" class="container-fluid py-2">

        <div id="contents">
            <header class="oc-page-header mb-2">
                <h1 class="h5 fw-semibold mb-0">Settings: User Management</h1>
            </header>

            <p class="bg-secondary-subtle rounded p-2 mb-2 fw-semibold">User Management</p>

            <div class="table-responsive mb-2"><table class="sortable table table-sm table-striped align-middle">
                <thead>
                    <tr>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Username</th>
                        <th>Access Level</th>
                        <th>Last Success</th>
                        <th>Last Fail</th>
                    </tr>
                </thead>

                <?php if (!empty($this->rs)): ?>
                    <?php foreach ($this->rs as $rowNumber => $data): ?>
                        <tr class="<?php TemplateUtility::printAlternatingRowClass($rowNumber); ?>">
                            <td>
                                <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=showUser&amp;userID=<?php $this->_($data['userID']); ?>">
                                    <?php $this->_($data['firstName']); ?>
                                </a>
                            </td>
                            <td>
                                <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=showUser&amp;userID=<?php $this->_($data['userID']); ?>">
                                    <?php $this->_($data['lastName']); ?>
                                </a>
                            </td>
                            <td><?php $this->_($data['username']); ?></td>
                            <td><?php $this->_($data['accessLevelDescription']); ?></td>
                            <td><?php $this->_($data['successfulDate']); ?></td>
                            <td><?php $this->_($data['unsuccessfulDate']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </table></div>
            <?php if (AUTH_MODE != "ldap"): ?>
                <a class="btn btn-primary btn-sm" id="add_link" href="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=addUser" title="You have <?php $this->_($this->license['diff']); ?> user accounts remaining.">
                    <img src="images/candidate_inline.gif" class="absmiddle" alt="add" style="border: none;" />&nbsp;Add User
                </a>
            <?php endif; ?>
        </div>
    </main>
<?php TemplateUtility::printFooter(); ?>
