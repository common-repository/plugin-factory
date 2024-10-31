<div class="wrap">
<h2><?php echo $this->pluginDirName ;?></h2>
<h3><?php _e( 'Generate Conf files' , 'plugin-factory');?></h3>
<script>
jQuery(function($){
	var action = $("#selectConfAction");
	$("#selectConf").click(function(){
		action.val("selectConf");
	});
	$("#generatePlugin").click(function(){
		action.val("generatePlugin");
	});
	$("#confTabs").tabs();
})(jQuery);
</script>
<?php
	if ( !empty( $this->pluginGenerationMsg ) )
		echo '<div class="updated fade">'.$this->pluginGenerationMsg.'</div>';
?>
<form method="post" action="#">
	<input type="hidden" name="selectConfAction" id="selectConfAction" value=""/>
	<table class="conf form-table">
		<tr>
			<td><?php _e( 'Choose a conf' , 'plugin-factory');?></td>
			<td>
				<select name="confSelected">
					<option value=""><?php _e( 'New' , 'plugin-factory');?></option>
					<?php
						$currentConf = empty( $_POST['confSelected'] ) ? '' : $_POST['confSelected'];
						$string = '';
						foreach( $this->possibleConfs as $conf ){
							$string .= '<option'.( $currentConf == $conf ? ' selected="selected"':'').' value="'.$conf.'">'.$conf.'</option>';
						}
						echo $string;
					?>
				</select>
			</td>
			<td><button id="selectConf" class="button-secondary"><?php _e( 'Choose' , 'plugin-factory');?></button></td>
		</tr>
		<tr>
			<td><?php _e( 'Current conf :' , 'plugin-factory');?></td>
			<td><?php echo $currentConf;?></td>
			<td><button id="generatePlugin" style="display:block;float:left;" class="button-primary"><?php _e('Generate Plugin', 'plugin-factory') ?></button></td>
		</tr>
	</table>
</form>
<form id="confActionForm" method="post" action="#">
	<input type="hidden" name="confAction" id="confAction" value="generateConf"/>
		<?php
			$string = '';
			$titles = array();
			$contents = array();
			foreach( $this->confTemplate as $group=>$options ){
				$titles[] = __( $group , 'plugin-factory');
				$tmp = '<table class="conf form-table">
				<thead>
					<tr>
						<th>'.__( 'Field name' , 'plugin-factory').'</th>
						<th>'.__( 'Field value' , 'plugin-factory').'</th>
						<th>'.__( 'Field description' , 'plugin-factory').'</th>
					</tr>
				</thead>
				<tbody>';
				foreach( $options as $name=>$value ){
					$tmp .= '<tr>
	<td>'.__( $name , 'plugin-factory').'</td>
	<td>';
					$value = preg_replace( array( '!^\"(.*)\"$!', '!\\\!' ), array( '', '"' ), $value );
					$options = json_decode( $value );
					if ($options instanceof stdClass ){
						if ( $options->type == 'text' )
							$tmp .= '<input type="text" name="conf['.$group.']['.$name.']" value="'.( empty( $this->confValues[$group][$name] ) ? '' : esc_attr( $this->confValues[$group][$name] ) ).'" />';
						elseif ( $options->type == 'textarea' )
							$tmp .= '<textarea name="conf['.$group.']['.$name.']">'.( empty( $this->confValues[$group][$name] ) ? '' : esc_attr( $this->confValues[$group][$name] ) ).'</textarea>';
					}
					$tmp .= '</td><td>'.( empty( $options->description ) ? '' : __( $options->description , 'plugin-factory') ).'</td>
</tr>';
				}
				$tmp .= '</tbody></table>';
				$contents[] = $tmp;
			}
			$string .= '<div id="confTabs"><ul>';
			$count = 0;
			foreach( $titles as $title )
				$string .= '<li><a href="#tabs-'.($count++).'">'.$title.'</a></li>';
			$string .= '</ul>';
			$count = 0;
			foreach( $contents as $tabContent )
				$string .= '<div id="tabs-'.($count++).'">'.$tabContent.'</div>';
			$string .= '</div>';
			echo $string;
		?>
	<div class="submit">
		<button id="generateConf" style="display:block;float:left;" class="button-primary"><?php _e('Generate Conf', 'plugin-factory') ?></button>
		<div style="clear:both;"></div>
	</div>
</form>