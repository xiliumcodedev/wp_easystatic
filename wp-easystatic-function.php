<?php

if (!defined( 'ABSPATH' ) ) {
   exit;
}

DEFINE('EASYSTATIC_DIR', dirname(__FILE__));
DEFINE('EASYSTATIC_URL', plugin_dir_url(__FILE__));
DEFINE('EASYSTATIC_CONTENT', WP_CONTENT_URL);
DEFINE('EASYSTATIC_CONTENT_DIR', WP_CONTENT_DIR);
DEFINE('EASYSTATIC_BASE', ABSPATH);

class WP_Easystatic_Function{
	
	private $component; 

	static function instance(){
		return new self();
	}

	function __construct(){
		add_action( 'plugins_loaded', array($this, 'easystatic_load'));
		add_filter( 'plugin_action_links_' . plugin_basename( EASYSTASTIC_FILE ), 'WP_Easystatic_Utils::es_plugin_link');
		add_action('save_post', array($this, 'wp_easystatic_update_static'), 10, 3);
		register_activation_hook( EASYSTASTIC_FILE, array($this, 'es_onactivation'));
	}

	/*
		load all the functions and scripts
	*/
	function easystatic_load(){

		include_once EASYSTATIC_DIR . '/includes/thirdparty/Utils.php';
		include_once EASYSTATIC_DIR . '/includes/thirdparty/Colors.php';
		include_once EASYSTATIC_DIR . '/includes/thirdparty/Minifier.php';
		include_once EASYSTATIC_DIR . '/includes/thirdparty/JS/jsmin.php';
		include_once EASYSTATIC_DIR . '/includes/thirdparty/HTML/minify-html.php';

		$this->component = new WP_Easystatic_Components();

		add_action('admin_init', array($this, 'init_settings' ), 10);
		add_action('easystatic_page', array($this, 'init_template'));
		add_filter('admin_enqueue_scripts',array($this, 'easystatic_script'));
		add_action('admin_footer', array($this, 'easystatic_script_footer'));
		add_filter('script_loader_tag', array($this, 'es_requirejs_init'), 10, 3);
		add_action('admin_menu', array($this, 'easystatic_dashboard'));
		add_action( 'rest_api_init', function () {
			$is_admin = WP_Easystatic_Utils::get_user_auth(wp_get_current_user());
			$cntrlr = new WP_Easystatic_Controller($is_admin);
			$cntrlr->register_routes();
		});
	}

	/*
		needs to create directories on activation
	*/
	function es_onactivation(){
		$dirs = array(
			EASYSTATIC_CONTENT_DIR . DIRECTORY_SEPARATOR . "static-backup",
			EASYSTATIC_CONTENT_DIR . DIRECTORY_SEPARATOR . "cache" . DIRECTORY_SEPARATOR . "wp-easystatic",
			EASYSTATIC_BASE . DIRECTORY_SEPARATOR . WP_Easystatic_Utils::es_option_settings('static_directory', 'generate-files')
		);

		foreach($dirs as $dir){
			if(!file_exists($dir)){
				mkdir($dir);
			}
		}

		if(!get_option("wp_static_page")){
			add_option("wp_static_page", maybe_serialize([]));
		}

		if(!get_option("wp_static_version")){
			add_option("wp_static_version", EASYSTATIC_PLUGIN_VERSION);
		}
	}

	function easystatic_script(){
		
		$page = isset($_GET['page']) ? $_GET['page'] : false;

		if($page == EASYSTASTIC_SLUG){
			wp_enqueue_style( 'codemirror-css', plugins_url( 'scripts/codemirror/styles/css/codemirror.css' , __FILE__ ), array(), '0.1' );
			wp_enqueue_style( 'codematerial-css', plugins_url( 'scripts/codemirror/styles/css/material.css' , __FILE__ ), array(), '0.1' );
			wp_enqueue_style( 'cmmerge-css', plugins_url( 'scripts/codemirror/styles/css/merge.css' , __FILE__ ), array(), '0.1' );
			wp_enqueue_style( 'es-style-css', plugins_url( 'assets/es-main-style.css' , __FILE__ ), array(), '0.1' );
		}
	}

	function easystatic_script_footer(){
		
		$page = isset($_GET['page']) ? $_GET['page'] : false;

		if($page == EASYSTASTIC_SLUG){

			$tab = isset($_GET['tab']) ? $_GET['tab'] : false;

			if($tab == "static"){
				wp_enqueue_script( 'wp_easystatic-require-js', plugins_url( 'scripts/require.js' , __FILE__ ), array(), '0.1' );

				wp_localize_script( 'wp_easystatic-require-js', 'wp_easystatic', array(
	                'url' => site_url(),
	                'baseUrl' => plugins_url('scripts', __FILE__),
	                'tab' => 'static',
	                'slug' => EASYSTASTIC_SLUG
	        	));
			}
			else if($tab == "backup"){
				wp_enqueue_script( 'wp_easystatic-backup-require-js', plugins_url( 'scripts/require.js' , __FILE__ ), array(), '0.1' );

				wp_localize_script( 'wp_easystatic-backup-require-js', 'wp_easystatic', array(
	                'url' => site_url(),
	                'baseUrl' => plugins_url('scripts', __FILE__),
	                'tab' => 'backup',
	                'slug' => EASYSTASTIC_SLUG
	        	));
			}
			else{
				wp_enqueue_script( 'wp_easystatic-activate-js', plugins_url( 'scripts/wp-es-activate.js' , __FILE__ ), array(), '0.1' );
				wp_localize_script( 'wp_easystatic-activate-js', 'wp_easystatic', array(
	                'url' => site_url(),
	                'slug' => EASYSTASTIC_SLUG
	        	));
			}
		}
	}

	/*
		load all js script via requiresjs configuation
	*/
	function es_requirejs_init($tag, $handle, $src){

		if($handle == 'wp_easystatic-require-js'){
			$tag = '<script type="text/javascript" data-main="'.plugin_dir_url(__FILE__).'scripts/config" src="' . esc_url( $src ) . '" ></script>';
		}else if($handle == 'wp_easystatic-backup-require-js'){
			$tag = '<script type="text/javascript" data-main="'.plugin_dir_url(__FILE__).'scripts/tab-backup-config" src="' . esc_url( $src ) . '" ></script>';
		}

		return $tag;
	}

	/*
		register option settings
	*/
	function init_settings(){

		$this->component->conf_setting(
			(object) [
				'group' => 'wp_easystatic',
				'section' => 'static_setting_section',
				'title' => '',
				'callback' => array($this, 'setting_field_section')
			]
		);

		$this->component->section_setting();

		$this->component->setting_field( 
			'static_directory_field',
			'Set Static Directory',
			array(
				'field' => 'static_directory',
				'id' => 'static_directory'
		));

		$this->component->setting_field( 
			'static_generate_field',
			'Convert to static file',
			array(
				'field' => 'static_page_field',
				'id' => 'static_page_field',
				'type' => array(
					'page',
					'post',
					'post_type'
			)
		));


		$this->component->setting_field( 
			'static_exclude_url',
			'Exclude static url',
			array(
				'field' => 'static_exclude_url',
				'id' => 'static_exclude_url'
		));


		$this->component->conf_setting(
			(object) [
				'group' => 'static_minify',
				'section' => 'static_setting_section',
				'title' => '',
				'callback' => array($this, 'setting_field_section')
			]
		);

		$this->component->section_setting();

		$this->component->setting_field( 
			'static_minify_css',
			'Minify CSS',
			array(
				'field' => 'static_minify_css',
				'id' => 'static_minify_css'
		));


		$this->component->setting_field( 
			'static_exclude_css',
			'Exclude CSS URL',
			array(
				'field' => 'static_exclude_css',
				'id' => 'static_exclude_css'
		));

		$this->component->setting_field( 
			'static_critical_enable',
			'Inline Critical CSS',
			array(
				'field' => 'static_critical_enable',
				'id' => 'static_critical_enable'
		));

		$this->component->setting_field( 
			'static_critical_css',
			'',
			array(
				'field' => 'static_critical_css',
				'id' => 'static_critical_css'
		));

		$this->component->setting_field( 
			'static_minify_js',
			'Minify JS',
			array(
				'field' => 'static_minify_js',
				'id' => 'static_minify_js'
		));

		$this->component->setting_field( 
			'static_exclude_js',
			'Exclude JS URL',
			array(
				'field' => 'static_exclude_js',
				'id' => 'static_exclude_js'
		));

		$this->component->setting_field( 
			'static_minify_html',
			'Minify HTML',
			array(
				'field' => 'static_minify_html',
				'id' => 'static_minify_html'
		));


		register_setting( 'wp_easystatic', 'static_directory');

		register_setting( 'wp_easystatic', 'static_page_field');

		register_setting( 'wp_easystatic', 'static_exclude_url');

		register_setting( 'static_minify', 'static_minify_css');

		register_setting( 'static_minify', 'static_critical_enable');
		
		register_setting( 'static_minify', 'static_exclude_css');

		register_setting( 'static_minify', 'static_critical_css');

		register_setting( 'static_minify', 'static_minify_js');

		register_setting( 'static_minify', 'static_exclude_js');

		register_setting( 'static_minify', 'static_minify_html');

	
	}

	function setting_field_section( $param ){

		if(!array_key_exists('id', $param)){
			return false;
		}

		$this->component->setting_section_opt($param);

	}

	/*
		main plugin page
	*/
	function init_template(){
		
		$tab = isset($_GET['tab']) ? $_GET['tab'] : "general";

		$menu_tab = $this->component->_tab_menu();
		$general_tab = $this->component->_tab_general_view();
		$static_tab = $this->component->_tab_static_view();
		$import_tab = $this->component->_tpl_tab_import();
		$backup = $this->component->_tpl_tab_backup();
		$optimize = $this->component->_tpl_tab_optimize();

		$static_enable = $this->component->static_redirect_code(new WP_Easystatic_Generate());
		$total_static = $this->component->count_static_generated();
		$total_unstatic = $this->component->count_unstatic();

		$data = [
			'template' => '/includes/wp-easystatic-page.php',
			'tab' => $tab,
			'menu_tab' => $menu_tab,
			'option_tmpl' => $general_tab,
			'static_tab' => $static_tab,
			'static_enable' => $static_enable,
			'total_static' => $total_static,
			'optimize_tab' => $optimize,
			'backup' => $backup,
			'export_import_tab' => $import_tab,
			'total_unstatic' => $total_unstatic
		];

		$this->component->easystatic_template($data);

	}

	/*
		directly update the static file from page update
	*/
	function wp_easystatic_update_static($post_id, $post, $is_update){
			
		if ( wp_is_post_revision( $post_id ) ) {
        	return;
        }
        
        $root = EASYSTATIC_BASE . '/' . WP_Easystatic_Utils::es_option_settings('static_directory', 'generate-files');

        $subdirs = WP_Easystatic_Utils::es_list_directory($root);

        $post_url = get_permalink( $post_id );

        $content = WP_Easystatic_Utils::es_get_sitecontent($post_url);

        $generate = new WP_Easystatic_Generate();

        $optimize = new WP_Easystatic_Optimize();

        $link = $generate->es_static_subdirectory($post);

        if(!in_array($link, $subdirs)){
        	return;
        }

        $op_content = $optimize->wp_easystatic_jscss_buffer($content, $post);

        $generate->es_append_request($op_content);
	}

	/*
		Add the menu from admin dashboard
	*/
 	function easystatic_dashboard(){
		add_menu_page(__('wp-easystatic', 'easystatic'), __('Easystatic', 'easystatic'), 'edit_theme_options', 
	    	EASYSTASTIC_SLUG ,  array($this, 'init_template'));
	}

}