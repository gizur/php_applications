<?php

$memcache_url = '10.58.226.192';
$file = './img/sample.jpg';
$memcache = new Memcache;

if ($memcache->connect($memcache_url, 11211)) {
    header('Content-Type: image/jpeg');  
    $file = $memcache->get($_GET['name']);
    header('Content-Length: '.  strlen($file));
    echo $file;
}
?>
