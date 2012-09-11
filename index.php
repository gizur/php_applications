<?php
require 'lib/klein.php';
respond('/api/[*:trailing]', function () {
    $response->render('/api/index.php/' . $request->trailing);
});
dispatch();
