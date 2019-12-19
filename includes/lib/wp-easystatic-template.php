<?php

/*
	This class is purely the plugin user interface
*/

if (!defined( 'ABSPATH' ) ) {
   exit;
}

class WP_Easystatic_Template{

	var $setting_group;
	var $setting_section;
	var $setting_callback;
	var $setting_title;

	static function instance(){
		return new self();
	}

	function tpl_open_form( $atts ){
		
		$default = array('method' => 'GET', 'action' => NULL, 'atts' => NULL);

		if(!is_array($atts)){
			return;
		}

		$merge = array_merge($default, $atts);

		$attr = "";
		if(!empty($merge['atts'])){
			foreach($merge['atts'] as $k => $v){
				$attr .= $k . "='{$v}'";
			}
		}

		echo "<form method={$merge['method']} action={$merge['action']} {$attr}>";
	}

	function tpl_close_form(){
		echo "</form>";
	}

	function tpl_open_table( $atts ){
		
		$default = array('class' => NULL, 'id' => NULL, 'atts' => NULL);

		if(!is_array($atts)){
			return;
		}

		$merge = array_merge($default, $atts);

		$attr = "";
		if(!empty($merge['atts'])){
			foreach($merge['atts'] as $k => $v){
				$attr .= $k . "='{$v}'";
			}
		}

		echo "<table class={$merge['class']} id={$merge['id']} {$attr}>";
	}

	function tpl_close_table(){
		echo "</table>";
	}

	function tpl_table_headers( $headers ){
		if(!is_array($headers)){
			return;
		}

		echo "<thead><tr>";
		foreach($headers as $header){
			echo "<th>{$header}</th>";
		}
		echo "</tr></thead>";

	}

	function tpl_table_body( $bodies ){
		
		if(!is_array($bodies)){
			return;
		}

		echo "<tbody>";
		foreach( $bodies as $body ){
			echo "<tr>";
			echo $body;
			echo "</tr>";
		}
		echo "</tbody>";
	}

	function tpl_partial_percent(){
		?>
		<div class='percent-wrapper'>
		<div class='side'>
		<div id="percent-bar"><div class='percent'></div></div>
		<div id='count-em'>0%</div>
		</div>
		<div class='bottom'>
		<div id="logs"></div>
		</div>
		</div>
		<?php
	}

	function tpl_partial_buttons(){
		?>
		<div class='console-content'>
		<div class='content-left'>
		<button class='generate-static-file' id="start_scan"><?php echo __('Generate Static', 'easystatic') ?></button>
		</div>
		<div class='content-right'>
		<button class='check-update-file' id="update_scan"><?php echo __('Update Static', 'easystatic') ?></button>
		</div>
		</div>
		<?php
	}

	function tpl_partial_table(){
		?>
		<div class='wp-es-row'>
		<div class='wp-es-col-left'>
		<strong><?php echo __('Search Title:', 'easystatic') ?></strong>
		<span><input type='text' id='search-title' /></span>
		</div>
		<div class='wp-es-col-right'>
		<strong><?php echo __('Show:', 'easystatic') ?></strong>
		<span><select id="es-select-show">
			<option value="5"><?php echo __('5', 'easystatic') ?></option>
			<option value="10"><?php echo __('10', 'easystatic') ?></option>
			<option value="20"><?php echo __('20', 'easystatic') ?></option>
			<option value="30"><?php echo __('30', 'easystatic') ?></option>
			<option value="40"><?php echo __('40', 'easystatic') ?></option>
			<option value="50"><?php echo __('50', 'easystatic') ?></option>
			</select>
		</span>
		<!-- <button id="refresh"><?php echo __('Refresh', 'easystatic') ?></button> -->
		</div>
		</div/>
		<?php
	}

	function tpl_partial_tab_menu(){
		?>
		<h2 class="nav-tab-wrapper">  
		    <a href="?page=<?php echo EASYSTASTIC_SLUG ?>&tab=general" class="nav-tab">
		    	<?php echo __('General', 'easystatic') ?></a>  
		    <a href="?page=<?php echo EASYSTASTIC_SLUG ?>&tab=static" class="nav-tab">
		    	<?php echo __('Static', 'easystatic') ?></a>
		    <a href="?page=<?php echo EASYSTASTIC_SLUG ?>&tab=backup" class="nav-tab">
		    	<?php echo __('Backup', 'easystatic') ?></a>
		    <a href="?page=<?php echo EASYSTASTIC_SLUG ?>&tab=import" class="nav-tab">
		    	<?php echo __('Import', 'easystatic') ?></a>
		    <a href="?page=<?php echo EASYSTASTIC_SLUG ?>&tab=optimize" class="nav-tab">
		    	<?php echo __('Optimize', 'easystatic') ?></a>
		</h2>
		<?php
	}

	function tpl_partial_static(WP_EASYSTATIC_Components $component){
		
		$root = EASYSTATIC_BASE . '/' . WP_Easystatic_Utils::es_option_settings('static_directory', 'generate-files');

		$static = WP_Easystatic_Utils::es_list_directory($root);

		$pages = $component->get_base_scanner();
		
		$static_page = (array) maybe_unserialize(WP_Easystatic_Utils::es_option_settings('wp_static_page', []));
		
		$ids = [];

		if(empty($pages) || empty($static_page) || empty($static)){
			return $ids;
		}

		
		foreach($pages as $page){
			if(empty($page)) continue;
			foreach($page as $p){
				$sublink = str_replace(get_site_url(), '', get_permalink($p->ID));
				$sublink = str_replace("/", DIRECTORY_SEPARATOR, $sublink);
				$sublink = substr($sublink, 0, -1);
				if(in_array($sublink, $static)){
					$ids[] = $p->ID;
				}
			}
		}
		return $ids;

	}

	function tpl_partial_td_action( $page = false ){
		
		if(!$page){
			return;
		}

		ob_start();
		
		?>
		<a href='#TB_inline?&width=600&height=550&inlineId=static-file-modal' data-id="<?php echo $page->ID ?>" class='stat_edit_file thickbox'><?php echo __('Edit', 'easystatic') ?></a>
		<a href='javascript:void(0)' data-id="<?php echo $page->ID ?>" class='stat_remove_file'><?php echo __('Remove', 'easystatic') ?></a>
		<a href='#TB_inline?&width=600&height=550&inlineId=static-file-update' data-id="<?php echo $page->ID ?>" class='stat_update_file thickbox'><?php echo __('Update', 'easystatic') ?></a>
		<?php

		return ob_get_clean();

	}

	function tpl_partial_modal(){

		?>
		
		<div id="static-file-modal" style="display:none;">
			 <div class='top-modal'>
			 <h3 class='edit-title'></h3>
			 <div class='btn-modal'>
			 <button id="save-source" class="button button-primary"><?php echo __('Save', 'easystatic') ?></button>
			 </div>
			 </div>
		     <textarea id="code-static-load" style="width:100%;height:100%;"></textarea>
		</div>
		<div id="static-file-update" style="display:none;">
			 <div class='top-modal'>
			 <h3 class='update-title'></h3>
			 <div class='btn-modal'>
			 	<button id="static-paste-update" class="button button-primary"><?php echo __('Paste Updated Content', 'easystatic') ?></button>
			 <button id="static-merge-update" class="button button-primary"><?php echo __('Update', 'easystatic') ?></button>
			 </div>
			</div>
		     <div id="static-update-view"></div>
		</div>

		<?php
	}

	function tpl_partial_backup_btn(){

		?>
		<div class='es-button-group'>
			<div class='form-group'>
				<div id='es-loader'><span><img src="<?php echo EASYSTATIC_URL ?>/assets/images/loader.gif" width="50px" /></span></div>
			<button class='generate-static-file' id="create_backup"><?php echo __('Create Backup', 'easystatic') ?></button>
			</div>
		</div>
		<?php

	}

	function tpl_partial_import_field(){

		?>
		<div class='static-console-wrapper'>
			<form method="post" enctype="multipart/form-data">
				<?php wp_nonce_field( 'es-file-upload', 'es-file-upload-field' ); ?>
				<input type='file' name='es-file-import' id='es-file-import'/>
				<button class='button button-primary'><?php echo __('Go', 'easystatic') ?></button>
			</form>
		</div>
		<?php

	}

	function es_dashboard_notice(){
		$errors = (array) get_settings_errors('easystatic_notice', true);
		if(is_array($errors)){
			if(isset($errors[0]['message'])){
				echo "<div class=\"notice notice-info is-dismissible\">
					<p>" . __("Go to static tab and generate HTML file", "easystatic") . "</p></div>";
			}
		}
	}

	function es_setting_baseurl_field( $param ) {
		WP_Easystatic_Utils::es_sprintf(
			'<input type="text" value="%s" class="regular-text" name=%s />', 
			get_option($param['field']), 
			$param['field']
		);
	}

	function es_setting_directory_field( $param ) {
		WP_Easystatic_Utils::es_sprintf(
			'<input type="text" value="%s" class="regular-text" name="%s" />', 
			get_option($param['field']), 
			$param['field']
		);
	}

	function es_setting_exclude_url_field( $param ){
		WP_Easystatic_Utils::es_sprintf('<textarea name=%1$s rows="10" cols="50" id=%1$s  class="large-text code">%2$s</textarea>', 
			$param['field'], 
			get_option($param['field'])
		);

		echo "<p>" . __("Exclude from generated static, the url format must have a pathname e.g.: /page-title, /2019/12/sample-post. One line per URL.", "easystatic") . "</p>";
	}

	function es_setting_post_page( $param ){
		foreach($param['post_type'] as $type){
			$opt = get_option($param['field']);
			$is_check = (in_array($type, is_array($opt) ? $opt : [])) ? 'checked' : '';
			WP_Easystatic_Utils::es_sprintf('<p><label><input type="checkbox" value="%s" name="%s[]" %s/><span>%s</span></label></p>', 
			$type, $param['field'], $is_check, ucwords(str_replace("_", ' ', $type)));
		}
	}

	function es_setting_minify_css( $param ){
		$opt = get_option($param['field']);
		$is_check = ($opt) ? 'checked' : '';
		WP_Easystatic_Utils::es_sprintf('<p><label><input type="checkbox" value="1" name=%s %s/></label>%s</p>', 
			$param['field'], 
			$is_check, 
			__('Check this option will include preloading css.', 'easystatic')
		);
	}

	function es_setting_critical_css( $param ){
		$check = get_option($param['field']);
		$is_check = ($check)  ? 'checked' : '';
		WP_Easystatic_Utils::es_sprintf('<p><label><input type="checkbox" value="1" name=%s %s/>%s</label></p>', 
			$param['field'], $is_check, __('Check this option will prioritize the critical css before main CSS file loaded.', 'easystatic'));
	}

	function es_setting_critical_css_codes( $param ){
		WP_Easystatic_Utils::es_sprintf('<p><textarea name=%1$s rows="10" cols="50" id=%1$s class="large-text code">%2$s</textarea></p>', $param['field'], 
			get_option($param['field'])
		); 
	}

	function es_setting_minify_js( $param ){
		$opt = get_option($param['field']);
		$is_check = ($opt) ? 'checked' : '';
		WP_Easystatic_Utils::es_sprintf('<p><label><input type="checkbox" value="1" name=%s %s/></label>%s</p>', $param['field'], $is_check, __('Check this option will aggregate all link JS file and Inline JS Code', 'easystatic')); 
	}

	function es_setting_minify_html( $param ){
		WP_Easystatic_Utils::es_sprintf('<p><label><input type="checkbox" value="1" name=%s %s/>', 
			$param['field'], 
			get_option($param['field']) ? 'checked' : ''
		);
	}

	function es_setting_exclude_css( $param ){
		WP_Easystatic_Utils::es_sprintf('<input type="text" value="%s" class="regular-text" name=%s />', 
			get_option($param['field']), 
			$param['field']
		);
		echo "<p>" . __("List of CSS url on the text box and separated by comma to exclude from optimized e.g.: style1.css, style2.css.", "easystatic") . "</p>";
	}

	function es_setting_exclude_js( $param ){
		WP_Easystatic_Utils::es_sprintf('<input type="text" value="%s" class="regular-text" name=%s />', 
			get_option($param['field']), 
			$param['field']
		);

		echo "<p>" . __("List of JS url on the text box and separated by comma to exclude from optimized e.g.: script1.js, script2.js.", "easystatic") . "</p>";
	}

}
