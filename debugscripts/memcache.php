<?php

$memcache = new Memcache;
$server_hostname = '173.203.108.8';
$memcache->connect($server_hostname, 11211) or die ("Could not connect");

$tmp_object = new stdClass;
$tmp_object->str_attr = 'test';
$tmp_object->int_attr = time();
$key_string = 'abc';

$get_result = $memcache->get($key_string);
if($get_result != false) {
        echo "<h1>CACHE HIT!</h1><p>Data from the cache:<br/>\n";
        var_dump($get_result);
        exit(0);
}

echo "<h1>CACHE MISS!</h1>";
$memcache->set($key_string, $tmp_object, false, 5) or die ("Failed to save data at the server using key: $key_string");
echo "<p>Stored data in the cache (data will expire in 5 seconds)<br/>\n";

$get_result = $memcache->get($key_string);
echo "<p>Fetched key '$key_string' from the cache:<br/>\n";
var_dump($get_result);

?>