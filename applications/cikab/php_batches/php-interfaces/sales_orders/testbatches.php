<?php
ini_set('display_errors', 'On');
error_reporting(E_ALL);
$output = null;
switch($_GET['action']){
    case 'setup-tables':
        $output = shell_exec('./setup-tables.sh');
        break;
    case 'phpcronjob1':
        $output = shell_exec('./phpcronjob1.sh');
        break;
    case 'phpcronjob2':
        $output = shell_exec('./phpcronjob2.sh');
        break;
    case 'phpcronjob3':
        $output = shell_exec('./phpcronjob3.sh');
        break;
    case 'phpinfo':
        phpinfo();
        break;
}

echo "<pre>$output</pre>";
?>
