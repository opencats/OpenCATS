<?php if (!$this->modal): ?>
<?php TemplateUtility::printHeader('Support'); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active); ?>
<?php TemplateUtility::printQuickSearch(); ?>
<main id="main" class="container-fluid py-2">
    <div id="contents">
<?php endif; ?>
        <div class="alert alert-danger m-2" role="alert">
            <h1 class="h5 fw-semibold"><?php echo $this->errorTitle; ?></h1>
            <div>
                <?php echo $this->errorMessage; ?>
                <?php if ($this->isDemo): ?>
                <br /><br />
                You are logged in as a <b>demo account.</b> Demo accounts
                have several restrictions in place because of their inherent anonymity.
                You may wish to sign up for a CATS Hosted account -- it's free,
                and none of the demo restrictions are in place. To sign up, <a href="?a=getcats">click here</a>!
                <?php endif; ?>
                <?php
                eval(Hooks::get('FRIENDLYERRORS_CONTACTCATS'));
                ?>
            </div>
        </div>
<?php if (!$this->modal): ?>
    </div>
</main>
<?php TemplateUtility::printFooter(); ?>
<?php endif; ?>
