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
	$curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_HEADER, false);
	$content = curl_exec($curl);
	curl_close($curl);
	return $content;
}


/*
	Retrieve all scanned pages
*/
static function es_pages(&$lists = []){
	$pages = get_pages([
		'post_type' => 'page',
		'post_status' => 'publish'
	]);
	
	$list = array_map(function($v){
		return $v->ID;
	}, $pages);
	
	$opt = (array) maybe_unserialize(WP_Easystatic_Utils::es_option_settings("wp_static_page", []));
	
	if(array_key_exists('page', $opt)){
		$list = array_diff($list, $opt['page']);
	}
	
	return array_splice($lists, count($lists) - 1, count($list), $list);
}

/*
	Retrieve all scanned posts
*/
static function es_posts(&$lists = []){
	$posts = get_posts([
		'posts_per_page' => -1,
		'post_status' => 'publish'
	]);
	
	$list = array_map(function($v){
		return $v->ID;
	}, $posts);

	$opt = (array) maybe_unserialize(WP_Easystatic_Utils::es_option_settings("wp_static_page", []));
	
	if(array_key_exists('post', $opt)){
		$list = array_diff($list, $opt['post']);
	}

	return array_splice($lists, count($lists) - 1, count($list), $list);
}

/*
	Retrieve all scanned pages/posts
*/
static function es_lists_ids(&$lists = []){

	$opt = (array) maybe_unserialize(WP_Easystatic_Utils::es_option_settings("wp_static_page", []));
	
	if(!is_array($opt))
		return false;

	if(array_key_exists('page', $opt)){
		$list = array_merge($lists, $opt['page']);
	}

	if(array_key_exists('post', $opt)){
		$list = array_merge($lists, $opt['post']);
	}

	return array_splice($lists, count($lists) - 1, count($list), $list);
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
		admin_url( 'admin.php?page=' . EASYSTASTIC_SLUG ) .
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
			$posts[] = get_post($id);
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
