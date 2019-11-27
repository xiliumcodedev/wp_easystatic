<?php extract($data); ?>
<div class='wp-easy-container-main'>
<div class='wp-easy-container-top'>
  <div class='left-side'>
    <div class='form-group'>
      <p><h2><?= _e('Generated static HTML file:', 'easystatic') ?> <?= $total_static - $total_unstatic . '/' . $total_static ?></h2></p>
    </div>
</div>
<div class='right-side'>
  <p><h3><?= __('Activate Static Redirection:', 'easystatic') ?></h3></p>
  <label class="switch">
  <input type="checkbox" class='static_activate' <?php echo ($static_enable) ? 'checked' : '' ?>>
  <span class="slider round"></span>
  </label>
  <p><strong><small><?= __('This will force to override the htaccess to enabled redirection', 'easystatic') ?></small></strong></p>
</div>
</div>
<div class='es-container'>
  <div class='form-group'>
  <?= $menu_tab ?>
  </div>
<?php if($tab == 'general'): ?>
  <div class='form-group'>
  <?= $option_tmpl ?>
  </div>
<?php elseif($tab == 'static'): ?>
  <div class='form-group'>
  <?= $static_tab ?>
  </div>
<?php elseif($tab == 'import'): ?>
<div class="es-container">
  <div class="es_tpl_content">
    <div class='form-group'>
    <?= $export_import_tab ?>
    </div>
  </div>
</div>
<?php elseif($tab == 'backup'): ?>
<div class="wp-es-container">
  <div class="es-tpl-content">
    <div class='form-group'>
    <?= $backup ?>
    </div>
  </div>
</div>
<?php elseif($tab == 'optimize'): ?>
<div class='form-group'>
  <?= $optimize_tab ?>
</div>
<?php else: ?>
<div class='form-group'>
  <?= $option_tmpl ?>
</div>
<?php endif; ?>
</div>
</div>