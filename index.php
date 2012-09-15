<?php
require 'lib/klein.php';
$baseURL = '/gizurcloud';

respond($baseURL . '/api/[:trailing]', function ($request,$response) {
    $response->query(array('/api/' . $request->trailing));
    $response->render('./api/index.php');
});

respond($baseURL . '/[a:clientid]/trailer_app/[:trailing]', function ($request,$response) {
    $response->query(array($request->trailing));
    $response->render('./applications/' . $request->clientid . '/trailer_app_portal/index.php');
});

respond($baseURL . '/[a:clientid]/vtiger/index.php?[:trailing]', function($request, $response) {
    $response->query(array(
        $request->trailing, 
        'clientid' => $request->clientid,
    ));
    $response->render('./lib/vtwrapper-index.php');
});
dispatch();
