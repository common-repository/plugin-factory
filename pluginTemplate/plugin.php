<?php
/*
Plugin Name: %name
Plugin URI: %URI
Description: %shortDescription
Version: %stable
Author: %contributors
Author URI: %authorURI
Text Domain: %textDomain

  Copyright 2009 %name  (email : %authorMail)

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
	
	Based on plugin-factory of Frederic Vauchelles (fredpointzero@gmail.com)
*/
// Base plugin depedency
if ( !class_exists( 'basePlugin' ) ){
	$pluginFactoryPath = realpath( dirname( __FILE__ ).'/../plugin-factory/' );
	if ( !file_exists( $pluginFactoryPath ) )
		die( 'Plugin : plugin-factory is needed' );
	else
		require_once( $pluginFactoryPath.'/libs/basePlugin.php' );
}
class %className extends basePlugin{
	// Compatibility with parent
	public static function getInstance(){ return parent::getInstance( __CLASS__ ); }
	public function __construct(){
		parent::__construct();
		$this->initializePlugin();
		$this->className = __CLASS__;
		// Set plugin directory
		if ( preg_match( '!wp-content[/\\\]plugins[/\\\]([^/\\\]+)!', dirname( __FILE__ ), $matches ) ){
			$this->pluginDirName = $matches[1];
			$this->pluginDir = 'wp-content/plugins/'.$this->pluginDirName;
		} else {
			$this->pluginDirName = 'plugins';
			$this->pluginDir = 'wp-content/'.$this->pluginDirName;
		}
	}
	// End
	//test
	// Start editing here
	//[EDIT-CLASS-BEGIN] 
	//[EDIT-CLASS-END] End editing here
}
/**
 *	End of your plugin
 */
%className::getInstance();
//[EDIT-OTHER-BEGIN]
//[EDIT-OTHER-END]
?>