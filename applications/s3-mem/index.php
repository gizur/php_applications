<?php

$memcache_url = '10.58.226.192';
$file = './img/sample.jpg';
$memcache = new Memcache;
$name = uniqid() . ".jpg";
$start_time = time();
if ($memcache->connect($memcache_url, 11211)) {
    if (file_exists($file)) {
        $size = getimagesize($file);
        $fp = file_get_contents($file);
        if ($size and $fp) {
            $memcache->set($name, $fp);
        }
    } else {
        echo "File not exists.";
    }
}
$end_time = time();
echo "<br/>Memcache took " . ($end_time - $start_time) . " Seconds";

require_once __DIR__ . '../../lib/aws-php-sdk/sdk.class.php';
$start_time = time();

$_sThree = new AmazonS3();
$responseSThree = $_sThree->create_object(
    "gc3-image-test", $name, array(
    'body' => file_get_contents($file),
    'contentType' => 'plain/text',
    'headers' => array(
        'Cache-Control' => 'max-age',
        'Content-Language' => 'en-US',
        'Expires' =>
        'Thu, 01 Dec 1994 16:00:00 GMT',
    ))
);

if (!$responseSThree->isOK())
    echo "S3 Transfer failed.";

$end_time = time();
echo "<br/>Memcache took " . ($end_time - $start_time) . " Seconds";


?>
