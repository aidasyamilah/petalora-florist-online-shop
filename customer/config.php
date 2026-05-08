<?php
$host = $_SERVER['HTTP_HOST'];

if ($host == "localhost" || $host == "127.0.0.1") {
    $base_url = "http://localhost/petalora/online_florist/";
} else {
    $base_url = "http://192.168.100.21/petalora/online_florist/";
}
?>