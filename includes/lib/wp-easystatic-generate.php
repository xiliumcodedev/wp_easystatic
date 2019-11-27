<?php

if (!defined( 'ABSPATH' ) ) {
   exit;
}

class WP_Easystatic_Generate extends WP_Easystatic_Request{
	
	/**
	*  Retrieve URL and return full content
	*  @return url content
	*/
	function es_read($post){

		$link = get_permalink($post->ID);
		$content = WP_Easystatic_Utils::es_get_sitecontent($link);
		return $content;
	}

	/**
	*  Retrieve static HTML file content and format. This is use in edit request
	*  @return format content
	*/
	function es_edit($post = false){
		
		$dom = new DOMDocument();
		
		$content = file_get_contents(EASYSTATIC_BASE . '/' . 
			$this->es_static_basedir() . $this->es_static_subdirectory($post) . 'index.html');

		@$dom->loadHTML($content, LIBXML_HTML_NOIMPLIED);
		@$dom->preserveWhiteSpace = false; 
		@$dom->formatOutput = true;

		return $content;
		
	}

	/*
		Append static content from edit mode
	*/
	function es_append($post = false, $content = ""){
		// Get the link and subdirectory
		$sub = $this->es_static_subdirectory($post);
		if(!empty($sub)){
			$this->es_append_request($content);
			return true;
		}
		return false;
	}

	/*
		This remove static HTML file
	*/
	function es_remove($post = false){
		$sub = $this->es_static_subdirectory($post);
		$hasDelete = false;
		if($this->es_file_exists()){
			$this->es_file_remove();
			$hasDelete = true;
		}
		return $hasDelete;
	}

	/*
		retrieve url and return its full content
	*/
	function es_request_dynamic($post = false){
		$link = get_permalink($post->ID);
		$content = WP_Easystatic_Utils::es_get_sitecontent($link);
		$strip_content = stripslashes_deep($content);

		$dom = new DOMDocument();
		@$dom->loadHTML($strip_content, LIBXML_HTML_NOIMPLIED);
		@$dom->preserveWhiteSpace = false; 
		@$dom->formatOutput = true;
		return $strip_content;
	}

	/*
		This update the static HTML file
	*/
	function es_static_update($post = false, $content = ""){
		$this->es_static_subdirectory($post);
		if(!$this->es_file_exists()){
			return false;
		}

		$this->es_append_request($content);
		return true;
	}

	/*
		wrapper enable static redirect and rewrite HTACCESS
	*/
	function es_rewrite_static($exclude_urls = []){
		global $directory;

		$file = fopen(EASYSTATIC_BASE . DIRECTORY_SEPARATOR . '.htaccess', "r");
		$content = WP_Easystatic_Utils::es_remove_index($file);
		fclose($file);
		$static_mod = "\r\n#begin Static Rule\n<IfModule mod_rewrite.c>\r\n";
		$this->static_rewrite_ht($static_mod, $exclude_urls);

		if(!empty($exclude_urls)){
			$path = WP_Easystatic_Utils::es_domain_path();
			foreach($exclude_urls as $url){
				$static_mod .= "RewriteRule ^^" . $url . '/? ' . $path . "index.php [L]" . PHP_EOL;
			}
		}

		$static_mod .= "</IfModule>\n#End Static Rule";
		$substr = substr(trim($content[1]), strlen("</IfModule>"));
		$output = trim($content[0]) . PHP_EOL . "</IfModule>" . PHP_EOL . $static_mod . $substr;


		$file = fopen(EASYSTATIC_BASE . DIRECTORY_SEPARATOR . '.htaccess', "w");
		fwrite($file, stripslashes_deep($output));
		if(fclose($file)){
			return true;
		}

	}

	/*
		wrapper to remove static redirect and return to dynamic state
	*/
	function es_rewrite_dynamic(){
		$file = fopen(EASYSTATIC_BASE . DIRECTORY_SEPARATOR . '.htaccess', "r");
		$content = WP_Easystatic_Utils::es_remove_static($file);
		fclose($file);

		$path = WP_Easystatic_Utils::es_domain_path();
		$split = explode("</IfModule>", str_replace("\n\r", "", $content[0]));
		$index = $split[0] . "RewriteRule . " . $path . "index.php" . PHP_EOL . "</IfModule>";
		$format = $index . PHP_EOL . $content[count($content) - 1];

		$write = file_put_contents(EASYSTATIC_BASE . DIRECTORY_SEPARATOR . '.htaccess', $format);
		return true;

	}

	/*
		retrieve all zip file from static back up and return its info
	*/
	function static_backup_zip($dir){
		$root = EASYSTATIC_BASE . DIRECTORY_SEPARATOR . 'wp-content' . DIRECTORY_SEPARATOR . 'static-backup';
		$zipfiles = [];
		foreach($dir as $d){
			preg_match('/.*(\.)+$/', $d, $match, PREG_OFFSET_CAPTURE);
			if(!$match){
				$info = pathinfo($d);
				$zipfiles[] = [
				'location' => get_site_url() . '/wp-content/static-backup/' . $info['basename'],
				'file' => $info['filename'], 
				'size' => (filesize($root . DIRECTORY_SEPARATOR . $info['basename']) / 1000),
				'date' => date ("M d Y H:i:s", filemtime($root . DIRECTORY_SEPARATOR . $info['basename']))
				];
			}
		}

		return $zipfiles;
	}

	/*
		function to create zip file for static backup
	*/
	function create_zip_file(ZipArchive $zip, $directory = false){

		$filename = EASYSTATIC_BASE . DIRECTORY_SEPARATOR . $directory . ".zip";

		$root = EASYSTATIC_BASE . '/' . WP_Easystatic_Utils::es_option_settings('static_directory', 'generate-files');
		
		if(!is_dir($root)){
			return false;
		}

		$static = WP_Easystatic_Utils::es_list_directory($root);

		if(!$zip->open($filename, ZipArchive::CREATE | ZipArchive::OVERWRITE)){

			return false;

		}

		foreach($static as $file){
			 $zip->addFile( $root . $file . DIRECTORY_SEPARATOR . 'index.html',  $file . DIRECTORY_SEPARATOR . 'index.html');
		}

		$zip->close();
		
		$backupdir = EASYSTATIC_BASE . DIRECTORY_SEPARATOR . 'wp-content' . DIRECTORY_SEPARATOR . 
			'static-backup';
		$tempfile = EASYSTATIC_BASE . DIRECTORY_SEPARATOR . $directory . ".zip";
		$zipbackup = $backupdir .DIRECTORY_SEPARATOR . $directory . ".zip";

		if(!file_exists($backupdir)){
			mkdir($backupdir, 0777, true);
		}

		if(file_exists($zipbackup)){
			$files = glob($backupdir . DIRECTORY_SEPARATOR . $directory . '*.zip');
			$zipbackup = $backupdir .DIRECTORY_SEPARATOR . $directory . "-" . count($files) .".zip";
		}

		rename($tempfile, $zipbackup);

		return true;
		
	}

	/*
		This restore the backup file to static directory
	*/
	function restore_backup(ZipArchive $zip, $file){
		$directory = WP_Easystatic_Utils::es_option_settings('static_directory', 'generate-files');
		if(!$zip->open($file)){
			return false;
		}
		$zip->extractTo(EASYSTATIC_BASE . DIRECTORY_SEPARATOR . $directory);
		$zip->close();

		return true;
	}

	/*
		function to remove the backup zip file
	*/
	function remove_backup($file){

		if(file_exists($file)){
			unlink($file);
		}

		return true;
	}

	/*
		function to optimize the static HTML file
	*/
	function es_optimize(){
		$optimize = new WP_Easystatic_Optimize();
		$optimize->get_styles();
		$directory = $optimize->check_cache_directory();
		return $optimize->get_minify_css("256M");
	}

}
