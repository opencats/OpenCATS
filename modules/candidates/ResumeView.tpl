<!-- NOSPACEFILTER -->
<html>
    <head>
        <?php if (!empty($this->data)): ?>
            <title>
                <?php echo __("Candidates");?> - <?php echo __("Preview");?>
                <?php $this->_($this->data['firstName'] . ' ' . $this->data['lastName']); ?>
            </title>
        <?php else: ?>
            <title><?php echo __("Candidates");?> - <?php echo __("Preview");?> (<?php echo __("Error");?>)</title>
        <?php endif; ?>
    </head>

    <body>
<?php if (!empty($this->data)): ?>

<pre style="font-size: 12px; padding: 5px;">
<?php echo($this->data['text']); ?>
</pre>

<?php else: ?>

<pre style="font-size: 12px; padding: 5px;">
<?php echo __("Error: No text exists for this attachment.");?>
</pre>

<?php endif; ?>

    </body>
</html>
