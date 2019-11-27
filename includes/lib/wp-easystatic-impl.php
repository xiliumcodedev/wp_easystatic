<?php

interface WP_Easystatic_Impl{
	
	/**
	* initialize page/post to retrieve the id
	* @return array id
	*/
	public function es_init();

	/**
	* Get the url and convert to static directory
	* @param wp_post
	* @return string
	*/
	public function es_static_subdirectory($post);

	/**
	* Open the generated HTML file
	* @param directory, filename
	* @return string
	*/
	public function es_file_open($dir, $file);

	/**
	* Open the generated HTML file and overwrite
	* @param static file content
	* @return void
	*/
	public function es_file_write($content);

	/**
	* Close the file request
	* @return false if fail
	*/
	public function es_file_close();

	/**
	* @return static base directory
	*/
	public function es_static_basedir();


	/**
	* check if static redirection is present in HTACCESS
	* @return bool
	*/
	public function es_static_is_enable();

}

