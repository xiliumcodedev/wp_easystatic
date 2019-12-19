<?php

/*
This API is use to generate, edit, update, and remove a static HTML file 
*/

if (!defined( 'ABSPATH' ) ) {
   exit;
}

class WP_Easystatic_Controller extends WP_REST_Controller{
		
	private $generate;
	private $optimize;
	private $auth;

	function __construct($auth){

		$this->generate = new WP_Easystatic_Generate();
		$this->optimize = new WP_Easystatic_Optimize();
		$this->auth = $auth;
	}

	/*
		Register custom routes
	*/
	function register_routes() {
		register_rest_route( EASYSTASTIC_SLUG , 'request/ids/init', array(
		      array(
		        'methods'             => WP_REST_Server::READABLE,
		        'callback'            => array( $this, 'es_ids_generate' ),
		        'permission_callback' => function(){ return $this->auth; },
		        'args'                => array(),
		      ),
	      ));

		register_rest_route( EASYSTASTIC_SLUG , 'request/ids/init_update', array(
		      array(
		        'methods'             => WP_REST_Server::READABLE,
		        'callback'            => array( $this, 'es_init_update' ),
		        'permission_callback' => function(){ return $this->auth; },
		        'args'                => array(),
		      ),
	      ));
		
		register_rest_route( EASYSTASTIC_SLUG , 'request/ids/directories', array(
		      array(
		        'methods'             => WP_REST_Server::READABLE,
		        'callback'            => array( $this, 'es_scan_static' ),
		        'permission_callback' => function(){ return $this->auth; },
		        'args'                => array(),
		      ),
	      ));

		register_rest_route( EASYSTASTIC_SLUG , 'request/ids/read', array(
		      array(
		        'methods'             => WP_REST_Server::CREATABLE,
		        'callback'            => array( $this, 'es_ids_read' ),
		        'permission_callback' => function(){ return $this->auth; },
		        'args'                => array(
		        	'page_id' => array(
		        		'validate_callback' => function($param, $request, $key) {
				          	return is_numeric( $param );
				        }
		        	)
		        ),
		      ),
	      ));

		register_rest_route( EASYSTASTIC_SLUG , 'request/static/edit', array(
		      array(
		        'methods'             => WP_REST_Server::CREATABLE,
		        'callback'            => array( $this, 'es_static_edit' ),
		        'permission_callback' => function(){ return $this->auth; },
		        'args'                => array(
		        	'id' => array(
		        		'validate_callback' => function($param, $request, $key) {
				          	return is_numeric( $param );
				        }
		        	)
		        ),
		      ),
	      ));
		register_rest_route( EASYSTASTIC_SLUG , 'request/static/append', array(
		      array(
		        'methods'             => WP_REST_Server::CREATABLE,
		        'callback'            => array( $this, 'es_static_append' ),
		        'permission_callback' => function(){ return $this->auth; },
		        'args'                => array(
		        	'id' => array(
		        		'validate_callback' => function($param, $request, $key) {
				          	return is_numeric( $param );
				        }
		        	),
		        	'content' => array(
		        		'validate_callback' => function($param, $request, $key) {
				          	return !empty( $param );
				        }
		        	)
		        ),
		      ),
	      ));
		register_rest_route( EASYSTASTIC_SLUG , 'request/static/remove', array(
		      array(
		        'methods'             => WP_REST_Server::CREATABLE,
		        'callback'            => array( $this, 'es_static_remove' ),
		        'permission_callback' => function(){ return $this->auth; },
		        'args'                => array(
		        	'id' => array(
		        		'validate_callback' => function($param, $request, $key) {
				          	return is_numeric( $param );
				        }
		        	)
		        ),
		      ),
	      ));
		register_rest_route( EASYSTASTIC_SLUG , 'request/static/review', array(
		      array(
		        'methods'             => WP_REST_Server::CREATABLE,
		        'callback'            => array( $this, 'es_static_review' ),
		        'permission_callback' => function(){ return $this->auth; },
		        'args'                => array(
		        	'id' => array(
		        		'validate_callback' => function($param, $request, $key) {
				          	return is_numeric( $param );
				        }
		        	)
		        ),
		      ),
	      ));

		register_rest_route( EASYSTASTIC_SLUG , 'request/static/update', array(
		      array(
		        'methods'             => WP_REST_Server::CREATABLE,
		        'callback'            => array( $this, 'es_static_update' ),
		        'permission_callback' => function(){ return $this->auth; },
		        'args'                => array(
		        	'id' => array(
		        		'validate_callback' => function($param, $request, $key) {
				          	return is_numeric( $param );
				        }, 
				        ''
		        	),
		        	'content' => array(
		        		'validate_callback' => function($param, $request, $key) {
				          	return !empty( $param );
				        }
		        	)
		        ),
		      ),
	      ));

		register_rest_route( EASYSTASTIC_SLUG , 'request/static/enable', array(
		      array(
		        'methods'             => WP_REST_Server::CREATABLE,
		        'callback'            => array( $this, 'es_static_urls' ),
		        'permission_callback' => function(){ return $this->auth; },
		        'args'                => array(),
		      ),
	      ));

		register_rest_route( EASYSTASTIC_SLUG , 'request/static/disable', array(
		      array(
		        'methods'             => WP_REST_Server::CREATABLE,
		        'callback'            => array( $this, 'es_dynamic_urls' ),
		        'permission_callback' => function(){ return $this->auth; },
		        'args'                => array(),
		      ),
	      ));

		register_rest_route( EASYSTASTIC_SLUG , 'request/zip/create', array(
		      array(
		        'methods'             => WP_REST_Server::CREATABLE,
		        'callback'            => array( $this, 'es_create_zip' ),
		        'permission_callback' => function(){ return $this->auth; },
		        'args'                => array(),
		      ),
	      ));

		register_rest_route( EASYSTASTIC_SLUG , 'request/zip/restore', array(
		      array(
		        'methods'             => WP_REST_Server::CREATABLE,
		        'callback'            => array( $this, 'es_restore_zip' ),
		        'permission_callback' => function(){ return $this->auth; },
		        'args'                => array(),
		      ),
	      ));

		register_rest_route( EASYSTASTIC_SLUG , 'request/zip/remove', array(
		      array(
		        'methods'             => WP_REST_Server::CREATABLE,
		        'callback'            => array( $this, 'es_remove_zip' ),
		        'permission_callback' => function(){ return $this->auth; },
		        'args'                => array(),
		      ),
	      ));

		register_rest_route( EASYSTASTIC_SLUG , 'request/optimize/init', array(
		      array(
		        'methods'             => WP_REST_Server::READABLE,
		        'callback'            => array( $this, 'es_optimize' ),
		        'permission_callback' => function(){ return $this->auth; },
		        'args'                => array(),
		      ),
	      ));
	}

	function es_ids_generate(){
		$init = false;
		$field = WP_Easystatic_Utils::es_option_settings('static_page_field', null);
		$lists = $this->generate->es_init($field);

		if(!empty($lists)){
			$init = true;
		}

		return $this->generate->_server_response(
			[
			'ids' => $lists,
			'init' => $init
		]);
	}

	function es_scan_static(){

		$root = EASYSTATIC_BASE . '/' . WP_Easystatic_Utils::es_option_settings('static_directory', 'generate-files');
		$data = WP_Easystatic_Utils::es_list_directory($root);

		if(empty($data)){
			$data['error'] = new WP_Error(200, "You have an empty directory", true);
		}

		return $this->generate->_server_response($data);
	}

	function es_ids_read( $data ){
		$id = absint($data['page_id']);
		$post = WP_Easystatic_Utils::es_sanitize_post($id);
		$read = $this->generate->es_read($post);
		if(is_wp_error($read)){
			$response['error'] = $read;
		}
		else{
			update_option("wp_static_page", maybe_serialize(WP_Easystatic_Utils::es_options($post)));
			$link = $this->generate->es_static_subdirectory($post);
			$op_content = $this->optimize->wp_easystatic_jscss_buffer($read, $post);
			$this->generate->es_append_request(stripslashes_deep($op_content));
			$response = [
				'dir' => $link,
				'title' => $post->post_title
			];
		}
	
		return $this->generate->_server_response($response);
	}

	function es_init_update(){
		$init = false;
		$field = WP_Easystatic_Utils::es_option_settings('static_page_field', null);
		$lists = $this->generate->es_init($field, true);

		if(!empty($lists)){
			$init = true;
		}

		return $this->generate->_server_response(
			[
			'ids' => $lists,
			'init' => $init
		]);

	}

	function es_static_edit( $data ){
		$id = absint($data['id']);
		$post = WP_Easystatic_Utils::es_sanitize_post($id);
		
		if($post){
			$link = get_permalink($post->ID);
			$content = stripslashes_deep($this->generate->es_edit($post));
			$sanitize = $this->optimize->es_content_sanitize($content);
			$response = [
			'content' => $sanitize, 
			'link' => $link, 
			'title' => $post->post_title
			];
		}
		else{
			$response['error'] = new WP_Error(200, "Can't find post content", true);
		}

		return $this->generate->_server_response($response);
	}

	function es_static_append( $data ){
		$id = absint($data['id']);
		$post = WP_Easystatic_Utils::es_sanitize_post($id);
		$content = stripslashes_deep($this->generate->es_edit($post));

		//make sure content is clean if updating the static html file
		$clean_html = WP_Easystatic_Utils::es_safe_content($content, $data);
		$op_content = $this->optimize->minify_html( $clean_html, ['keepComments' => false, 'xhtml' => true]);

		if($this->generate->es_append($post, $op_content)){
			$response['content'] = $id;
		}
		else{
			$response['error'] = new WP_Error(200, "Can't save your post", true);
		}

		return $this->generate->_server_response($response);
	}

	function es_static_remove( $data ){
		$id = absint($data['id']);
		$static_page = (array) maybe_unserialize(WP_Easystatic_Utils::es_option_settings('wp_static_page', []));
		$post = WP_Easystatic_Utils::es_sanitize_post($id);
		$status = 200;

		if(array_key_exists($post->post_type, $static_page)){
			$filter = array_filter($static_page[$post->post_type], function($v, $k) use ($id){
				if($v != $id){
					return $v;
				}
			}, ARRAY_FILTER_USE_BOTH);

			$static_page[$post->post_type] = $filter;
			update_option('wp_static_page', maybe_serialize($static_page));
		}

		$response = [
			'id' => $id,
			'status' => false
		];

		if($this->generate->es_remove($post)){
			$response = [
				'id' => $id,
				'status' => true
			];
		}
		
		return $this->generate->_server_response($response);
	}

	function es_static_review( $data ){
		$id = absint($data['id']);
		$post = WP_Easystatic_Utils::es_sanitize_post($id);
		$static_content = $this->generate->es_edit($post);
		$dynamic_content = $this->generate->es_request_dynamic($post);
		$op_content = $this->optimize->wp_easystatic_jscss_buffer($dynamic_content, $post);
		$op_content = $this->optimize->es_format_html( $op_content );
		
		//sanitize the whole content in dynamic output and static html file
		$sanitize_dynamic = $this->optimize->es_content_sanitize($op_content);
		$sanitize_static = $this->optimize->es_content_sanitize($static_content);

		return $this->generate->_server_response([
			'status' => 1,
			'title' => $post->post_title,
			'sitecontent' => stripslashes_deep($sanitize_static),
			'editcontent' => stripslashes_deep($sanitize_dynamic)
		]);
	}

	function es_static_update( $data ){
		$id = absint($data['id']);
		$post = WP_Easystatic_Utils::es_sanitize_post($id);
		$content = stripslashes_deep($this->generate->es_edit($post));
		$clean_html = WP_Easystatic_Utils::es_safe_content($content, $data);
		$op_content = $this->optimize->minify_html($clean_html, ['keepComments' => false, 'xhtml' => true]);

		if($this->generate->es_static_update($post, $op_content)){
			$response = WP_Easystatic_Utils::_status_msg(1, "Successfully Updated");
		}
		else
		{
			$response = WP_Easystatic_Utils::_status_msg(0, "Invalid Content");
		}

		return $this->generate->_server_response($response);
	}

	function es_static_urls(){
		global $directory;
		$directory = $this->generate->es_static_basedir();
		$urls = WP_Easystatic_Utils::es_option_settings('static_exclude_url', []);

		if($urls){
			$urls = explode("\n", $urls);
		}
		
		if($this->generate->es_rewrite_static($urls)){
			$response = WP_Easystatic_Utils::_status_msg(1, "Successfully Updated");
		}
		else
		{
			$response = WP_Easystatic_Utils::_status_msg(0, "Invalid Content");
		}
		
		return $this->generate->_server_response($response);
	}

	function es_dynamic_urls(){

		if($this->generate->es_rewrite_dynamic()){
			$response = WP_Easystatic_Utils::_status_msg(1, "Successfully Updated");
		}
		else
		{
			$response = WP_Easystatic_Utils::_status_msg(0, "Invalid Content");
		}
		
		return $this->generate->_server_response($response);
	}

	function es_create_zip(){

		$directory = WP_Easystatic_Utils::es_option_settings('static_directory', 'generate-files');
		if($this->generate->create_zip_file(new ZipArchive(), $directory)){
			$response = WP_Easystatic_Utils::_status_msg(1, "Created new zip file");
		}
		else
		{
			$response = WP_Easystatic_Utils::_status_msg(0, "Invalid Content");
		}
		
		return $this->generate->_server_response($response);
	}

	function es_restore_zip(){
		$f = esc_url_raw(filter_input(INPUT_POST, 'url', FILTER_SANITIZE_URL));
		$r = str_replace(get_site_url('/'), EASYSTATIC_BASE, $f);
		$r = str_replace("/", DIRECTORY_SEPARATOR, $r);

		if($this->generate->restore_backup(new ZipArchive(), $r)){
			$response = WP_Easystatic_Utils::_status_msg(1, "Restore Backup file");
		}
		else
		{
			$response = WP_Easystatic_Utils::_status_msg(0, "Invalid Content");
		}

		return $this->generate->_server_response($response);
	}

	function es_remove_zip(){
		$f = esc_url_raw(filter_input(INPUT_POST, 'url', FILTER_SANITIZE_URL));
		$r = str_replace(get_site_url('/'), EASYSTATIC_BASE, $f);
		$r = str_replace("/", DIRECTORY_SEPARATOR, $r);
		
		if($this->generate->remove_backup($r)){
			$response = WP_Easystatic_Utils::_status_msg(1, "Removed Backup file");
		}
		else
		{
			$response = WP_Easystatic_Utils::_status_msg(0, "Invalid Content");
		}

		return $this->generate->_server_response($response);

	}
}

?>