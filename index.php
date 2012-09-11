<?php
die;
require 'lib/klein.php';
respond('/api/[*:trailing]', function () {
    $response->render('/api/index.php/api/' . $request->trailing);
});
dispatch();
