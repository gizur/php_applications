<?php

require 'lib/klein.php';
respond('gizurcloud/gizurrest/*', function () {
    $response->render('./api/index.php');
});
dispatch();
