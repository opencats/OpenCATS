<!-- NOSPACEFILTER -->
<!doctype html>
<html lang="en">
    <head>
        <link rel="stylesheet" href="vendor/twbs/bootstrap/dist/css/bootstrap.min.css">
        <meta charset="<?php echo HTML_ENCODING; ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <?php if (!empty($this->data)): ?>
            <title>
                Candidates - Preview
                <?php $this->_($this->data['firstName'] . ' ' . $this->data['lastName']); ?>
            </title>
        <?php else: ?>
            <title>Candidates - Preview (Error)</title>
        <?php endif; ?>
    </head>

    <body class="bg-body">
<main class="container-fluid p-2 oc-candidate-resume-page">
<?php if (!empty($this->data)): ?>

<pre class="small mb-0">
<?php echo($this->data['text']); ?>
</pre>

<?php else: ?>

<pre class="small mb-0">
Error: No text exists for this attachment.
</pre>

<?php endif; ?>

    </main>
    </body>
</html>
