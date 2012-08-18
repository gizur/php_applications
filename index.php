<?php

echo "Hellow World,";
echo phpinfo();

die;
require 'lib/klein.php';
respond('gizurcloud/gizurrest/*', function () {
    $response->render('./api/index.php');
});
dispatch();
