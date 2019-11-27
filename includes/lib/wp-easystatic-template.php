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

		extract($merge);

		echo "<form method={$method} action={$action} {$atts}>";
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

		extract($merge);

		echo "<table class={$class} id={$id} {$atts}>";
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
		<button class='generate-static-file' id="start_scan"><?= __('Generate Static', 'easystatic') ?></button>
		</div>
		<div class='content-right'>
		<button class='check-update-file' id="update_scan"><?= __('Update Static', 'easystatic') ?></button>
		</div>
		</div>
		<?php
	}

	function tpl_partial_table(){
		?>
		<div class='wp-es-row'>
		<div class='wp-es-col-left'>
		<strong><?= __('Search Title:', 'easystatic') ?></strong>
		<span><input type='text' id='search-title' /></span>
		</div>
		<div class='wp-es-col-right'>
		<strong><?= __('Show:', 'easystatic') ?></strong>
		<span><select id="es-select-show">
			<option value="1">1</option>
			<option value="2">2</option>
			<option value="3">3</option>
			<option value="4">4</option>
			<option value="5">5</option>
			<option value="6">6</option>
			<option value="7">7</option>
			</select>
		</span>
		<button id="refresh"><?= __('Refresh', 'easystatic') ?></button>
		</div>
		</div/>
		<?php
	}

	function tpl_partial_tab_menu(){
		?>
		<h2 class="nav-tab-wrapper">  
		    <a href="?page=<?php echo EASYSTASTIC_SLUG ?>&tab=general" class="nav-tab">
		    	<?= __('General', 'easystatic') ?></a>  
		    <a href="?page=<?php echo EASYSTASTIC_SLUG ?>&tab=static" class="nav-tab">
		    	<?= __('Static Files', 'easystatic') ?></a>
		    <a href="?page=<?php echo EASYSTASTIC_SLUG ?>&tab=backup" class="nav-tab">
		    	<?= __('Backup', 'easystatic') ?></a>
		    <a href="?page=<?php echo EASYSTASTIC_SLUG ?>&tab=import" class="nav-tab">
		    	<?= __('Import', 'easystatic') ?></a>
		    <a href="?page=<?php echo EASYSTASTIC_SLUG ?>&tab=optimize" class="nav-tab">
		    	<?= __('Optimize', 'easystatic') ?></a>
		</h2>
		<?php
	}

	function tpl_partial_static(WP_EASYSTATIC_Components $component){

		$page = $component->get_base_scanner('page');
		$post = $component->get_base_scanner('post');


		$merge = [];
		
		if($page){
			array_splice($merge, count($merge) - 1, count($page), $page);
		}
		
		if($post){
			array_splice($merge, count($merge) - 1, count($post), $post);
		}

		return $merge;

	}

	function tpl_partial_td_action( $page = false ){
		
		if(!$page){
			return;
		}

		ob_start();
		
		?>
		<a href='#TB_inline?&width=600&height=550&inlineId=static-file-modal' data-id="<?= $page->ID ?>" class='stat_edit_file thickbox'><?= __('Edit', 'easystatic') ?></a>
		<a href='javascript:void(0)' data-id="<?= $page->ID ?>" class='stat_remove_file'><?= __('Remove', 'easystatic') ?></a>
		<a href='#TB_inline?&width=600&height=550&inlineId=static-file-update' data-id="<?= $page->ID ?>" class='stat_update_file thickbox'><?= __('Update', 'easystatic') ?></a>
		<?php

		return ob_get_clean();

	}

	function tpl_partial_modal(){

		?>
		
		<div id="static-file-modal" style="display:none;">
			 <div class='top-modal'>
			 <h3 class='edit-title'></h3>
			 <div class='btn-modal'>
			 <button id="save-source" class="button button-primary"><?= __('Save', 'easystatic') ?></button>
			 </div>
			 </div>
		     <textarea id="code-static-load" style="width:100%;height:100%;"></textarea>
		</div>
		<div id="static-file-update" style="display:none;">
			 <div class='top-modal'>
			 <h3 class='update-title'></h3>
			 <div class='btn-modal'>
			 <button id="static-merge-update" class="button button-primary"><?= __('Update', 'easystatic') ?></button>
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
			<button class='generate-static-file' id="create_backup"><?= __('Create Backup', 'easystatic') ?></button>
			<div id='es-loader'><span><img src="<?php echo EASYSTATIC_URL ?>/assets/images/loader.gif" width="50px" /></span></div>
			</div>
		</div>
		<?php

	}

	function tpl_partial_import_field(){

		?>

		<form method="post" enctype="multipart/form-data">
		<?php wp_nonce_field( 'es-file-upload', 'es-file-upload-field' ); ?>
		<input type='file' name='es-file-import' id='es-file-import'/>
		<button class='button button-primary'><?= __('Go', 'easystatic') ?></button>
		</form>

		<?php

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
	}

	function es_setting_post_page( $param ){
		$opt = get_option($param['field']);
		$is_check = (in_array($param['type'][0], is_array($opt) ? $opt : [])) ? 'checked' : '';
		WP_Easystatic_Utils::es_sprintf('<p><label><input type="checkbox" value="%s" name="%s[]" %s/><span>%s</span></label></p>', 
			$param['type'][0], $param['field'], $is_check, __('Page', 'easystatic'));
		
		$is_check = (in_array($param['type'][1], is_array($opt) ? $opt : []) && is_array($opt)) ? 'checked' : '';
		WP_Easystatic_Utils::es_sprintf('<p><label><input type="checkbox" value="%s" name="%s[]" %s/><span>%s</span></label></p>', 
			$param['type'][1], $param['field'], $is_check, __('Post', 'easystatic'));
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
	}

	function es_setting_exclude_js( $param ){
		WP_Easystatic_Utils::es_sprintf('<input type="text" value="%s" class="regular-text" name=%s />', 
			get_option($param['field']), 
			$param['field']
		);
	}

}
