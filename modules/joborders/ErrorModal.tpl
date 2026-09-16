<?php TemplateUtility::printModalHeader('Job Orders'); ?>
<main class="container-fluid p-2 oc-joborder-errormodal">
    <section class="oc-page-header mb-2"><h1 class="h5 fw-semibold mb-0">Job Orders: Error</h1></section>

    <div class="alert alert-danger" role="alert">
        A fatal error has occurred.<br>
        <br>
        <?php echo($this->errorMessage); ?>
    </div>
</main>
</body>
</html>

