      <!-- Polyfill(s) for older browsers -->
    <script src="<?php echo $ngSystemRoot;?>node_modules/core-js/client/shim.min.js"></script>
    <script src="<?php echo $ngSystemRoot;?>node_modules/zone.js/dist/zone.js"></script>
    <script src="<?php echo $ngSystemRoot;?>node_modules/systemjs/dist/system.src.js"></script>

    <script src="<?php echo $ngAppRoot;?>systemjs.config.php.js"></script>
    <script>
      System.import('<?php echo $ngAppRoot;?>main.php.js').catch(function(err){ console.error(err); });
    </script>