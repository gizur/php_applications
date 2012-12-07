<?php
if (isset($_REQUEST['salesorder_no']) && !empty($_REQUEST['salesorder_no']))
    echo str_replace('${number}', $_REQUEST['salesorder_no'], getTranslatedString('LBL_SUCCESS_IN_SAVING_SALESORDER'));
else
    echo getTranslatedString('LBL_PROBLEM_IN_SAVING_SALESORDER');
?>
</td>
<td>&nbsp;</td>
</tr>