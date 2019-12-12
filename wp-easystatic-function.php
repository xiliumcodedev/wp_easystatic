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
		register_deactivation_hook( EASYSTASTIC_FILE, array($this, 'es_deactivation') );
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
		add_filter('admin_enqueue_scripts',array($this, 'easystatic_style'));
		add_action('admin_footer', array($this, 'easystatic_script_footer'));
		add_filter('script_loader_tag', array($this, 'es_requirejs_init'), 10, 3);
		add_filter('pre_update_option_static_directory', array($this, 'easystatic_preupdate_option'), 50, 3);
		add_filter('pre_update_option_static_critical_css', array($this, 'es_optimize_update'), 50, 3);
		add_action('admin_menu', array($this, 'easystatic_dashboard'));
		add_action('admin_notices', array($this, 'easystatic_admin_notice_template'));
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
			EASYSTATIC_CONTENT_DIR . DIRECTORY_SEPARATOR . "cache" . DIRECTORY_SEPARATOR . "easystatic",
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

	function es_deactivation(){
		if(!class_exists('WP_Easystatic_Generate')){
			return;
		}

		$generate = $this->component->get_es_generate();

		if($generate instanceof WP_Easystatic_Generate){
			$generate->es_rewrite_dynamic();
		}
	}

	/*
		include easystatic style
	*/
	function easystatic_style(){
		global $current_screen;

		if($current_screen->id == "settings_page_" . EASYSTASTIC_SLUG){
			wp_enqueue_style( 'codemirror-css', plugins_url( 'scripts/codemirror/styles/css/codemirror.css' , __FILE__ ), array(), '0.1' );
			wp_enqueue_style( 'codematerial-css', plugins_url( 'scripts/codemirror/styles/css/material.css' , __FILE__ ), array(), '0.1' );
			wp_enqueue_style( 'cmmerge-css', plugins_url( 'scripts/codemirror/styles/css/merge.css' , __FILE__ ), array(), '0.1' );
			wp_enqueue_style( 'es-style-css', plugins_url( 'assets/es-main-style.css' , __FILE__ ), array(), '0.1' );
		}
	}

	/*
		include easystatic js scripts
	*/
	function easystatic_script_footer(){
		global $current_screen;

		if($current_screen->id == "settings_page_" . EASYSTASTIC_SLUG){

			$panel = WP_Easystatic_Utils::es_panelurl_tab();
			$tab = trim(sanitize_key( (isset($panel['tab']) ? $panel['tab'] : '') ));
			switch ($tab) {
				case 'static':
					wp_enqueue_script( 'wp_easystatic-require-js', plugins_url( 'scripts/require.js' , __FILE__ ), array(), '0.1' );
					wp_localize_script( 'wp_easystatic-require-js', 'wp_easystatic', array('url' => site_url(), 'baseUrl' => plugins_url('scripts', __FILE__), 'tab' => 'static', 'slug' => EASYSTASTIC_SLUG
		        	));
					break;
				case 'backup' :
					wp_enqueue_script( 'wp_easystatic-backup-require-js', plugins_url( 'scripts/require.js' , __FILE__ ), array(), '0.1' );
					wp_localize_script( 'wp_easystatic-backup-require-js', 'wp_easystatic', array('url' => site_url(), 'baseUrl' => plugins_url('scripts', __FILE__), 'tab' => 'backup', 'slug' => EASYSTASTIC_SLUG
		        	));
		        	break;
				default:
					wp_enqueue_script( 'wp_easystatic-activate-js', plugins_url( 'scripts/wp-es-activate.js' , __FILE__ ), array(), '0.1' );
					wp_localize_script( 'wp_easystatic-activate-js', 'wp_easystatic', array('url' => site_url(), 'slug' => EASYSTASTIC_SLUG
		        	));
					break;
			}
		}
	}

	/*
		load all js script via requiresjs configuation
	*/
	function es_requirejs_init($tag, $handle, $src){
		switch ($handle) {
			case 'wp_easystatic-require-js':
				$tag = '<script type="text/javascript" data-main="'. EASYSTATIC_URL .'scripts/config" src="' . esc_url( $src ) . '" ></script>';
				break;
			case 'wp_easystatic-backup-require-js':
				$tag = '<script type="text/javascript" data-main="'. EASYSTATIC_URL .'scripts/tab-backup-config" src="' . esc_url( $src ) . '" ></script>';
				break;
			default:
				break;

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
				'post_type' => get_post_types(
					['public' => true]
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


		register_setting( 'wp_easystatic', 'static_directory', 
		['sanitize_callback' => 'WP_Easystatic_Utils::es_sanitize_settings']);

		register_setting( 'wp_easystatic', 'static_page_field', 
		['sanitize_callback' => 'WP_Easystatic_Utils::es_sanitize_settings']);

		register_setting( 'wp_easystatic', 'static_exclude_url', 
		['sanitize_callback' => 'WP_Easystatic_Utils::es_sanitize_settings']);

		register_setting( 'static_minify', 'static_minify_css',
		['sanitize_callback' => 'WP_Easystatic_Utils::es_sanitize_settings']);

		register_setting( 'static_minify', 'static_critical_enable',
		['sanitize_callback' => 'WP_Easystatic_Utils::es_sanitize_settings']);
		
		register_setting( 'static_minify', 'static_exclude_css',
		['sanitize_callback' => 'WP_Easystatic_Utils::es_sanitize_settings']);

		register_setting( 'static_minify', 'static_critical_css',
		['sanitize_callback' => 'WP_Easystatic_Utils::es_sanitize_settings']);

		register_setting( 'static_minify', 'static_minify_js',
		['sanitize_callback' => 'WP_Easystatic_Utils::es_sanitize_settings']);

		register_setting( 'static_minify', 'static_exclude_js',
		['sanitize_callback' => 'WP_Easystatic_Utils::es_sanitize_settings']);

		register_setting( 'static_minify', 'static_minify_html',
		['sanitize_callback' => 'WP_Easystatic_Utils::es_sanitize_settings']);

		//import function for backup file
		$this->component->static_zip_upload();
	}

	
	// setting field view section
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

		$panel = WP_Easystatic_Utils::es_panelurl_tab();
		$template['template'] = '/includes/wp-easystatic-page.php';
		$template['tab'] = sanitize_key((isset($panel['tab']) ? trim($panel['tab']) : ''));
		$template['static_enable'] = $this->component->static_redirect_code(new WP_Easystatic_Generate());
		$template['total_static'] = $this->component->count_static_generated(0);
		$template['total_unstatic'] = $this->component->count_unstatic(0);
		$template['menu_tab'] = $this->component->_tab_menu();
		$template['general_tab'] = $this->component->_tab_general_view();
		$template['static_tab'] = $this->component->_tab_static_view();
		$template['optimize_tab'] = $this->component->_tpl_tab_optimize();
		$template['backup_tab'] = $this->component->_tpl_tab_backup();
		$template['export_import_tab'] = $this->component->_tpl_tab_import();

		$this->component->easystatic_template($template);

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
		add_options_page(__('wp-easystatic', 'easystatic'), __('Easystatic', 'easystatic'), 'edit_theme_options', 
	    	EASYSTASTIC_SLUG ,  array($this, 'init_template'));
	}

	function es_remove_notice(){
		if(WP_Easystatic_Utils::es_filter_input(INPUT_GET, 'page') === 'easystatic'){
			WP_Easystatic_Utils::es_remove_admin_notice();
		}
	}

	/*
		Additional admin notice
	*/
	function easystatic_admin_notice_template(){
		if(!class_exists('WP_Easystatic_Template')){
			return;
		}

		$template = $this->component->get_es_template();

		if($template instanceof WP_Easystatic_Template){
			$template->es_dashboard_notice();
		}
	}

	/*
		check static directory field
	*/
	function easystatic_preupdate_option($values, $option_name, $old){

		$setting = 'easystatic_notice';
		$setting_code = esc_attr( 'settings_updated_notice' );
		$setting_msg = __("General Settings saved.", "easystatic");
		$setting_type = "updated";
		$setting_isvalid = false;

		if(absint($values)){

			$setting = 'easystatic_error';
			$setting_code = esc_attr( 'settings_updated' );
			$setting_msg = __("Your value in static directory field must be a string", "easystatic");
			$setting_type = "error";
			$setting_isvalid = false;

		}

		else if(empty($values)){

			$setting = 'easystatic_error';
			$setting_code = esc_attr( 'settings_updated' );
			$setting_msg = __("Your value in static directory field must not be empty", "easystatic");
			$setting_type = "error";
			$setting_isvalid = false;

		}

		else if(strpos($values, ",")){

			$setting = 'easystatic_error';
			$setting_code = esc_attr( 'settings_updated' );
			$setting_msg = __("Your value in static directory field must have a valid string", "easystatic");
			$setting_type = "error";
			$setting_isvalid = false;

		}

		if(get_option($option_name) == false && $setting_isvalid){

			add_option($option_name, esc_html($values));
		}
		else{

			update_option($option_name, esc_html($values));
		
		}
		
		add_settings_error($setting, $setting_code, $setting_msg, $setting_type);

		return $values;
	}

	function es_optimize_update($values, $option_name, $old){

		$option = get_option($option_name);
		$setting = 'easystatic_notice';
		$setting_code = esc_attr( 'settings_updated_notice' );
		$setting_msg = __("Settings saved and cache is empty.", "easystatic");
		$setting_type = "updated";

		if($option == false){
		
			add_option($option_name, strip_tags(stripslashes($value)));
		
		}
		else{

			update_option($option_name, strip_tags(stripslashes($value)));
		
		}

		if(WP_Easystatic_Utils::es_filter_input(INPUT_POST, 'clear_cache')){
			$cache_path = EASYSTATIC_BASE . DIRECTORY_SEPARATOR . "wp-content" . DIRECTORY_SEPARATOR . "/cache/" . EASYSTASTIC_SLUG;
			if(file_exists($cache_path)){
				$dirs = array('css', 'js');
				foreach($dirs as $dir){
				$files = scandir($cache_path . DIRECTORY_SEPARATOR . $dir);
					foreach($files as $file){
						preg_match('/.*(\.)+$/', $file, $match, PREG_OFFSET_CAPTURE);
						if(!$match){
							unlink($cache_path . DIRECTORY_SEPARATOR . $dir . DIRECTORY_SEPARATOR . $file);
						}
					}
				}
			}
			
			add_settings_error($setting, $setting_code, $setting_msg, $setting_type);
		}
	}

}