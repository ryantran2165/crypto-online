<?php
$url = parse_url(getenv('CLEARDB_DATABASE_URL'));
$hn = $url['host'];
$un = $url['user'];
$pw = $url['pass'];
$db = substr($url['path'], 1);
?>
