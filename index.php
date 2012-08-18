<?php
$path = getcwd();
echo "Hellow World,".$path;
echo phpinfo();

die;
require 'lib/klein.php';
respond('gizurcloud/gizurrest/*', function () {
    $response->render('./api/index.php');
});
dispatch();
