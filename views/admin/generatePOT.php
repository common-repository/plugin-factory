<div class="wrap">
<h2><?php echo $this->pluginDirName ;?></h2>
<h3><?php _e( 'Generate POT Files' , 'plugin-factory');?></h3>
<script>
jQuery(function($){
	var action = $("#POTAction");
	$("#pluginName").change(function(){
		$("#pluginTextDomain").val($(this).val());
	});
	$("#addTextDomainButton").click(function(){
		action.val("addTextDomain");
	});
	$("#generatePOTButton").click(function(){
		action.val("generatePOT");
	});
	$("#generatePOButton").click(function(){
		action.val("generatePO");
	});
	$("#generateMOButton").click(function(){
		action.val("generateMO");
	});
})(jQuery);
</script>
<?php
	if ( isset( $this->msg ) )
		echo '<div class="updated fade">'.$this->msg.'</div>';
?>
<form id="POTActionForm" method="post" action="#">
	<input type="hidden" name="POTAction" id="POTAction" value=""/>
	<table class="conf form-table">
		<tr>
			<td><?php _e( 'Select plugin' , 'plugin-factory');?></td>
			<td>
				<select id="pluginName" name="plugin">
					<?php
						$plugin = empty( $_POST['plugin'] ) ? '' : $_POST['plugin'];
						$string = '';
						$scannedDir = scandir( ABSPATH.'/wp-content/plugins/' );
						$dir = array();
						foreach( $scannedDir as $pluginDir ){
							if ( !preg_match( '/\./', $pluginDir ) ){
								$dir[] = $pluginDir;
							}
						}
						foreach( $dir as $pluginDir )
							$string .= '<option'.( $plugin == $pluginDir ? ' selected="selected"' : '').' value="'.$pluginDir.'">'.$pluginDir.'</option>';
						echo $string;
					?>
				</select>
			</td>
		</tr>
		<tr>
			<td><?php _e( 'Text domain' , 'plugin-factory');?></td>
			<td><input id="pluginTextDomain" type="text" name="text-domain" value="<?php echo empty( $_POST['text-domain'] ) ? '' : $_POST['text-domain'];?>" /></td>
		</tr>
		<tr>
			<td><?php _e( 'Language' , 'plugin-factory');?></td>
			<td><input id="language" type="text" name="language" value="<?php echo empty( $_POST['language'] ) ? WPLANG : $_POST['language'];?>" /></td>
		</tr>
	</table>
	<div class="submit">
		<button id="addTextDomainButton" style="display:block;float:left;" class="button-primary"><?php _e('Add text domain', 'plugin-factory') ?></button>
		<button id="generatePOTButton" style="display:block;float:left;" class="button-primary"><?php _e('Generate POT', 'plugin-factory') ?></button>
		<button id="generatePOButton" style="display:block;float:left;" class="button-primary"><?php _e('Generate PO', 'plugin-factory') ?></button>
		<button id="generateMOButton" style="display:block;float:left;" class="button-primary"><?php _e('Generate MO', 'plugin-factory') ?></button>
		<div style="clear:both;"></div>
	</div>
</form>