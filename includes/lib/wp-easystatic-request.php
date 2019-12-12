<?php

/*
	Abstract class for static generator
*/

if (!defined( 'ABSPATH' ) ) {
   exit;
}

abstract class WP_Easystatic_Request implements WP_Easystatic_Impl{
	
	protected $static_file;
	protected $basedir;
	protected $subdirectory;

	function __construct(){
		$this->basedir = WP_Easystatic_Utils::es_option_settings('static_directory', 'generate-files');
	}

	/**
	* initialize page/post to retrieve the id
	* @return array id
	*/

	function es_init($field = [], $is_update = false){
		
		$lists = [];
		if(!$is_update){
			foreach($field as $post_type){
				WP_Easystatic_Utils::es_pages($lists, $post_type);
			}
		}
		else{
			foreach($field as $post_type){
				WP_Easystatic_Utils::es_lists_ids($lists, $post_type);
			}
		}

		return $lists;
	}


	/**
	* Get the url and convert to static directory
	* @param wp_post
	* @return string
	*/

	function es_static_subdirectory($post = ""){
		$link = get_permalink($post->ID);
		$this->subdirectory = str_replace(get_site_url(), '', $link);
		return $this->subdirectory;
	}

	/**
	* @return static base directory
	*/
	function es_static_basedir(){
		return $this->basedir;
	}

	/**
	* Check if static HTML directory is exist
	* @return bool
	*/
	function es_file_exists(){
		return file_exists(EASYSTATIC_BASE . DIRECTORY_SEPARATOR . $this->basedir . $this->subdirectory);
	}

	/**
	* Open the generated HTML file
	* @param directory, filename
	* @return string
	*/
	function es_file_open($dir, $file){

		if (!file_exists(EASYSTATIC_BASE . DIRECTORY_SEPARATOR . $dir . $file)) {
			mkdir(EASYSTATIC_BASE . DIRECTORY_SEPARATOR . $dir . $file, 0777, true);
		}

		$this->static_file = fopen(EASYSTATIC_BASE . DIRECTORY_SEPARATOR . $dir . $file . 'index.html', "w");

	}

	/**
	* Open the generated HTML file and overwrite
	* @param static file content
	* @return void
	*/
	function es_file_write($content){

		if(!$this->static_file){
			return false;
		}

		fwrite($this->static_file, $content);

	}

	/**
	* Close the file request
	* @return false if fail
	*/
	function es_file_close(){
		if(!$this->static_file){
			return false;
		}

		if(fclose($this->static_file)){
			return [];
		}
	}

	/*
		remove the static HTML file and its directory
	*/
	function es_file_remove(){
		unlink(EASYSTATIC_BASE . DIRECTORY_SEPARATOR .  $this->basedir . $this->subdirectory . 'index.html');
		rmdir(EASYSTATIC_BASE . DIRECTORY_SEPARATOR . $this->basedir . $this->subdirectory);
	}

	/*
		Overwrite the HTML file content
	*/
	function es_append_request($content){
		$this->es_file_open($this->basedir, $this->subdirectory);
		$this->es_file_write($content);
		$this->es_file_close();
	}

	/*
		wrapper to retrieve HTML directory and write in HTACESS
	*/
	function static_rewrite_ht(&$static_mod = "", $exclude = []){
		global $directory;

		$root = EASYSTATIC_BASE . '/' . $this->basedir;
		$dirs = WP_Easystatic_Utils::es_list_directory($root);
		if(!empty($dirs)){
			$static_mod .= WP_Easystatic_Utils::es_write_staticmod($dirs, $exclude);
		}
	}

	/*
		Check if static redirect is present in HTACESS
	*/
	function es_static_is_enable(){
		$file = fopen(EASYSTATIC_BASE . DIRECTORY_SEPARATOR . '.htaccess', "r");
		$static_amper = false;
		$static_rule = false;
		while(!feof($file)){
			$read = fgets($file);
			if(preg_match("/begin(.*static)\b/i", $read)){
				$static_amper = true;
			}

			if($static_amper && preg_match("/RewriteRule(.*\^\^)\b/i", $read)){
				$static_rule = true;
			}	
		}

		if(fclose($file)){
			return $static_rule;
		}
	}

	/*
		wrapper for server response
	*/
	function _server_response( $data, $options = []){
		
		$status = 200;
		$headers = ['Content-Type' => 'application/json'];

		if(array_key_exists('error', $data)){
			$status = $data['error']->get_error_code();
			$data['error'] = $data['error']->get_error_message( $status );
		}

		if(array_key_exists("status", $options)){
			$status = $options['status'];
		}

		if(array_key_exists('headers', $options)){
			$headers = $options['headers'];
		}

		return new WP_REST_Response(
			$data,
			$status,
			$headers
		);
	}
	
}