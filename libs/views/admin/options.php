<div class="wrap">
<h2><?php echo $this->pluginDirName ;?></h2>

<form method="post" action="options.php">
<?php
	$string = '';
	if ( null !== $this->pluginOptions ){
		foreach( $this->pluginOptions as $group=>$options ){
			settings_fields( $group );
			$string .= '<table class="form-table">';
			if ( is_array( $options ) ){
				foreach($options as $optionName=>$option ){
					$value = get_option( sanitize_title( $optionName ) );
					$option = array_merge( self::$defautPluginOptions, $option );
					$string .= '<tr><td>'.__( $option['label'] , 'plugin-factory').'</td><td>';
					if ( $option['type'] == 'text' ){
						$string .= '<input type="text" value="'.(empty( $value )?esc_attr( $option['default'] ):esc_attr( $value )).'" name="'.sanitize_title( $optionName ).'" />';
					} elseif ( $option['type'] == 'radio' ){
						if ( is_array( $option['values'] ) ){
							foreach( $option['values'] as $val=>$name ){
								$string .= __( $name , 'plugin-factory').'<input'.( $value == $val ? ' checked="checked"':'' ).' type="radio" name="'.sanitize_title( $optionName ).'" value="'.$val.'" />';
							}
						}
					} elseif ( $option['type'] == 'checkbox' ){
						if ( is_array( $option['values'] ) ){
							foreach( $option['values'] as $val=>$name ){
								$string .= __( $name , 'plugin-factory').'<input'.( is_array( $value ) && in_array( $val, $value ) ? ' checked="checked"':'' ).' type="checkbox" name="'.sanitize_title( $optionName ).'[]" value="'.$val.'" />';
							}
						}
					} elseif ( $option['type'] == 'select' ){
						if ( is_array( $option['values'] ) ){
							$string .= '<select name="'.sanitize_title( $optionName ).'">';
							foreach( $option['values'] as $val=>$name ){
								$string .= '<option'.( $value == $val?' selected="selected"':'' ).' value="'.$val.'">'.__( $name , 'plugin-factory').'</option>';
							}
							$string .= '</select>';
						}
					}
					$string .= '</td></tr>';
				}
			}
			$string .= '</table>';
		}
	}
	echo $string;
?>
<p class="submit">
<input type="submit" class="button-primary" value="<?php _e('Save Changes', 'plugin-factory') ?>" />
</p>

</form>