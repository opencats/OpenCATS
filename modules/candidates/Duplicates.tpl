<?php
/*
 * OpenCATS
 *
 * Portions Copyright (C) 2005-2007 Cognizo Technologies, Inc.
 * Originally released as part of CATS Standard Edition under the
 * CATS Public License 1.1a.
 *
 * See LICENSE.md.
 */

TemplateUtility::printHeader(
    'Candidates',
    array(
        'js/highlightrows.js',
        'js/export.js',
        'js/dataGrid.js',
        'js/dataGridFilters.js'
    )
);
?>

<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active); ?>
<?php TemplateUtility::printQuickSearch(); ?>
<?php $md5InstanceName = md5($this->dataGrid->getInstanceName()); ?>

<main id="main" class="container-fluid py-2 oc-candidate-duplicates-page">
    <div id="contents" class="oc-candidate-duplicates-content">
        <?php if ($this->totalDuplicates): ?>

        <section
            class="oc-page-header d-flex flex-wrap align-items-center gap-2 mb-2"
            >
            <div class="d-flex align-items-baseline gap-2 flex-shrink-0">
                <h1 class="h5 fw-semibold mb-0">Duplicates</h1>

                <span class="small text-body-secondary">
                <?php echo number_format(
    $this->dataGrid->getNumberOfRows()
); ?> items
                </span>
            </div>

            <form
                name="candidatesViewSelectorForm"
                id="candidatesViewSelectorForm"
                action="<?php echo Template::escapeAttr(
    CATSUtility::getIndexName()
); ?>"
                method="get"
                class="d-flex flex-wrap flex-xl-nowrap align-items-center justify-content-end gap-2 small ms-auto"
                >
                <input type="hidden" name="m" value="candidates">
                <input type="hidden" name="a" value="listByView">

                <div class="oc-datagrid-navigation flex-shrink-0">
                    <?php $this->dataGrid->printNavigation(false); ?>
                </div>

                <div class="form-check form-check-inline mb-0 me-0 flex-shrink-0">
                    <input
                    class="form-check-input"
                    type="checkbox"
                    name="onlyMyCandidates"
                    id="onlyMyCandidates"
                    <?php
if ($this->dataGrid->getFilterValue('OwnerID') == $this->userID)
{
    echo 'checked';
}
?>
                    onclick="<?php echo $this->dataGrid
->getJSAddRemoveFilterFromCheckbox(
    'OwnerID',
    '==',
    $this->userID
); ?>"
                    >

                    <label
                        class="form-check-label text-nowrap"
                        for="onlyMyCandidates"
                        >
                        Only My Candidates
                    </label>
                </div>

                <div class="form-check form-check-inline mb-0 me-0 flex-shrink-0">
                    <input
                    class="form-check-input"
                    type="checkbox"
                    name="onlyHotCandidates"
                    id="onlyHotCandidates"
                    <?php
    if ($this->dataGrid->getFilterValue('IsHot') == '1')
    {
        echo 'checked';
    }
    ?>
                    onclick="<?php echo $this->dataGrid
    ->getJSAddRemoveFilterFromCheckbox(
        'IsHot',
        '==',
        '\'1\''
    ); ?>"
                    >

                    <label
                        class="form-check-label text-nowrap"
                        for="onlyHotCandidates"
                        >
                        Only Hot Candidates
                    </label>
                </div>

                <div class="position-relative"><a href="javascript:void(0);" id="exportBoxLink<?= $md5InstanceName ?>" onclick="toggleHideShowControls('<?= $md5InstanceName ?>-tags'); return false;">Filter by tag</a>
                                        <div id="tagsContainer">
                                        <div class="card card-body p-2 position-absolute end-0 z-3 text-nowrap" id="ColumnBox<?= $md5InstanceName ?>-tags"  style="<?= isset($this->globalStyle)?$this->globalStyle:"" ?>">
                                            <div><div class="row g-2 align-items-start mb-2"><div class="col-12 col-sm">Tag list</div>
                                            <div class="col-12 col-sm">
                                                <button type="button" onclick="applyTagFilter()" value="Save&amp;Close" class="btn btn-sm btn-outline-secondary">Save&amp;Close</button>
                                                <button type="button" onclick="document.getElementById('ColumnBox<?= $md5InstanceName?>').style.display='none';" value="Close" class="btn btn-sm btn-outline-secondary">Close</button>
                                            </div>
                                            </div></div>


                                            <ul>
                                            <script>
                                            function applyTagFilter(){
                                                var arrValues=[];
                                                var tags=document.getElementsByName('candidate_tags[]');
                                                for(var el in tags){
                                                    if (tags[el].checked) arrValues.push(tags[el].value);
                                                };

                                                <?php echo $this->dataGrid->getJSAddFilter('Tags', '=#',  "arrValues.join('/')")?>;
                                            }
                                            </script>
                                            <?php $i=1;

                                            function drw($data, $id){
                                                global $i;
                                                foreach($data as $k => $v){
                                                    if ($v['tag_parent_id'] == $id){
                                                        ?><li><input type="checkbox" name="candidate_tags[]" id="checkbox<?= $i ?>" value="<?= $v['tag_id'] ?>" class="form-check-input"><label for="checkbox<?= $i++ ?>" class="form-label small mb-1"><?= $v['tag_title'] ?></label></li><?php
                                                        echo "\n<ul>";
                                                        drw($data, $v['tag_id']);
                                                        echo "\n</ul>";
                                                    }
                                                }
                                            }
                                            drw($this->tagsRS, '');
                                            ?></ul>
                                        </div>
                                        </div>
                                        <span style="display:none;" id="ajaxTableIndicator<?= $md5InstanceName ?>"><img src="images/indicator_small.gif" alt=""></span></div>
                <div class="oc-datagrid-rows-per-page flex-shrink-0">
                    <?php $this->dataGrid->drawRowsPerPageSelector(); ?>
                </div>

                <div class="oc-datagrid-filter-control flex-shrink-0">
                    <?php $this->dataGrid->drawShowFilterControl(); ?>
                </div>
            </form>
        </section>

        <?php if ($this->topLog != ''): ?><div class="mb-2"><?php echo $this->topLog; ?></div><?php endif; ?>
        <?php if ($this->errMessage != ''): ?>
        <div
            id="errorMessage"
            class="alert alert-danger py-2"
            role="alert"
            >
            <div class="fw-semibold">
                There was a problem with your request:
            </div>

            <div>
                <?php echo $this->errMessage; ?>
            </div>
        </div>
        <?php endif; ?>

        <section class="card oc-candidate-duplicates-list">
            <div class="card-body p-2">
                <div class="oc-candidate-duplicates-filters">
                    <?php $this->dataGrid->drawFilterArea(); ?>
                </div>

                <div class="oc-candidate-duplicates-datagrid">
                    <?php $this->dataGrid->draw(); ?>
                </div>
            </div>

            <div class="card-footer bg-body py-1 px-2">
                <div
                    class="d-flex flex-wrap align-items-center justify-content-between gap-2 small"
                    >
                    <div class="oc-candidate-duplicates-actions">
                        <?php $this->dataGrid->printActionArea(); ?>
                    </div>

                    <div class="oc-candidate-duplicates-pagination">
                        <?php $this->dataGrid->printNavigation(true); ?>
                    </div>
                </div>
            </div>
        </section>

        <?php else: ?>
        <section class="oc-page-header mb-2"><h1 class="h5 fw-semibold mb-0">Duplicates</h1></section>
        <section class="card"><div class="card-body p-3">
                <p class="text-body-secondary">No duplicate candidates found.</p>

            </div></section>
        <?php endif; ?>
    </div>
</main>

<?php TemplateUtility::printFooter(); ?>
