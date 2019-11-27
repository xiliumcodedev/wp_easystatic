<?php

if (!defined( 'ABSPATH' ) ) {
   exit;
}

class WP_Easystatic_Components{
	
	private $generate;
	private $template;

	static function instance(){
		return new self();
	}

	/*
		construct plugin template and static generator
	*/
	function __construct(){
		$this->generate = new WP_Easystatic_Generate();
		$this->template = new WP_Easystatic_Template();
	}

	function easystatic_template($data = array()){

		if(!array_key_exists('template', $data)){
			return false;
		}
		
		if(is_array($data)){
			extract($data);
		}
		
		$file = file_exists(EASYSTATIC_DIR . $template );

		if(!$file){
			return false;
		}

		include(EASYSTATIC_DIR . $template );

	}

	/*
		retrieve all the post ID from scanning
	*/
	function get_base_scanner($type = ''){
		
		$root = EASYSTATIC_BASE . '/' . WP_Easystatic_Utils::es_option_settings('static_directory', 'generate-files');

		$static = WP_Easystatic_Utils::es_list_directory($root);

		$pages = [];
		if($type == 'page'){
			$pages = WP_Easystatic_Utils::es_scanned_page('page');
		}
		else if($type == 'post'){
			$pages = WP_Easystatic_Utils::es_scanned_page('post');
		}

		if(empty($pages)){
			return false;
		}

		$ids = [];
		foreach($pages as $page ){
			$sublink = str_replace(get_site_url(), '', get_permalink($page->ID));
			$sublink = str_replace("/", DIRECTORY_SEPARATOR, $sublink);
			$sublink = substr($sublink, 0, -1);
			if(in_array($sublink, $static)){
				$ids[] = $page->ID;
			}
		}

		return $ids;
	}

	/*
		count all generated files
	*/
	function count_static_generated(){
		$total = 0;

		$root = EASYSTATIC_BASE . '/' . WP_Easystatic_Utils::es_option_settings('static_directory', 'generate-files');
		$static = WP_Easystatic_Utils::es_list_directory($root);
		
		if(empty($static)){
			return $total;
		}
		
		$pages = WP_Easystatic_Utils::es_scanned_page('page');
		$posts = WP_Easystatic_Utils::es_scanned_page('post');
		
		if($pages){
			$total += count($pages);
		}

		if($posts){
			$total += count($posts);
		}

		return $total;
	}

	/*
		if not exists in the static directory then count
	*/
	function count_unstatic(){

		$total = 0;

		$root = EASYSTATIC_BASE . '/' . WP_Easystatic_Utils::es_option_settings('static_directory', 'generate-files');
		$static = WP_Easystatic_Utils::es_list_directory($root);
		
		if(empty($static)){
			return $total;
		}

		$pages = WP_Easystatic_Utils::es_scanned_page('page');
		$posts = WP_Easystatic_Utils::es_scanned_page('post');

		if($pages || $posts){
			foreach($pages as $page ){
			$sublink = str_replace(get_site_url(), '', get_permalink($page->ID));
			$sublink = str_replace("/", DIRECTORY_SEPARATOR, $sublink);
			$sublink = substr($sublink, 0, -1);
				if(!in_array($sublink, $static)){
					$total++;
				}
			}
		}
		

		return $total;
	}

	
	function conf_setting($conf){
		
		$this->template->setting_group = $conf->group;
		$this->template->setting_section = $conf->section;
		$this->template->setting_callback = $conf->callback;
		$this->template->setting_title = $conf->title;
	}

	function section_setting(){
		
		add_settings_section(
			$this->template->setting_section,
			$this->template->setting_title,
			$this->template->setting_callback,
			$this->template->setting_group
		);

	}

	function setting_field($field, $label, $args){
		add_settings_field(
			$field,
			$label,
			$this->template->setting_callback,
			$this->template->setting_group,
			$this->template->setting_section,
			$args
		);
	}

	/*
		option settings templates
	*/
	function setting_section_opt( $field ){
		if($field['id'] == "baseurl_field"){
			$this->template->es_setting_baseurl_field($field);
		}
		else if($field['id'] == "static_directory"){
			$this->template->es_setting_directory_field($field);
		}
		else if($field['id'] == 'static_page_field'){
			$this->template->es_setting_post_page($field);
		}
		else if($field['id'] == "static_exclude_url"){
			$this->template->es_setting_exclude_url_field($field);
		}
		else if($field['id'] == "static_minify_css"){
			$this->template->es_setting_minify_css($field);
		}
		else if($field['id'] == "static_minify_js"){
			$this->template->es_setting_minify_js($field);
		}
		else if($field['id'] == "static_minify_html"){
			$this->template->es_setting_minify_html($field);
		}
		else if($field['id'] == "static_exclude_css"){
			$this->template->es_setting_exclude_css($field);
		}
		else if($field['id'] == "static_critical_css"){
			$this->template->es_setting_critical_css_codes($field);
		}
		else if($field['id'] == "static_critical_enable"){
			$this->template->es_setting_critical_css($field);
		}
		else if($field['id'] == "static_exclude_js"){
			$this->template->es_setting_exclude_js($field);
		}
	}

	function static_redirect_code(WP_Easystatic_Impl $easystatic){
		if($easystatic){
			return $easystatic->es_static_is_enable();
		}
		return false;
	}


	/*
		unzip the uploaded zip file once submit
	*/
	function static_zip_upload(){
		$is_upload = false;
		
		if(isset($_POST['es-file-upload-field']) && wp_verify_nonce($_POST['es-file-upload-field'], 'es-file-upload')){
			$file = $_FILES['es-file-import'];
			
			$backupdir = EASYSTATIC_BASE . DIRECTORY_SEPARATOR . 'wp-content' . DIRECTORY_SEPARATOR . 
			'static-backup';
			$file_dir = $backupdir . '/' . basename($file['name']);
			$zipext = strtolower(pathinfo($file_dir,PATHINFO_EXTENSION));

			if($zipext != 'zip'){
				return false;
			}

			$zipname = explode('.' . $zipext, basename($file['name']));

			$zipfiles = WP_Easystatic_Utils::es_check_zip_exists($backupdir, $zipname[0]);
			if(file_exists($file_dir)){
				$newzip = $zipname[0] . '-' . count($zipfiles) . '.zip';
				if(!file_exists($backupdir . '/tmp')){
					mkdir($backupdir . '/tmp', 0777, true);
					$tmp = $backupdir . "/tmp/" . $newzip;
					if (move_uploaded_file($file["tmp_name"], $tmp)){
						rename($tmp, $backupdir . '/' . $newzip);
					}
					rmdir($backupdir . "/tmp/");
				}
				
				WP_Easystatic_Utils::es_extracting_zip($backupdir . '/' . $newzip);
				$is_upload = true;
			}

			else if (move_uploaded_file($file["tmp_name"], $file_dir)) {
				WP_Easystatic_Utils::es_extracting_zip($file_dir);
				$is_upload = true;
			}
		}

		return $is_upload;
	}

	/*
		count all the backup generated in directory
	*/
	function static_backup_lists(){

		$root = EASYSTATIC_BASE . DIRECTORY_SEPARATOR . 'wp-content' . DIRECTORY_SEPARATOR . 'static-backup';
		
		if(!file_exists($root)){
			mkdir($root, 0777, true);
		}

		$dir = scandir($root);
		$zipfiles = $this->generate->static_backup_zip($dir);

		return $zipfiles;
	}

	function get_static_update(){
		
		$stat_ids = $this->generate->static_size_update();
		
		if(empty($stat_ids)){
			return false;
		}

		if(!is_array($stat_ids)){
			return false;
		}

		return $stat_ids;
	}

	/*
		plugin page tab menu navigation
	*/
	function _tab_menu(){
		
		ob_start();

		$this->template->tpl_partial_tab_menu();

		$tab_template = ob_get_clean();
		
		ob_flush();

		return $tab_template;
	}

	/*
		display settings in general tab menu
	*/
	function _tab_general_view(){
		
		ob_start();

		$this->template->tpl_open_form([
			'method' => 'POST',
			'action' => 'options.php'
		]);

		settings_fields( 'wp_easystatic' );
		do_settings_sections( 'wp_easystatic' );
		submit_button('submit');

		$this->template->tpl_close_form();

		$tab_template = ob_get_clean();
		
		ob_flush();

		return $tab_template;
	}

	/*
		display generated html files in static tab menu
	*/
	function _tab_static_view(){
		
		ob_start();

		add_thickbox();
		
		echo "<div class='static-console-wrapper'>";
		$this->template->tpl_partial_percent();
		$this->template->tpl_partial_buttons();
		echo "</div>";

		echo "<div class='wp-es-container'>
		<h3>Static Pages</h3>";

		$this->template->tpl_partial_table();
		$this->template->tpl_open_table(['class' => 'table', 'id' => 'datatable']);
		$this->template->tpl_table_headers([
			'Type', 'Title', 'Link', 'Static Directory', 'Action'
		]);
		
		$static = $this->template->tpl_partial_static($this);

		$td_content = array_map(function($id){
			$page = get_post($id);
			$link = get_the_permalink($page->ID);
			$sublink = str_replace(get_site_url(), '', get_permalink($page->ID));
			$sublink = str_replace("/", DIRECTORY_SEPARATOR, $sublink);
			$sublink = substr($sublink, 0, -1);
			$action_link = $this->template->tpl_partial_td_action($page);

			return "<td>{$page->post_type}</td>
					<td>{$page->post_title}</td>
					<td>{$link}</td>
					<td>{$sublink}</td>
					<td>{$action_link}</td>";
		}, $static);

		$this->template->tpl_table_body($td_content);
		$this->template->tpl_close_table();
		$this->template->tpl_partial_modal();
		echo '</div>';

		$tab_template = ob_get_clean();
		
		ob_flush();

		return $tab_template;
	}

	/*
		display generated zip files in backup tab menu
	*/
	function _tpl_tab_backup(){

		ob_start();

		$this->template->tpl_partial_backup_btn();
		echo '<div class="form-group">';
		$this->template->tpl_open_table(['class' => 'table', 'id' => 'backup-dt']);
		$this->template->tpl_table_headers([
			'Filename', 'Date', 'Size', '', '', ''
		]);
		echo '</div>';

		$files = $this->static_backup_lists();

		$td_content = array_map(function($file){
			return "
			<td>{$file['file']}</td>
			<td>{$file['date']}</td>
			<td>".round($file['size'], 2)."kb</td>
			<td><a href=\"{$file['location']}\">Export</a></td>
			<td><a href=\"{$file['location']}\" class='es-restore-backup'>Restore</a></td>
			<td><a href=\"{$file['location']}\" class='es-remove-backup'>Remove</a></td>";

		}, $files);

		$this->template->tpl_table_body($td_content);
		$this->template->tpl_close_table();

		$tab_template = ob_get_clean();
		
		ob_flush();

		return $tab_template;

	}

	/*
		display import
	*/
	function _tpl_tab_import(){
		
		ob_start();

		$this->template->tpl_partial_import_field();

		$tab_template = ob_get_clean();
		
		ob_flush();

		return $tab_template;

	}

	/*
		display optimize settings in optimize tab menu
	*/
	function _tpl_tab_optimize(){
		
		ob_start();

		$this->template->tpl_open_form([
			'method' => 'POST',
			'action' => 'options.php'
		]);

		settings_fields( 'static_minify' );
		do_settings_sections( 'static_minify' );
		submit_button('submit');

		$this->template->tpl_close_form();

		$tab_template = ob_get_clean();
		
		ob_flush();

		return $tab_template;
	}


}

