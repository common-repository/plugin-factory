<?php
/*
Plugin Name: Plugin Factory
Plugin URI: http://fredpointzero.com/plugin-factory/
Description: Plugin Factory
Version: 0.1
Author: Frederic Vauchelles, Cathy Vauchelles
Author URI: http://fredpointzero.com
Text Domain: plugin-factory

  Copyright 2009 Plugin Factory  (email : fredpointzero@gmail.com)

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
*/
// Base plugin depedency
if ( !class_exists( 'basePlugin' ) ){
	$pluginFactoryPath = realpath( dirname( __FILE__ ).'/../plugin-factory/' );
	if ( !file_exists( $pluginFactoryPath ) )
		die( 'Plugin : plugin-factory is needed' );
	else
		require_once( $pluginFactoryPath.'/libs/basePlugin.php' );
}
class pluginFactory extends basePlugin{
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
	// Do not remove these comments, needed for updating
	//[EDIT-CLASS-BEGIN] Start editing here
	/**
	 *	Initialization of the plugin goes here
	 */
	public function initializePlugin(){
		$this->textDomain = 'plugin-factory';
	}
	
	/**
	 *	Allow user to use localization functions for your menu labels
	 *	@return null or a menu tree
	 *
	 * 	Default structure of a menu item
	 *	@param string type 		: submenu|menu|wordpressSubMenu
	 *	@param string name 		: name of the menu
	 *	@param string id 			: id of the menu item and filename of the view
	 *	@param string parent 	: id of the parent element (for wordpressSubMenu only) options|management|pages|posts|theme
	 */
	public function get_admin_tree(){
		// Admin menu generation
		return array(
			array(
				'type' => 'menu',
				'name' => __( 'Plugin Factory' , 'plugin-factory'),
				'id' => 'generateConf',
				'subMenus' => array(
					array(
						'type' => 'submenu',
						'name' => __( 'Generate Locale Files' , 'plugin-factory'),
						'id' => 'generatePOT'
					)
				)
			)
		);
		// End
	}
	// Pre render function
	public function pre_render_admin_generatePOT(){
		if ( !empty( $_POST['POTAction'] ) ){
			try {
				if ( $_POST['POTAction'] == 'generatePOT' ){
					if ( !empty( $_POST['plugin'] ) && $this->generatePOT( $_POST['plugin'] ) )
						$this->msg = __( 'POT file successfully generated' , 'plugin-factory');
					else
						$this->msg = __( 'POT file generation failure' , 'plugin-factory');
				} elseif ( $_POST['POTAction'] == 'addTextDomain' ){
					if ( !empty( $_POST['text-domain'] ) ){
						// Get domain processor
						$this->addTextDomain( $_POST['plugin'], $_POST['text-domain'] );
						$this->msg = __( 'Text domain successfully added' , 'plugin-factory');
					}
				} elseif ( $_POST['POTAction'] == 'generatePO' ){
					if ( !empty( $_POST['plugin'] ) && !empty( $_POST['language'] ) && $this->generatePO( $_POST['plugin'], $_POST['language'] ) )
						$this->msg = __( 'PO file successfully generated' , 'plugin-factory');
					else
						$this->msg = __( 'PO file generation failure' , 'plugin-factory');
				} elseif ( $_POST['POTAction'] == 'generateMO' ){
					if ( !empty( $_POST['plugin'] ) && $this->generateMO( $_POST['plugin'] ) )
						$this->msg = __( 'MO file(s) successfully generated' , 'plugin-factory');
					else
						$this->msg = __( 'MO file(s) generation failure' , 'plugin-factory');
				}
			} catch( PluginGenerationException $e ) {
				$this->msg = $e->getMessage();
			}
		}
	}
	/**
	 *	Generate MO files
	 *	@param string $pluginName : name of the plugin
	 *	@return bool : true one success, false on faliure
	 *	@throw PluginGenerationException
	 */
	private function generateMO( $pluginName ){
		if ( file_exists( ABSPATH.$this->pluginDir.'/POT/Generated/'.$pluginName.'/'.$pluginName.'.pot' ) ){
			$old_dir = getcwd();
			chdir( ABSPATH.$this->pluginDir.'/POT/Generated/'.$pluginName );
			$scannedDir = scandir( ABSPATH.$this->pluginDir.'/POT/Generated/'.$pluginName.'/' );
			$mo = array();
			foreach( $scannedDir as $file ){
				if ( preg_match( '/^(.*)\.po$/', $file, $matches ) )
					$mo[] = $matches[1];
			}
			$exit_code = 0;
			$output = array();
			foreach( $mo as $mofile ){
				exec(
					'msgfmt '.escapeshellarg( $mofile.'.po' ).' --output-file='.escapeshellarg( $mofile.'.mo' ),
					$output,
					$exit_codeTmp
				);
				$exit_code |= $exit_codeTmp;
			}
			if ( $exit_code != 0 )
				throw new PluginGenerationException( str_replace( '%i', $exit_code, __( 'Generation failure, exit code of msgfmt : %i' , 'plugin-factory') ) );
			chdir( $old_dir );
		} else
			throw new PluginGenerationException( str_replace( '%s', $pluginName, __( 'POT file was not generated for plugin : %s' , 'plugin-factory') ) );
			return true;
	}
	/**
	 *	Generate PO files
	 *	@param string $pluginName : name of the plugin
	 *	@param string $lang : language of the PO file
	 *	@return bool : true one success, false on failure
	 *	@throw PluginGenerationException
	 */
	private function generatePO( $pluginName, $lang ){
		if ( file_exists( ABSPATH.$this->pluginDir.'/POT/Generated/'.$pluginName.'/'.$pluginName.'.pot' ) ){
			$old_dir = getcwd();
			chdir( ABSPATH.$this->pluginDir.'/POT/Generated/'.$pluginName );
			$args = array(
				'locale' => $lang,
				'output-file' => $pluginName.'-'.$lang.'.po'
			);
			$argsString = '';
			foreach( $args as $name => $value )
				$argsString .= ' --'.$name.'='.escapeshellarg( $value );
			system(
				'msginit'.$argsString,
				$exit_code
			);
			if ( $exit_code != 0 )
				throw new PluginGenerationException( str_replace( '%i', $exit_code, __( 'Generation failure, exit code of msginit : %i' , 'plugin-factory') ) );
			chdir( $old_dir );
		} else
			throw new PluginGenerationException( str_replace( '%s', $pluginName, __( 'POT file was not generated for plugin : %s' , 'plugin-factory') ) );
			return true;
	}
	/**
	 *	Add text domain to plugins files
	 *	@param string $pluginName : name of the plugin
	 *	@param string $textDomain : text domain to add
	 */
	private function addTextDomain( $pluginName, $textDomain ){
		require_once( ABSPATH.$this->pluginDir.'/POT/tools/add-textdomain.php' );
		$domainProcessor = new AddTextdomain();
		// Get files to process
		$files =  $this->get_files_from_dir( 'wp-content/plugins/'.$pluginName);
		foreach( $files as $file )
			$domainProcessor->process_file( $textDomain, ABSPATH.$file, true );
	}
	/**
	 *	Generate a POT file for a plugin
	 *	@param string $pluginName : name of the plugin
	 *	@return true on success, false on failure
	 */
	private function generatePOT( $pluginName ){
		// Get pot maker
		require_once( ABSPATH.$this->pluginDir.'/POT/tools/makepot.php' );
		$potmaker = new MakePOT();
		if ( !file_exists( ABSPATH.$this->pluginDir.'/POT/Generated/'.$pluginName ) )
			mkdir( ABSPATH.$this->pluginDir.'/POT/Generated/'.$pluginName );
		return $potmaker->wp_plugin( ABSPATH.'wp-content/plugins/'.$pluginName, ABSPATH.$this->pluginDir.'/POT/Generated/'.$pluginName.'/'.$pluginName.'.pot' );
	}
	public function pre_render_admin_generateConf(){
		if ( empty( $_POST['confSelected'] ) )
			$this->confValues = array();
		else{
			if ( !empty( $_POST['selectConfAction'] ) && $_POST['selectConfAction'] == 'generatePlugin' ){
				try{
					$this->generatePlugin( $_POST['confSelected'] );
					$this->pluginGenerationMsg =__( 'Plugin generation success' , 'plugin-factory');
				} catch( PluginGenerationException $e ){
					$this->pluginGenerationMsg = str_replace( '%s', $e->getMessage(), __( 'Plugin generation failure : %s' , 'plugin-factory') );
				}
			}
			$this->confValues = parse_ini_file( ABSPATH.$this->pluginDir.'/conf/'.$_POST['confSelected'], true );
		}
		if ( !empty( $_POST['confAction'] ) ){
			if ( $_POST['confAction'] == 'generateConf' ){
				if ( !empty( $_POST['conf'] ) && !empty( $_POST['conf']['other']['directory'] ) && !empty( $_POST['conf']['plugin']['name'] ) && !empty( $_POST['conf']['plugin']['className'] ) ){
					write_ini_file( $_POST['conf'], ABSPATH.$this->pluginDir.'/conf/'.$_POST['conf']['other']['directory'].'.conf', true );
					$this->confValues = $_POST['conf'];
					$_POST['conf']['other']['directory'] = trim( $_POST['conf']['other']['directory'] );
					$_POST['confSelected'] = $_POST['conf']['other']['directory'].'.conf';
				} else {
					$this->pluginGenerationMsg = __( 'Missing fields to generate conf file : plugin > name, className and other > directory' );
				}
			}
		}
		$this->confTemplate = parse_ini_file( ABSPATH.$this->pluginDir.'/conf/template/template.conf', true );
		$this->possibleConfs = $this->getConfs();
	}
	/**
	 *	@param string $confFileName : file name of the plugin conf to generate
	 *	@return bool : true on success
	 *	@throw PluginGenerationException
	 */
	private function generatePlugin( $confFileName ){
		if ( file_exists( ABSPATH.$this->pluginDir.'/conf/'.$confFileName ) ) {
			$conf = parse_ini_file( ABSPATH.$this->pluginDir.'/conf/'.$confFileName );
			if ( empty( $conf['directory'] ) || empty( $conf['name'] ) || empty( $conf['className'] ) )
				throw new PluginGenerationException( __( 'Directory, className or name field have not been properly filled' , 'plugin-factory') );
			if ( !file_exists( ABSPATH.'wp-content/plugins' ) ){
				if ( is_writable( ABSPATH.'wp-content/plugins' ) )
					mkdir( ABSPATH.'wp-content/plugins/'.$conf['directory'] );
				else
				throw new PluginGenerationException( __( 'Plugin directory is not writable' , 'plugin-factory') );
			}
			$this->generateDir( ABSPATH.'wp-content/plugins/'.$conf['directory'], ABSPATH.$this->pluginDir.'/pluginTemplate', $conf );
		} else
			throw new PluginGenerationException( str_replace( '%s', ABSPATH.$this->pluginDir.'/conf/'.$confFileName, __( 'Conf file does not exist (%s)' , 'plugin-factory') ) );
	}
	/**
	 *	Transpose file under $templateDir to $targetDir
	 *	@param string $targetDir : absolute directory of target dir
	 *	@param string $templateDir : absolute directory of template dir to transpose
	 *	@param array $conf : pair of key/values to translate in files
	 *	@return bool : true on success or error msg
	 *	@throw PluginGenerationException
	 */
	private function generateDir( $targetDir, $templateDir, $conf ){
		$scannedDir = scandir( $templateDir );
		foreach( $scannedDir as $file ){
			if ( is_file( $templateDir.'/'.$file ) && $templateDir.'/'.is_readable( $file ) ){
				$filename = $file;
				// rename main plugin file
				if ( $file == 'plugin.php' )
					$filename = $conf['name'].'.php';
				$this->generateFile( $targetDir, $filename, $templateDir.'/'.$file, $conf );
			}
			elseif ( is_dir( $templateDir.'/'.$file ) ){ 
				if ( $file != '.' && $file != '..' ){
					if ( !file_exists( $targetDir.'/'.$file ) )
						mkdir( $targetDir.'/'.$file );
					$this->generateDir( $targetDir.'/'.$file, $templateDir.'/'.$file, $conf );
				}
			} else
				throw new PluginGenerationException( str_replace( '%s', $templateDir.'/'.$file, __( 'Plugin template directory contains incorrect files : %s' , 'plugin-factory') ) );
		}
	}
	/**
	 *	Transpose the template file to the target directory
	 *	(Update file if it was modified)
	 *	@param string $targetDir : target directory
	 *	@param string $filename : name of the file
	 *	@param string $templateFile : path to the template file
	 *	@param array $conf : pair of key/values to translate in files
	 *	@throw PluginGenerationException
	 */
	private function generateFile( $targetDir, $filename, $templateFile, $conf ){
		if ( is_writable( $targetDir ) && is_readable( $templateFile ) ){
			$templateContent = file_get_contents( $templateFile );
			$contentProcessed = str_replace(
				array_map(
					array(
						'pluginFactory',
						'prepareKeys'
					),
					array_keys(
						$conf
					)
				),
				array_values(
					$conf
				),
				$templateContent
			);
			// Update if file exists
			if ( file_exists( $targetDir.'/'.$filename ) ){
				$fileContent = $this->getContent( file_get_contents( $targetDir.'/'.$filename ) );
				$contentProcessed = $this->setContent( $contentProcessed, $fileContent );
			}
			file_put_contents(
				$targetDir.'/'.$filename,
				$contentProcessed,
				FILE_TEXT
			);
		} else
			throw new PluginGenerationException( str_replace( array( '%s1', '%s2' ), array( $templateFile, $targetDir), __( 'Template file is not readable(%s1) or target directory is not writable (%s2)' , 'plugin-factory') ) );
	}
	/**
	 *	Get content inside [EDIT-*-BEGIN] [EDIT-*-END]
	 *	@param string $content : content to parse
	 *	@return array : key is name of the categorie, value is the content
	 */
	private function getContent( $content ){
		if ( preg_match_all( '!\[EDIT\-([^\-]+)\-BEGIN\]!', $content, $matches ) ){
			$result = array();
			foreach( $matches[1] as $cat ){
				preg_match( '!\[EDIT\-'.$cat.'\-BEGIN\](.*)\[EDIT\-'.$cat.'\-END\]!ms', $content, $match );
				$result[$cat] = $match[1];
			}
			return $result;
		} else
			return array();
	}
	/**
	 *	Set content inside [EDIT-*-BEGIN] [EDIT-*-END]
	 *	@param string $content : content to change
	 *	@param array $changes : changes to apply (keys are categories, values are contents)
	 */
	private function setContent( $content, array $changes ){
		foreach( $changes as $cat => $change ){
			$content = preg_replace( '!\[EDIT\-'.$cat.'\-BEGIN\](.*)\[EDIT\-'.$cat.'\-END\]!ms', '[EDIT-'.$cat.'-BEGIN]'.$change.'[EDIT-'.$cat.'-END]', $content );
		}
		return $content;
	}
	private static function prepareKeys( $e ){
		return '%'.$e;
	}
	/**
	 *	@return all selectable confs
	 */
	private function getConfs(){
		$scannedDir = scandir( ABSPATH.$this->pluginDir.'/conf' );
		$confs = array();
		foreach( $scannedDir as $file ){
			if ( is_file( ABSPATH.$this->pluginDir.'/conf/'.$file ) && preg_match( '/^(\w+)\.conf$/', $file ) ){
				$confs[] = $file;
			}
		}
		return $confs;
	}
	/**
	 *	@param dir to inspect
	 *	@return array : php writable file path relative to ABSPATH
	 */
	private function get_files_from_dir( $dir ) {
		$result = array();
		$dirname = dirname( ABSPATH.$dir );
		$scannedDir = scandir( ABSPATH.$dir );
		if ( is_array( $scannedDir ) ) {
			foreach( $scannedDir as $file ){
				// Avoid . and ..
				if ( !( $file == '.' || $file == '..') ){
					$absFile = ABSPATH.$dir.'/'.$file;
					// Get php writable files and dir
					if ( is_file( $absFile ) && is_writable( $absFile ) && preg_match( '/^.*\.php$/', $file) ){
						$result[] = $dir.'/'.$file;
					}
					elseif ( is_dir( $absFile ) )
						$result = array_merge_recursive( $result, $this->get_files_from_dir( $dir.'/'.$file ) ) ;
				}
			}
		}
		return $result;
	}
	
	/**
	 *	Define plugin options
	 */
	/*public $pluginOptions = array(
		'plugin-factory-options' =>array(
			'textTest' => array(
				'type' => 'text',
				'default' => 'testing',
				'label' => 'textTest'
			),
			'radioTest' => array(
				'type' => 'radio',
				'values' => array(
					0 => '0',
					1 => '1'
				),
				'default' => 0,
				'label' => 'radioTest'
			),
			'checkboxTest' => array(
				'type' => 'checkbox',
				'values' => array(
					0 => '0',
					1 => '1',
					2 => '2'
				),
				'default' => array(
					0,1
				),
				'label' => 'checkboxTest'
			),
			'selectTest' => array(
				'type' => 'select',
				'values' => array(
					0 => '0',
					1 => '1',
					2 => '2'
				),
				'default' => 0,
				'label' => 'selectTest'
			)
		)
	);*/
	// Actions
	/**
	 *	Callback for init action
	 *	public function action_init(){
		
	 }
	 */
	public function action_admin_init(){
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'jquery-ui-tabs' );
		wp_enqueue_style(
			'jquery-ui',
			get_bloginfo('wpurl').'/'.$this->pluginDir.'/css/ui-theme/ui.all.css'
		);
		wp_enqueue_style(
			'plugin-factory.admin',
			get_bloginfo('wpurl').'/'.$this->pluginDir.'/css/admin.css'
		);
	}
	/**
	 *	Callback for the_content filter hook
	 *	public function filter_the_content($e){
		return 'test'.$e;
	}
	 */
	// End
	
	//[EDIT-CLASS-END] End editing here
}
/**
 *	End of your plugin
 */
pluginFactory::getInstance();
//[EDIT-OTHER-BEGIN]
class PluginGenerationException extends Exception {
	
}
//[EDIT-OTHER-END]
?>