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
		$static = WP_Easystatic_Utils::es_list_directory($root);

		if(empty($static)){
			return new WP_Error("es_404", "You have an empty directory", true);
		}

		return $this->generate->_server_response($static);
	}

	function es_ids_read( $data ){
		$id = sanitize_text_field(esc_html($data['page_id'] ));
		$post = get_post($id);
		$content = stripslashes_deep($this->generate->es_read($post));
		if(empty($content)){
			return new WP_Error("es_404", "You have an empty content", true);
		}
		update_option("wp_static_page", maybe_serialize(WP_Easystatic_Utils::es_options($post)));
		$link = $this->generate->es_static_subdirectory($post);
		$op_content = $this->optimize->wp_easystatic_jscss_buffer($content, $post);
		$this->generate->es_append_request($op_content);

		return $this->generate->_server_response(
			[
			'dir' => $link,
			'title' => $post->post_title
		]);
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
		$id = sanitize_text_field(esc_html($data['id']));
		$post = get_post($id);
		
		if(!$post){
			return new WP_Error("es_404", "Can't find post content", true);
		}

		$link = get_permalink($post->ID);
		$content = stripslashes_deep($this->generate->es_edit($post));

		return $this->generate->_server_response(
			[
			'content' => $content, 
			'link' => $link, 
			'title' => $post->post_title
			]
		);
	}

	function es_static_append( $data ){
		$id = sanitize_text_field(esc_html($data['id']));
		$post = get_post($id);
		$content = stripslashes_deep($data['content']);
		if($this->generate->es_append($post, $content)){
			return $id;
		}
		return new WP_Error("es_404", "Can't save your post", true);
	}

	function es_static_remove( $data ){
		$id = sanitize_text_field(esc_html($data['id']));
		$post = get_post($id);
		$status = 200;

		$data = [
			'id' => $id,
			'status' => false
		];

		if($this->generate->es_remove($post)){
			$data = [
				'id' => $id,
				'status' => true
			];
		}
		
		return $this->generate->_server_response($data);
	}

	function es_static_review( $data ){
		$id = sanitize_text_field(esc_html($data['id']));
		$post = get_post($id);
		$static_content = $this->generate->es_edit($post);
		$dynamic_content = $this->generate->es_request_dynamic($post);
		$op_content = $this->optimize->wp_easystatic_jscss_buffer($dynamic_content, $post);

		return $this->generate->_server_response([
			'status' => 1,
			'title' => $post->post_title,
			'sitecontent' => stripslashes_deep($static_content),
			'editcontent' => stripslashes_deep($op_content)
		]);
	}

	function es_static_update( $data ){
		$content = stripslashes_deep($data['content']);
		$id = sanitize_text_field(esc_html($data['id']));
		$post = get_post($id);

		if($this->generate->es_static_update($post, $content)){
			$data = WP_Easystatic_Utils::_status_msg(1, "Successfully Updated");
		}
		else
		{
			$data = WP_Easystatic_Utils::_status_msg(0, "Invalid Content");
		}

		return $this->generate->_server_response($data);
	}

	function es_static_urls(){
		global $directory;
		$directory = $this->generate->es_static_basedir();
		$urls = WP_Easystatic_Utils::es_option_settings('static_exclude_url', false);

		if($urls){
			$urls = explode("\r\n", $urls);
		}

		if($this->generate->es_rewrite_static($urls)){
			$data = WP_Easystatic_Utils::_status_msg(1, "Successfully Updated");
		}
		else
		{
			$data = WP_Easystatic_Utils::_status_msg(0, "Invalid Content");
		}
		
		return $this->generate->_server_response($data);
	}

	function es_dynamic_urls(){

		if($this->generate->es_rewrite_dynamic()){
			$data = WP_Easystatic_Utils::_status_msg(1, "Successfully Updated");
		}
		else
		{
			$data = WP_Easystatic_Utils::_status_msg(0, "Invalid Content");
		}
		
		return $this->generate->_server_response($data);
	}

	function es_create_zip(){

		$directory = WP_Easystatic_Utils::es_option_settings('static_directory', 'generate-files');
		if($this->generate->create_zip_file(new ZipArchive(), $directory)){
			$data = WP_Easystatic_Utils::_status_msg(1, "Created new zip file");
		}
		else
		{
			$data = WP_Easystatic_Utils::_status_msg(0, "Invalid Content");
		}
		
		return $this->generate->_server_response($data);
	}

	function es_restore_zip(){
		$f = esc_url_raw($_POST['url']);
		$r = str_replace(get_site_url('/'), EASYSTATIC_BASE, $f);
		$r = str_replace("/", DIRECTORY_SEPARATOR, $r);

		if($this->generate->restore_backup(new ZipArchive(), $r)){
			$data = WP_Easystatic_Utils::_status_msg(1, "Restore Backup file");
		}
		else
		{
			$data = WP_Easystatic_Utils::_status_msg(0, "Invalid Content");
		}

		return $this->generate->_server_response($data);
	}

	function es_remove_zip(){
		$f = esc_url_raw($_POST['url']);
		$r = str_replace(get_site_url('/'), EASYSTATIC_BASE, $f);
		$r = str_replace("/", DIRECTORY_SEPARATOR, $r);
		
		if($this->generate->remove_backup($r)){
			$data = WP_Easystatic_Utils::_status_msg(1, "Removed Backup file");
		}
		else
		{
			$data = WP_Easystatic_Utils::_status_msg(0, "Invalid Content");
		}

		return $this->generate->_server_response($data);

	}
}

?>