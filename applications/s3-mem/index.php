<?php

$memcache_url = '10.58.226.192';
$file = './img/sample.jpg';
$memcache = new Memcache;

if (!$memcache->connect($memcache_url, 11211)) {
    $start_time = time();
    $arr = array();
    $name = uniqid() . ".jpg";
    if (file_exists($file)) {
        $size = getimagesize($file, $arr);
        $fp = fopen($file, 'rb');
        if ($size and $fp) {
            $memcache->set($name, fpassthru($fp));
        }
    } else {
        echo "File not exists.";
    }
    $end_time = time();

    echo "<br/>Memcache took " . ($end_time - $start_time) . " Seconds";
}

?>
