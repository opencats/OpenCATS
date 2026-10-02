<?php TemplateUtility::printHeader('Activities', array('js/highlightrows.js', 'modules/activity/validator.js', 'js/sweetTitles.js')); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active); ?>
<?php TemplateUtility::printQuickSearch(); ?>
<main id="main" class="container-fluid py-2">
    <div id="contents">
        <section class="oc-page-header d-flex flex-wrap align-items-center gap-2 mb-2">
            <h1 class="h5 fw-semibold mb-0">Activities</h1>
            <?php if (!empty($this->quickLinks)): ?>
            <nav class="ms-auto small" aria-label="Activity date ranges"><?php echo($this->quickLinks); ?></nav>
            <?php endif; ?>
        </section>
        <section class="card"><div class="card-body p-2">
            <?php if (!empty($this->rs)): ?>
                <p class="small text-body-secondary">Activities on <?php echo Template::escapeHtml($this->startDate['month'] . '/' . $this->startDate['day'] . '/' . $this->startDate['year']); ?></p>

                 <div class="table-responsive">
                 <table id="activityTable" class="sortable table table-sm table-striped table-hover align-top" onmouseover="javascript:trackTableHighlight(event)">
                    <thead>
                    <tr>
                        <th scope="col" class="text-nowrap">
                            <?php $this->pager->printSortLink('dateCreatedSort', 'Date'); ?>
                        </th>
                        <th scope="col" class="text-nowrap"></th>
                        <th scope="col" class="text-nowrap">
                            <?php $this->pager->printSortLink('firstName', 'First Name'); ?>
                        </th>
                        <th scope="col" class="text-nowrap">
                            <?php $this->pager->printSortLink('lastName', 'Last Name'); ?>
                        </th>
                        <th scope="col" class="text-nowrap">
                            <?php $this->pager->printSortLink('regarding', 'Regarding'); ?>
                        </th>
                        <th scope="col" class="text-nowrap">
                            <?php $this->pager->printSortLink('typeDescription', 'Activity'); ?>
                        </th>
                        <th scope="col" class="text-nowrap">
                            <?php $this->pager->printSortLink('notes', 'Notes'); ?>
                        </th>
                        <th scope="col" class="text-nowrap">
                            <?php $this->pager->printSortLink('enteredByLastName', 'Entered By'); ?>
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($this->rs as $rowNumber => $activityData): ?>
                        <tr class="<?php TemplateUtility::printAlternatingRowClass($rowNumber); ?>">
                            <td class="text-nowrap">
                                <?php echo Template::escapeHtml($activityData['dateCreated']); ?>
                            </td>

                            <td>
                               <img width="16" height="16" src="<?php echo Template::escapeUrl('images/' . $activityData['icon']); ?>" alt="" />
                            </td>

                            <td>
                                <a href="<?php echo Template::escapeUrl($activityData['activityURL']); ?>" title="<?php echo Template::escapeAttr($activityData['itemInfo']); ?>">
                                    <?php $this->_($activityData['firstName']); ?>
                                </a>
                            </td>

                            <td>
                                <a href="<?php echo Template::escapeUrl($activityData['activityURL']); ?>" title="<?php echo Template::escapeAttr($activityData['itemInfo']); ?>">
                                    <?php $this->_($activityData['lastName']); ?>
                                </a>
                            </td>

                            <td id="activityRegarding<?php echo Template::escapeAttr($activityData['activityID']); ?>">
                                <?php echo Template::escapeHtml($activityData['regarding']); ?>
                            </td>

                            <td id="activityType<?php echo Template::escapeAttr($activityData['activityID']); ?>">
                                <?php $this->_($activityData['typeDescription']); ?>
                            </td>

                            <td>
                                <?php echo nl2br(TemplateUtility::highlightStatusChangeActivityNote($activityData['notes'])); ?>
                            </td>

                            <td>
                                <?php $this->_($activityData['enteredByAbbrName']); ?>
                            </td>
                        </tr>
                    <?php endforeach ?>
                    </tbody>
                </table>
                </div>
                <?php $this->pager->printNavigation(); ?>
            <?php elseif ($this->isResultsMode): ?>
                <p class="small text-body-secondary">No activities found on <?php echo Template::escapeHtml($this->startDate['month'] . '/' . $this->startDate['day'] . '/' . $this->startDate['year']); ?></p>
            <?php endif; ?>
        </div></section>
    </div>
</main>
<?php TemplateUtility::printFooter(); ?>
