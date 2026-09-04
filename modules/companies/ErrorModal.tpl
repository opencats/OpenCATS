<?php TemplateUtility::printModalHeader('Companies'); ?>

<main class="container-fluid p-2">
<h1 class="h5 fw-semibold mb-2">Companies: Error</h1>

<div class="alert alert-danger mb-0" role="alert">
<div class="fw-semibold mb-1">A fatal error has occurred.</div>
<?php echo $this->errorMessage; ?>
</div>
</main>

</body>
</html>
