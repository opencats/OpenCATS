<?php TemplateUtility::printHeader($this->reportTitle); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
<main class="container-fluid py-2">
    <header class="oc-page-header mb-2">
        <h1 class="h5 fw-semibold mb-1"><?php $this->_($this->reportTitle); ?></h1>
        <p class="mb-0 text-body-secondary">Placements</p>
    </header>

    <?php foreach ($this->placementsJobOrdersRS as $rowNumber => $placementsJobOrdersData): ?>
        <section class="card mb-2">
            <h2 class="card-header bg-secondary-subtle h6 py-1 px-2 fw-semibold mb-0"><?php $this->_($placementsJobOrdersData['title']) ?> at <?php $this->_($placementsJobOrdersData['companyName']) ?> (<?php $this->_($placementsJobOrdersData['ownerFullName']) ?>)</h2>
            <div class="table-responsive">
                <table class="sortable table table-sm table-striped align-top mb-0">
                    <thead>
                        <tr>
                            <th scope="col" class="text-nowrap">First Name</th>
                            <th scope="col" class="text-nowrap">Last Name</th>
                            <th scope="col" class="text-nowrap">Candidate Owner</th>
                            <th scope="col" class="text-nowrap">Date Placed</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($placementsJobOrdersData['placementsRS'] as $rowNumber => $placementsData): ?>
                        <tr>
                            <td><?php $this->_($placementsData['firstName']) ?></td>
                            <td><?php $this->_($placementsData['lastName']) ?></td>
                            <td><?php $this->_($placementsData['ownerFullName']) ?></td>
                            <td><?php $this->_($placementsData['dateSubmitted']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    <?php endforeach; ?>
</main>
<?php TemplateUtility::printReportFooter(); ?>
