<?php
echo '<table><tr><td><input class="crmbutton small cancel" type="button" value="'.getTranslatedString('LBL_BACK_BUTTON').'" onclick="back();"/></td></tr></table>';
if (isset($_REQUEST['salesorder_no']) && !empty($_REQUEST['salesorder_no']))
    echo str_replace('${number}', $_REQUEST['salesorder_no'], getTranslatedString('LBL_SUCCESS_IN_SAVING_SALESORDER'));
else
    echo getTranslatedString('LBL_PROBLEM_IN_SAVING_SALESORDER');
?>
</td>
<td>&nbsp;</td>
</tr>
</table>
</td>
</tr>
</table>
<script type="text/javascript">
   function back()
{
        var protocol=window.location.protocol;
        var hostname=window.location.hostname;
        var path=window.location.pathname;
        var url=protocol+"//"+hostname+path+"?module=CikabTroubleTicket&action=index";
        window.location.assign(url);
}
</script>
