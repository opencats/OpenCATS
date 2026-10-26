<?php TemplateUtility::printHeader('Home', array('modules/home/home.css', 'vendor/twbs/bootstrap/dist/js/bootstrap.bundle.min.js', 'js/warningReviews.js', 'js/home.js')); ?>
<div class="home-screen">
<a class="visually-hidden-focusable home-skip" href="#main">Skip to priorities</a>
<div class="home-chrome">
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active); ?>
<?php TemplateUtility::printQuickSearch(); ?>
</div>
<main id="main" class="home container-fluid py-2" tabindex="-1">
    <div id="contents">
        <header class="oc-page-header d-flex flex-wrap align-items-center gap-2 mb-2">
            <h1 class="h4 fw-semibold mb-0">Your working day</h1>
            <nav class="ms-auto d-flex flex-wrap gap-2" aria-label="Work navigation">
                <?php if ($_SESSION['CATS']->getAccessLevel('tasks.list') >= ACCESS_LEVEL_READ && $_SESSION['CATS']->getAccessLevel('tasks.show') >= ACCESS_LEVEL_READ): ?>
                <a class="btn btn-sm btn-outline-secondary" href="<?php echo Template::escapeAttr(CATSUtility::getIndexName() . '?m=tasks'); ?>">My Tasks</a>
                <a class="btn btn-sm btn-outline-secondary" href="<?php echo Template::escapeAttr(CATSUtility::getIndexName() . '?m=tasks&status=completed'); ?>">Completed Tasks / Reopen</a>
                <?php endif; ?>
                <?php if ($_SESSION['CATS']->getAccessLevel('reports.show') >= ACCESS_LEVEL_READ): ?><a class="btn btn-sm btn-link" href="<?php echo Template::escapeAttr(CATSUtility::getIndexName() . '?m=reports'); ?>">Reports</a><?php endif; ?>
            </nav>
        </header>
        <?php include(LEGACY_ROOT . '/modules/home/RecruitmentWarnings.tpl'); ?>
    </div>
</main>
</div>
<?php TemplateUtility::printFooter(); ?>
