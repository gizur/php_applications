<?php /* Smarty version 2.6.18, created on 2012-08-22 07:47:12
         compiled from modules/FieldFormulas/EditExpressions.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'vtiger_imageurl', 'modules/FieldFormulas/EditExpressions.tpl', 26, false),array('modifier', 'getTranslatedString', 'modules/FieldFormulas/EditExpressions.tpl', 39, false),)), $this); ?>

<script src="modules/FieldFormulas/resources/jquery-1.2.6.js" type="text/javascript" charset="utf-8"></script>
<script src="modules/FieldFormulas/resources/functional.js" type="text/javascript" charset="utf-8"></script>
<script src="modules/FieldFormulas/resources/json2.js" type="text/javascript" charset="utf-8"></script>
<script type="text/javascript" charset="utf-8">
	var strings = <?php echo $this->_tpl_vars['JS_STRINGS']; ?>
;
	var meta_fieldnames = new Array(<?php echo $this->_tpl_vars['VALIDATION_DATA_FIELDNAME']; ?>
)
	var meta_fieldlabels = new Array(<?php echo $this->_tpl_vars['VALIDATION_DATA_FIELDLABEL']; ?>
);
	var meta_fielddatatypes = new Array(<?php echo $this->_tpl_vars['VALIDATION_DATA_FIELDDATATYPE']; ?>
);
</script>
<script src="modules/FieldFormulas/resources/editexpressionscript.js" type="text/javascript" charset="utf-8"></script>


<table align="center" border="0" cellpadding="0" cellspacing="0" width="98%">
<tbody><tr>
        <td valign="top"><img src="<?php echo vtiger_imageurl('showPanelTopLeft.gif', $this->_tpl_vars['THEME']); ?>
"></td>
        <td class="showPanelBg" style="padding: 10px;" valign="top" width="100%">
<br>
<div align=center>
		<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => 'SetMenu.tpl', 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
		
<!-- DISPLAY -->
<div id="view">
	<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => 'modules/FieldFormulas/ModuleTitle.tpl', 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
	<input type="hidden" id="pick_module" name="pick_module" value=<?php echo $this->_tpl_vars['FORMODULE']; ?>
 />
	<table class="tableHeading" width="100%" border="0" cellspacing="0" cellpadding="5" align="center">
		<tr>
			<td class="big" nowrap="nowrap">
				<strong><span id="module_info"><?php echo $this->_tpl_vars['MOD']['LBL_MODULE_INFO']; ?>
 "<?php echo getTranslatedString($this->_tpl_vars['FORMODULE'], $this->_tpl_vars['MODULE']); ?>
"</span></strong>
			</td>
			<td class="small" align="right">
				<span id="new_field_expression_busyicon"><b><?php echo $this->_tpl_vars['MOD']['LBL_CHECKING']; ?>
</b><img src="<?php echo vtiger_imageurl('vtbusy.gif', $this->_tpl_vars['THEME']); ?>
" border="0"></span>
				<input type="button" class="crmButton create small"
					value="<?php echo getTranslatedString('LBL_NEW_FIELD_EXPRESSION_BUTTON', $this->_tpl_vars['MODULE']); ?>
" id='new_field_expression' style="display: none;"/>
					
				<span id="status_message" class="helpmessagebox" style='font-weight: bold; display: none;'></span>
			</td>
		</tr>
	</table>
	<br>
	<div id='editpopup' class='layerPopup' style='display:none;' >
		<div id='editpopup_draghandle' style='cursor: move;'>
		<table width="100%" cellspacing="0" cellpadding="5" border="0" class="layerHeadingULine">
			<tr>
				<td width="60%" align="left" class="layerPopupHeading">
					<?php echo getTranslatedString('LBL_EDIT_EXPRESSION', $this->_tpl_vars['MODULE']); ?>

					</td>
				<td width="40%" align="right">
					<a href="javascript:void(0);" id="editpopup_close">
						<img border="0" align="middle" src="<?php echo vtiger_imageurl('close.gif', $this->_tpl_vars['THEME']); ?>
"/>
					</a>
				</td>
			</tr>
		</table>
		</div>
		<table width="100%" bgcolor="white" align="center" border="0" cellspacing="0" cellpadding="5">
			<tr>
				<td class='dvtCellLabel' align="right">
					<b><?php echo getTranslatedString('LBL_TARGET_FIELD', $this->_tpl_vars['MODULE']); ?>
</b>
				</td>
				<td class='dvtCellInfo'> 
					<select id='editpopup_field' class='small'><option></option></select>
				</td>
			</tr>
			<tr valign="top">
				<td class='dvtCellLabel' align="right">
					<b><?php echo getTranslatedString('LBL_EXPRESSION', $this->_tpl_vars['MODULE']); ?>
</b>
				</td>
				<td class='dvtCellInfo' align="left">
					<table width="100%" border="0" cellspacing="0" cellpadding="2" align="center">
						<tr valign="top">
							<td>
								<select id='editpopup_fieldnames' class='small'></select>
							
								<select id='editpopup_functions' class='small'>
									<option value=""><?php echo $this->_tpl_vars['MOD']['LBL_USE_FUNCTION_DASHDASH']; ?>
</option>
								</select>
							</td>
						</tr>
						<tr valign="top">
							<td>
								<textarea name="Name" rows="10" cols="50" id='editpopup_expression'></textarea>
							</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
		<table width="100%" cellspacing="0" cellpadding="5" border="0" class="layerPopupTransport">
			<tr><td align="center">
				<input type="button" class="crmButton small save" value="<?php echo $this->_tpl_vars['APP']['LBL_SAVE_BUTTON_LABEL']; ?>
" name="save" id='editpopup_save'/> 
				<input type="button" class="crmButton small cancel" value="<?php echo $this->_tpl_vars['APP']['LBL_CANCEL_BUTTON_LABEL']; ?>
" name="cancel" id='editpopup_cancel'/>
			</td></tr>
		</table>
		
		<table width="100%" cellspacing="1" cellpadding="5" border="0" class="helpmessagebox">
			<tr valign="top">
				<td><b><?php echo $this->_tpl_vars['MOD']['LBL_TARGET_FIELD']; ?>
</b></td>
				<td><b><?php echo $this->_tpl_vars['MOD']['LBL_EXPRESSION']; ?>
</b></td>
			</tr>
			<tr valign="top">
				<td>Custom Revenue</td>			
				<td><i>annual_revenue</i> / 12</td>
			</tr>			
			<tr valign="top">
				<td>Full Name</td>
				<td>
					<font color=blue>if</font> <i>mailingcountry</i> == "India" <font color=blue>then</font> <font color=blue>concat</font>(<i>firstname</i>," ",<i>lastname</i>) <font color=blue>else</font> <font color=blue>concat</font>(<i>lastname</i>," ",<i>firstname</i>) <font color=blue>end</font>
				</td>
			</tr>			
		</table>
	</div>
	<table class="listTable" width="100%" border="0" cellspacing="0" cellpadding="5" id='expressionlist'>
		<tr>
			<td class="colHeader small" width="20%">
				<?php echo getTranslatedString('LBL_FIELD', $this->_tpl_vars['MODULE']); ?>
				
			</td>
			<td class="colHeader small" width="65">
				<?php echo getTranslatedString('LBL_EXPRESSION', $this->_tpl_vars['MODULE']); ?>

			</td>
			<td class="colHeader small" width="15%">
				<?php echo getTranslatedString('LBL_SETTINGS', $this->_tpl_vars['MODULE']); ?>

			</td>
		</tr>
	</table>
	<div align='right' style='padding-right: 5px;' id="expressionlist_busyicon"><b><?php echo $this->_tpl_vars['MOD']['LBL_CHECKING']; ?>
</b><img src="<?php echo vtiger_imageurl('vtbusy.gif', $this->_tpl_vars['THEME']); ?>
" border="0"></div>
</div>

</td>
        </tr>
        </table>
        </td>
        </tr>
        </table>
        </div>

        </td>
        <td valign="top"><img src="<?php echo vtiger_imageurl('showPanelTopRight.gif', $this->_tpl_vars['THEME']); ?>
"></td>
        </tr>
</tbody>
</table>
<br>