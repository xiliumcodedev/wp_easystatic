<?php
/*
	Utilities of component and generator
*/
class WP_Easystatic_Utils{

/*
	Retrieve all the HTML folder generated
*/
static function es_list_directory($root = ""){
	global $scansub;
	
	if(!file_exists($root)){
		return;
	}

	$subdir = [];
	
	if(file_exists($root . DIRECTORY_SEPARATOR . 'index.html')){
		$subdir[] = '';
	}
	
	$dir = scandir($root);

	foreach($dir as $d){
		preg_match('/.*(\.)+$/', $d, $match, PREG_OFFSET_CAPTURE);
		if(is_dir($root . DIRECTORY_SEPARATOR . $d) && !$match){
			$scansub = function($sub) use (&$subdir, $root){
				global $scansub;
				$scan = scandir($sub);					
				$subdir[] = str_replace($root, '', $sub);
				foreach($scan as $s){
					preg_match('/(\.)$/', $s, $submatch, PREG_OFFSET_CAPTURE);
					if(is_dir($sub . DIRECTORY_SEPARATOR . $s) && !$submatch){
						$scansub($sub . DIRECTORY_SEPARATOR . $s);
					}
				}
			};

			$scansub($root . DIRECTORY_SEPARATOR . $d);
		}
	}

	return $subdir;
}

/*
	Check if zip file exist in easystatic backup directory
*/
static function es_check_zip_exists($dir, $filename){

	if(!file_exists($dir)){
		return false;
	}

	$scans = scandir($dir);
	$files = [];
	foreach($scans as $scan){
		if($scan != "." && $scan != '..' && $scan != "..."){
			preg_match('/[$filename]/', $scan, $match, PREG_OFFSET_CAPTURE);
			if($match){
				$files[] = $scan;
			}
		}
	}
	return $files;
}

/*
	wrapper for extracting HTML file and move to static directory
*/
static function es_extracting_zip($file){
	$directory = WP_Easystatic_Utils::es_option_settings('static_directory', 'generate-files');
	$zip = new ZipArchive();
	
	if(!$zip->open($file)){
		return false;
	}
	$zip->extractTo(EASYSTATIC_BASE . DIRECTORY_SEPARATOR . $directory);
	$zip->close();
	return true;
}

/*
	wrapper for CURL request
*/
static function es_get_sitecontent($url){
	$response = wp_remote_get($url);
	
	if(empty($response)){
		return false;
	}

	$status = wp_remote_retrieve_response_code($response);
	$header = wp_remote_retrieve_header($response, 'content-type');
	$body = wp_remote_retrieve_body($response);

	$content = ['content' => '', 'error' => ''];
	switch ($status) {
		case 301:
			$content['error'] = 'Resource was moved permanently';
			break;
		case 302:
			$content['error'] = 'Resource was moved temporarily';
			break;
		case 403:
			$content['error'] = 'Forbidden – Usually due to an invalid authentication';
			break;
		case 404:
			$content['error'] = 'Resource not found: ' . $url;
			break;
		case 500:
			$content['error'] = 'Internal server error';
			break;
		case 503:
			$content['error'] = 'Service unavailable';
			break;
		default:
			$content['content'] = $body;
			break;
	}
	
	if(!empty($content['error'])){
		$content['content'] = "<html><head></head><body>".$content['error']."</body></html>";
	}

	return $content['content'];
}


/*
	Get pages, post, and registered post
*/
static function es_pages(&$lists = [], $post_type = 'page'){
	$pages = get_posts([
		'post_type' => $post_type,
		'posts_per_page' => -1,
		'post_status' => 'publish'
	]);
	
	$list = array_map(function($v){
		return absint($v->ID);
	}, $pages);

	$list = wp_parse_id_list($list);
	
	$opt = (array) maybe_unserialize(WP_Easystatic_Utils::es_option_settings("wp_static_page", []));

	if(array_key_exists($post_type, $opt)){
		$list = array_diff($list, $opt[$post_type]);
	}
	
	return array_splice($lists, count($lists) - 1, count($list), $list);
}

/*
	Retrieve all scanned pages, post, and registered post
*/
static function es_lists_ids(&$lists = [], $post_type = 'page'){

	$opt = (array) maybe_unserialize(WP_Easystatic_Utils::es_option_settings("wp_static_page", []));
	
	if(!is_array($opt))
		return false;

	$list = [];
	if(array_key_exists($post_type, $opt)){
		$list = array_merge($lists, $opt[$post_type]);
	}

	return array_splice($lists, count($lists) - 1, count($list), $list);
}

// sanitize settings field
static function es_sanitize_settings( $opt ){
	foreach( $opt as $k => $v ) {
        if( isset( $opt[$k] ) ) {
            $opt[$k] = strip_tags(stripslashes($opt[$k]));
        }
	}
	return $opt;
}

// sanitize post data
static function es_sanitize_post( $args ){

	$post = sanitize_post( get_post( $args ), 'OBJECT', 'raw' );

	if(is_wp_error($post)){
		return false;
	}

	return $post;

}

/*
	content is cleaned before when updating the static html file
*/
static function es_safe_content( $content, $data = [] ){

	$content_split = explode("</head>", $content, 2);

	$scripts = [];
	if (preg_match_all( '#<script.*</script>#Usmi', $content_split[1], $matches)){
		foreach ( $matches[0] as $tag ) {
			$scripts[] = $tag;
		}
	}

	$content = stripslashes_deep(wp_filter_post_kses($data['content']));

	//returning all scripts into footer
	foreach($scripts as $script){
		$content .= $script . "\n\r";
	}

	preg_match("/\<body (.*)([^>])/", $content_split[1], $body);

	$content = current($content_split) . "</head>" . current($body) . $content . "</body></html>";

	return $content;
}

static function es_filter_input( $type, $name ){
	return filter_input( $type, $name, FILTER_UNSAFE_RAW) || null;
}

static function es_remove_admin_notice(){
	remove_all_actions('admin_notices');
}

static function es_panelurl_tab(){
	global $wp;

	if(!isset($wp)){
		return;
	}

	$url = esc_url( add_query_arg( $wp->query_vars, $wp->request ));
	$url = str_replace('038;', '', $url);
	$url = wp_parse_url($url, PHP_URL_FRAGMENT);
	wp_parse_str($url, $output);

	return $output;
}

/*
	Collect id from post
*/
static function es_options($post = false){
	$opt = (array) maybe_unserialize(WP_Easystatic_Utils::es_option_settings("wp_static_page", []));				
	
	if(!is_array($opt))
		return false;

	if(!array_key_exists($post->post_type, $opt)){
		$opt[$post->post_type] = [];
	}

	if(!in_array($post->ID, $opt[$post->post_type])){
		$opt[$post->post_type][] = $post->ID;
	}

	return $opt;
}

static function es_sprintf( $display, ...$args){
	
	if(empty($display)){
		return false;
	}

	echo wp_sprintf( $display, ...$args );
}

/*
	check if posts id exists
*/
static function es_static_exist( $post ){
	$opt = (array) maybe_unserialize(WP_Easystatic_Utils::es_option_settings("wp_static_page", []));
	if(array_key_exists($post->post_type, $opt)){
		if(in_array($post->ID, $opt[$post->post_type])){
			return true;
		}
	}

	return false;
}

/*
	return the domain path if run in offline server
*/
static function es_domain_path(){
	$site = wp_parse_url(get_site_url());
	
	if(empty($site) || !array_key_exists('path', $site)){
		return "/";
	}

	return join("", array($site['path'], "/"));
}

/*
	create settings link in plugin lists
*/
static function es_plugin_link( $links ){

	$links[] = '<a href="' .
		admin_url( 'options-general.php?page=' . EASYSTASTIC_SLUG ) .
		'">' . __('Settings') . '</a>';

	return $links;

}

/*
	option wrapper
*/
static function es_option_settings( $field, $default_val = "" ){
		
	if(get_option($field) == false || empty(get_option($field))){
		return $default_val;
	}

	return get_option($field);
}

/*
	retrieve objects data of page/post
*/
static function es_scanned_page($type = false){
		
	$opt = WP_Easystatic_Utils::es_option_settings("wp_static_page", []);
	$ids = maybe_unserialize($opt);
	
	if(empty($ids)) 
		return false;

	if(!is_array($ids))
		return false;

	if(array_key_exists($type, $ids)){			
		if(empty($ids[$type])){
			return false;
		}
		$posts = [];
		foreach($ids[$type] as $id){
			$posts[] = WP_Easystatic_Utils::es_sanitize_post($id);
		}
		return $posts;
	}
}

/*
	utility for removing rewriterule . /index in .HTACCESS
*/
static function es_remove_index($file){
	$regex = '/([RewriteRule]+\s+\.(.*\/index\.php\b))/i';
	$split = [];
	$read = "";
	
	while(!feof($file)){
		$read .= fgets($file);
	}

	if(preg_match($regex, $read)){
		$split = preg_split($regex, $read, 2);
	}

	return $split;
}

/*
	utility for removing static url in .HTACCESS
*/
static function es_remove_static($file){
	$read = "";
	while(!feof($file)){
		$read .= fgets($file);
	}

	$splitstr = [];
	if(preg_match("/#[?begin](.*static).*/i", $read)){
		$split = preg_split("/#[?begin](.*static).*/i", $read, 2);
		$splitstr = preg_split("/#[?end](.*static).*/i", $split[1], 2);
		array_unshift($splitstr, $split[0]);
	}

	return $splitstr;
}

/*
	utility for writing static url and redirection
*/
static function es_write_staticmod($static = [], $exclude = []){
	
	global $directory;

	$static_mod = "";
	$base_path = [];
	foreach($static as $stat){
		$sep = str_replace(DIRECTORY_SEPARATOR, "/", trim($stat));
		if($sep == ""){
			$static_mod .= "RewriteCond %{HTTP_HOST} ^".get_site_url(null, "/")."$\r\n";	
			$static_mod .= "RewriteRule (.*) ".get_site_url(null, "/")."$1 [R=301,L]\r\n";
			$static_mod .= "RewriteRule ^$ ".$directory."/$1 [L]\r\n";
		}else{
			if(strpos("/", $sep) == 0){
				$trim_url = substr($sep, 1, strlen($sep) - 1);
				$multiple_path = explode("/", $trim_url);
				if(!in_array($trim_url, $exclude)){
					$static_mod .= "RewriteRule ^^". $trim_url . '/? ' . $directory.'/' . $trim_url . "/$1 [L]\r\n";
				}
				if(count($multiple_path) > 1 && !in_array($multiple_path[0], $base_path)){
					$base_path[] = $multiple_path[0];
				}
			}
		}
	}

	$static_arr = explode("\r\n", $static_mod);

	if(count($static_arr) > 0){
		$path_replace = [];
		$filter = array_filter($static_arr, function($s, $k) use ($base_path, &$path_replace, &$static_arr){
			global $directory;
			foreach($base_path as $path){
				if(strpos($s, "^^" . $path . '/?')){
					$rw = "RewriteRule ^^" . $path . '/$ ' . $directory . '/' . $path . "/$1 [L]\r\n";
					$rw .= "RewriteRule ^^" . $path . '$ ' . $directory . '/' . $path . "/$1 [L]\r\n";
					$rw .= "RewriteRule ^^" . $path . '/(.*)/? ' . $directory . '/' . $path . "/$1 [L]";
					$path_replace[$k] = $rw;
					return $s;
				}
				else if(strpos($s, "^^" . $path)){
					unset($static_arr[$k]);
				} 
			}
		}, ARRAY_FILTER_USE_BOTH);
		$replace = array_replace($filter, $path_replace);
		$replace = array_replace($static_arr, $replace);
		$static_mod = implode("\n", $replace);
	}

	return $static_mod;
}

/*
	only the administrator has priviledge to access this plugin
*/
static function get_user_auth(WP_User $user){
	$auth = false;
	if(in_array('administrator', (array) $user->roles)){
		$auth = true;
	}
	return $auth;
}

/*
	wrapper for translation string
*/
static function _status_msg( $status, $msg, $custom = false ){
	
	if(is_array($custom)){
		return $custom;
	}

	return [
		'status' => $status,
		'msg' => __( $msg, 'easystatic')
	];
}

}
