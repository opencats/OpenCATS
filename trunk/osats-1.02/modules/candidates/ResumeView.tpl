<!-- NOSPACEFILTER -->
<html>
    <head>
        <?php if (!empty($this->data)): ?>
            <title>
                <?php _e('Candidates');?> - <?php _e('Preview');?>
                <?php $this->_($this->data['firstName'] . ' ' . $this->data['lastName']); ?>
            </title>
        <?php else: ?>
            <title><?php _e('Candidates');?> - <?php _e('Preview');?> (<?php _e('Error');?>)</title>
        <?php endif; ?>
    </head>

    <body>
<?php if (!empty($this->data)): ?>

<pre style="font-size: 12px; padding: 5px;">
<?php echo($this->data['text']); ?>
</pre>

<?php else: ?>

<pre style="font-size: 12px; padding: 5px;">
<?php _e('Error');?>: <?php _e('No text exists for this attachment.');?>
</pre>

<?php endif; ?>

    </body>
</html>