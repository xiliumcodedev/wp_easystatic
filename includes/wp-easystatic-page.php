<?php $obj = (object) $template ?>
<div class='wp-easy-container-main'>
<div class='wp-easy-container-top'>
  <div class='left-side'>
    <div class='form-group'>
      <p><h2><?= _e('Generated HTML File:', 'easystatic') ?> <?php echo $obj->total_static - $obj->total_unstatic ?></h2></p>
    </div>
</div>
<div class='right-side'>
  <p><h3><?php echo __('Activate Static Redirection:', 'easystatic') ?></h3></p>
  <label class="switch">
  <input type="checkbox" class='static_activate' <?php echo ($obj->static_enable) ? 'checked' : '' ?> />
  <span class="slider round"></span>
  </label>
  <p><strong><small><?php echo __('This will enable static HTML in your front-end.', 'easystatic') ?></small></strong></p>
</div>
</div>
<div class='es-container'>
  <div class='form-group'>
  <?php echo $obj->menu_tab ?>
  </div>
<?php if($obj->tab == 'general'): ?>
  <div class='form-group'>
  <?php echo $obj->general_tab ?>
  </div>
<?php elseif($obj->tab == 'static'): ?>
  <div class='form-group'>
  <?php echo $obj->static_tab ?>
  </div>
<?php elseif($obj->tab == 'import'): ?>
<div class="es-container">
  <div class="es_tpl_content">
    <div class='form-group'>
    <?php echo $obj->export_import_tab ?>
    </div>
  </div>
</div>
<?php elseif($obj->tab == 'backup'): ?>
<div class="wp-es-container">
  <div class="es-tpl-content">
    <div class='form-group'>
    <?php echo $obj->backup_tab ?>
    </div>
  </div>
</div>
<?php elseif($obj->tab == 'optimize'): ?>
<div class='form-group'>
  <?php echo $obj->optimize_tab ?>
</div>
<?php else: ?>
<div class='form-group'>
  <?php echo $obj->general_tab ?>
</div>
<?php endif; ?>
</div>
</div>