<?php
/**
 *	This class is designed to help developpers to make their plugins.
 *	It has its own rendering engine : to render a file, just type myPlugin::getInstance()->render_dirname_myfile();
 *	And it will include the file located in /views/dirname/myfile.php. In this file you can access to you plugin instance with $this.
 *	So you can assign datas to your plugin before rendering the view.
 *	@class pluginSingle
 */
class basePlugin {
	public $pluginDirName = "";
	public $pluginDir = "";
	public $pluginFactoryDir = "";
	protected $textDomain = null;
	protected $pluginOptions = null;
	protected $wpdb = null;
	protected $className = __CLASS__;
	private static $instance = array();
	
	// Singleton pattern
	protected function __construct(){
		// Get db connector
		global $wpdb;
		$this->wpdb = $wpdb;
		$this->pluginLibraryDir = preg_replace( '!.*[/\\\](wp-content[/\\\])!', '$1', realpath( dirname( __FILE__ ).'/../' ) );
		
		// Check for Hooks
		if ( method_exists( $this, 'action_activation' ) )
			register_activation_hook( __FILE__, array( $this, 'action_activation' ) );
		$is_admin = is_admin();
		foreach ( get_class_methods( $this ) as $method ){
			if ( preg_match( '/^action_(\w+)$/', $method, $matches ) )
				add_action( $matches[1], array( $this, $method ) );
			elseif ( preg_match( '/^filter_(\w+)$/', $method, $matches ) )
				add_filter( $matches[1], array( $this, $method ) );
		}
		add_action( 'admin_init', array( $this, '__admin_init' ) );
		add_action( 'admin_menu', array( $this, '__admin_menu' ) );
		add_action( 'init', array( $this, '__init' ) );
	}
	private function __clone(){}
	public static function getInstance($class){
		if ( empty( self::$instance[$class] ) ){
			self::$instance[$class] = new $class;
		}
		return self::$instance[$class];
	}
	// End
	
	// Magic call
	public function __call( $name, array $arguments=null ){
		// Rendering
		if ( preg_match( '/^render_(\w+)_(.*)$/', $name, $matches ) ){
			$fileFound = false;
			$file = '';
			if ( file_exists( ABSPATH.$this->pluginDir.'/views/'.$matches[1].'/'.$matches[2].'.php' ) ){
				$file = ABSPATH.$this->pluginDir.'/views/'.$matches[1].'/'.$matches[2].'.php';
				$fileFound = true;
			} elseif ( file_exists( dirname( __FILE__ ).'/views/'.$matches[1].'/'.$matches[2].'.php' ) ){
				$file = dirname( __FILE__ ).'/views/'.$matches[1].'/'.$matches[2].'.php';
				$fileFound = true;
			}
			if ( $fileFound ){
				if ( method_exists( $this, 'pre_render_'.$matches[1].'_'.$matches[2] ) )
					$this->{'pre_render_'.$matches[1].'_'.$matches[2]}();
				include ( $file );
			}
			else{
				$this->viewFile = array( $matches[1], $matches[2] );
				include ( dirname( __FILE__ ).'/views/default/notFound.php' );
			}
		}
		// End
	}
	// End
	
	// Admin generation
	/** Default structure of a menu item
	 *	@param string type 		: submenu|menu
	 *	@param string name 		: name of the menu
	 *	@param string id 			: id of the menu item and filename of the view
	 *	@param string parent 	: id of the parent element (for submenu only) options|management|pages|posts|theme
	 */
	private static $default_admin_menu = array(
		'type' => 'submenu',
		'name' => 'default',
		'id' => 'default',
		'subMenus' => array(),
		'parent' => 'options'
	);
	public function get_admin_tree(){
		return null;
	}
	/**
	 *	Menu generator
	 */
	public function __admin_menu(){
		$admin_tree = $this->get_admin_tree();
		if ( null !== $admin_tree ){
			foreach( $admin_tree as $menu ){
				$menu = array_merge( self::$default_admin_menu, $menu );
				if ( $menu['type'] == 'menu' ){
					add_menu_page( $menu['name'], $menu['name'], 8, __FILE__, array( $this, 'render_admin_'.$menu['id'] ) );
					foreach( $menu['subMenus'] as $submenu ){
						$submenu = array_merge( self::$default_admin_menu, $submenu );
						add_submenu_page(__FILE__, $submenu['name'], $submenu['name'], 8, $submenu['id'], array( $this, 'render_admin_'.$submenu['id'] ) );
					}
				}
				elseif ( $menu['type'] == 'wordpressSubMenu' ){
					call_user_func(
						'add_'.$menu['parent'].'_page',
						$menu['name'],
						$menu['name'],
						8,
						$menu['id'],
						array( $this, 'render_admin_'.$menu['id'] )
					);
				}
			}
		}
	}
	/**
	 *	Load locale files
	 */
	public function __init(){
		load_plugin_textdomain( empty( $this->textDomain ) ? $this->className : $this->textDomain, null, $this->pluginDirName.'/lang' );
	}
	/**
	 *	Register options
	 */
	public function __admin_init(){
		if ( null !== $this->pluginOptions && is_array( $this->pluginOptions ) ){
			foreach( $this->pluginOptions as $group=>$options ){
				if ( is_array( $options ) ){
					foreach( $options as $optionName=>$optionValues ){
						add_option( sanitize_title( $optionName ) );
						register_setting( $group, sanitize_title( $optionName ) );
					}
				}
			}
		}
	}
	protected static $defautPluginOptions = array(
		'type' => 'text',
		'values' => array(),
		'default' => 'default',
		'label' => 'default'
	);
	// End
	// Data assignation (for views rendering)
	// Magick get
	public function __get( $name ){
		if ( empty( $this->pluginDatas[$name] ) )
			return null;
		else
			return $this->pluginDatas[$name];
	}
	// Magick isset
	public function __isset( $name ){
		return !empty( $this->pluginDatas[$name] );
	}
	// Magick get
	public function __set( $name, $value ){
		$this->pluginDatas[$name] = $value;
	}
	private $pluginDatas = array();
	// End
}

// Taken from php.net on parse_ini_file topics (comment of DDRKhat on 19-Jun-2009 09:11)
if (!function_exists('write_ini_file')) { 
	function write_ini_file($assoc_arr, $path, $has_sections=FALSE) { 
		$content = ""; 

		if ($has_sections) { 
			foreach ($assoc_arr as $key=>$elem) { 
				$content .= "[".$key."]\n"; 
				foreach ($elem as $key2=>$elem2) 
				{ 
					if(is_array($elem2)) 
					{ 
						for($i=0;$i<count($elem2);$i++) 
						{ 
							$content .= $key2."[] = \"".$elem2[$i]."\"\n"; 
						} 
					} 
					else if($elem2=="") $content .= $key2." = \n"; 
					else $content .= $key2." = \"".$elem2."\"\n"; 
				} 
			} 
		} 
		else { 
			foreach ($assoc_arr as $key=>$elem) { 
				if(is_array($elem)) 
				{ 
					for($i=0;$i<count($elem);$i++) 
					{ 
						$content .= $key2."[] = \"".$elem[$i]."\"\n"; 
					} 
				} 
				else if($elem=="") $content .= $key2." = \n"; 
				else $content .= $key2." = \"".$elem."\"\n"; 
			} 
		} 

		if (!$handle = fopen($path, 'w')) { 
				return false; 
		} 
		if (!fwrite($handle, $content)) { 
				return false; 
		} 
		fclose($handle); 
		return true; 
	} 
}
// End