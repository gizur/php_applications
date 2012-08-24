<?php /* Smarty version 2.6.18, created on 2012-08-22 07:46:33
         compiled from Settings/FieldUI.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'vtiger_imageurl', 'Settings/FieldUI.tpl', 15, false),array('modifier', 'getTranslatedString', 'Settings/FieldUI.tpl', 28, false),)), $this); ?>
<?php if ($this->_tpl_vars['_FIELD_UI_TYPE'] == 56): ?>
<input id="<?php echo $this->_tpl_vars['_FIELD_ELEMENT_ID']; ?>
" name="<?php echo $this->_tpl_vars['_FIELD_ELEMENT_ID']; ?>
" type="checkbox" class="small" <?php if ($this->_tpl_vars['_FIELD_SELECTED_VALUE'] == 1): ?>checked<?php endif; ?>>
<?php elseif ($this->_tpl_vars['_FIELD_UI_TYPE'] == 23 || $this->_tpl_vars['_FIELD_UI_TYPE'] == 5 || $this->_tpl_vars['_FIELD_UI_TYPE'] == 6): ?>
<input class="small" id="<?php echo $this->_tpl_vars['_FIELD_ELEMENT_ID']; ?>
" name="<?php echo $this->_tpl_vars['_FIELD_ELEMENT_ID']; ?>
" type="text" size="11" maxlength="10" value="<?php if ($this->_tpl_vars['_FIELD_SELECTED_VALUE'] != 0): ?><?php echo $this->_tpl_vars['_FIELD_SELECTED_VALUE']; ?>
<?php endif; ?>">
<img align="absmiddle" src="<?php echo vtiger_imageurl('btnL3Calendar.gif', $this->_tpl_vars['THEME']); ?>
" id="jscal_trigger_<?php echo $this->_tpl_vars['_FIELD_ELEMENT_ID']; ?>
" />
<script type="text/javascript" id='layouteditor_<?php echo $this->_tpl_vars['_FIELD_ELEMENT_ID']; ?>
' class='layouteditor_javascript'>
	Calendar.setup ({
		inputField : "<?php echo $this->_tpl_vars['_FIELD_ELEMENT_ID']; ?>
", ifFormat : "<?php echo $this->_tpl_vars['JS_DATEFORMAT']; ?>
", showsTime : false, button : "jscal_trigger_<?php echo $this->_tpl_vars['_FIELD_ELEMENT_ID']; ?>
", singleClick : true, step : 1
	})
</script>
<?php elseif ($this->_tpl_vars['_FIELD_UI_TYPE'] == 15 || $this->_tpl_vars['_FIELD_UI_TYPE'] == 16 || $this->_tpl_vars['_FIELD_UI_TYPE'] == 33): ?>
<select id="<?php echo $this->_tpl_vars['_FIELD_ELEMENT_ID']; ?>
" name="<?php echo $this->_tpl_vars['_FIELD_ELEMENT_ID']; ?>
" class="small">
	<?php $_from = $this->_tpl_vars['_ALL_AVAILABLE_VALUES']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['_PICKLIST_VALUE']):
?>
	<option value="<?php echo $this->_tpl_vars['_PICKLIST_VALUE']; ?>
"
		<?php if ($this->_tpl_vars['_PICKLIST_VALUE'] == $this->_tpl_vars['_FIELD_SELECTED_VALUE']): ?>
		selected
		<?php endif; ?>
	><?php echo getTranslatedString($this->_tpl_vars['_PICKLIST_VALUE'], $this->_tpl_vars['MODULE']); ?>

	</option>
	<?php endforeach; else: ?>
	<option value=""></option>
	<option value="" style='color: #777777' disabled><?php echo $this->_tpl_vars['APP']['LBL_NONE']; ?>
</option>
	<?php endif; unset($_from); ?>
</select>
<?php else: ?>
<input id="<?php echo $this->_tpl_vars['_FIELD_ELEMENT_ID']; ?>
" name="<?php echo $this->_tpl_vars['_FIELD_ELEMENT_ID']; ?>
" type="text" class="detailedViewTextBox" value="<?php echo $this->_tpl_vars['_FIELD_SELECTED_VALUE']; ?>
" />
<?php endif; ?>