<?php

if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}

if (!defined( 'ABSPATH' ) ) {
   exit;
}

if(!file_exists('wp-easystatic-utils')){
	include_once( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 
		'includes' . DIRECTORY_SEPARATOR . 'wp-easystatic-utils.php');
}

//remove from wp_options
$del_opt = [
	"wp_static_page",
	"wp_static_version",
	"static_directory",
	"static_page_field",
	"static_exclude_url",
	"static_minify_css",
	"static_critical_enable",
	"static_exclude_css",
	"static_critical_css",
	"static_minify_js",
];

foreach($del_opt as $opt){
	delete_option($opt);
}

//remove easystatic backup directory
$path = ABSPATH . DIRECTORY_SEPARATOR . "wp-content" . DIRECTORY_SEPARATOR . "static-backup";
if(file_exists($path)){
	$files = scandir($path);
	foreach($files as $file){
		preg_match('/.*(\.)+$/', $file, $match, PREG_OFFSET_CAPTURE);
		if(!$match){
			unlink($path . '/' . $file);
		}
	}
	rmdir($path);
}

//remove easystatic cache directory
$cache_path = ABSPATH . DIRECTORY_SEPARATOR . "wp-content" . DIRECTORY_SEPARATOR . "/cache/wp-easystatic";
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
		rmdir($cache_path . DIRECTORY_SEPARATOR . $dir);
	}
	rmdir($cache_path);
}

//remove easystatic HTML directory
$static_dir = ABSPATH . DIRECTORY_SEPARATOR . (get_option("static_directory")==false) ? "generate-files" : get_option("static_directory");

if(file_exists($static_dir)){
	$dirs = WP_Easystatic_Utils::es_list_directory($static_dir);
	foreach($dirs as $dir){
		if(file_exists($static_dir . DIRECTORY_SEPARATOR . $dir . DIRECTORY_SEPARATOR . 'index.html')){
			unlink($static_dir . DIRECTORY_SEPARATOR . $dir . DIRECTORY_SEPARATOR . 'index.html');
			rmdir($static_dir . DIRECTORY_SEPARATOR . $dir);
		}
	}
	rmdir($static_dir);
}


