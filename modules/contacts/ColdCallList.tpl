<?php TemplateUtility::printHeader('Contacts', array('js/sorttable.js', 'js/highlightrows.js')); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active); ?>
<?php TemplateUtility::printQuickSearch(); ?>
<main id="main" class="container-fluid py-2 oc-contact-cold-call-page">

    <div id="contents">
        <section class="oc-page-header mb-2">
            <h1 class="h5 fw-semibold mb-0">Cold Call List</h1>
        </section>

        <section class="card">
            <div class="card-header bg-secondary-subtle py-1 px-2 fw-semibold">Cold Call List (Only Contacts with Phone Numbers)</div>

            <?php if (!empty($this->rs)): ?>
            <div class="table-responsive">
                <table class="sortable table table-sm table-striped table-hover align-middle mb-0" onmouseover="javascript:trackTableHighlight(event)">
                    <thead>
                        <tr>
                            <th scope="col">Company</th>
                            <th scope="col">First Name</th>
                            <th scope="col">Last Name</th>
                            <th scope="col">Title</th>
                            <th scope="col">Work Phone</th>
                        </tr>
                    </thead>
                    <tbody>

                        <?php foreach ($this->rs as $rowNumber => $data): ?>
                        <tr class="<?php TemplateUtility::printAlternatingRowClass($rowNumber); ?>">
                            <td><?php $this->_($data['companyName']); ?></td>
                            <td><?php $this->_($data['firstName']); ?></td>
                            <td><?php $this->_($data['lastName']); ?></td>
                            <td><?php $this->_($data['title']); ?></td>
                            <td><?php $this->_($data['phoneWork']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="card-body p-2 small text-body-secondary">No contacts with phone numbers found.</div>
            <?php endif; ?>
        </section>
    </div>
</main>
<?php TemplateUtility::printFooter(); ?>
