<?php 

//vd($args);
?>
			<script id="sap-ui-bootstrap"
				src='<?php echo SITE_URL;?>assets/openui5/resources/sap-ui-core.js'
				data-sap-ui-theme="sap_belize"
				data-sap-ui-libs="sap.m,sap.ui.unified"
				data-sap-ui-compatVersion="edge"
				data-sap-ui-preload="async"
				data-sap-ui-resourceroots='{
					"sap.ui.demo.wt": "/ats/mod/excel/view/import"

				}'>
			</script>
			<script>
			sap.ui.getCore().attachInit(function () {
<?php if (isset($args['xmlViews']) && is_array($args['xmlViews'])) {foreach($args['xmlViews'] as $k =>$v) {?>				
				sap.ui.xmlview({
					viewContent: <?php echo $v['content'];?>,
					controller: <?php echo $v['controller']?>,			
				}).placeAt("<?php echo $v['placeAt'];?>");
<?php }}//foreach//if ?>
			});
			</script>	
