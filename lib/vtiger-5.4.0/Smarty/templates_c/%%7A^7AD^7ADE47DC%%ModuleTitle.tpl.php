<?php /* Smarty version 2.6.18, created on 2012-08-22 07:47:12
         compiled from modules/FieldFormulas/ModuleTitle.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'getTranslatedString', 'modules/FieldFormulas/ModuleTitle.tpl', 8, false),)), $this); ?>
<table width="100%" cellspacing="0" cellpadding="5" border="0" class="settingsSelUITopLine"><tbody>
	<tr>
		<td width="50" valign="top" rowspan="2">
			<img width="48" height="48" border="0" src="modules/FieldFormulas/resources/FieldFormulas.png"/>
		</td>
		<td valign="bottom" class="heading2">
			<b><a href="index.php?module=Settings&action=ModuleManager&parenttab=Settings">Module Manager</a> > 
			<a href="index.php?module=Settings&action=ModuleManager&module_settings=true&formodule=<?php echo $this->_tpl_vars['FORMODULE']; ?>
&parenttab=Settings"><?php echo getTranslatedString($this->_tpl_vars['FORMODULE'], $this->_tpl_vars['MODULE']); ?>
</a> > 
				<?php echo $this->_tpl_vars['MOD'][$this->_tpl_vars['PAGE_NAME']]; ?>

		</td>
	</tr>
	<tr>
		<td valign="top" class="small"><?php echo $this->_tpl_vars['MOD'][$this->_tpl_vars['PAGE_DESC']]; ?>
 </td>
	</tr>
</tbody></table>
<br><br>
