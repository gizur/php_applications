<?php

$memcache_url = '10.58.226.192';
$file = './img/sample.jpg';
$memcache = new Memcache;

if ($memcache->connect($memcache_url, 11211)) {
    $start_time = time();
    $name = uniqid() . ".jpg";
    if (file_exists($file)) {
        $size = getimagesize($file);
        $fp = file_get_contents($file);
        if ($size and $fp) {
            $memcache->set($name, $fp);
        }
    } else {
        echo "File not exists.";
    }
    $end_time = time();

    echo "<br/>Memcache took " . ($end_time - $start_time) . " Seconds";
}

?>
