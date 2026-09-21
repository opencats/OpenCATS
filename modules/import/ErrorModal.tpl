<?php TemplateUtility::printModalHeader('Import'); ?>
    <main class="container-fluid p-3">
        <h1 class="h5 fw-semibold">Import: Error</h1>
        <div class="alert alert-danger" role="alert">
            <p>A fatal error has occurred.</p>
            <?php echo($this->errorMessage); ?>
        </div>
    </main>
    </body>
</html>
