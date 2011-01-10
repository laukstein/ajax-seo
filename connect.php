<?php
$dbhost='localhost';
$dbuser= 'root';
$dbpass='';

$dbname='test';
$dbtable='ajax_seo'; // Database table set by ajax_seo.sql

$conn=@mysql_connect($dbhost,$dbuser,$dbpass)or die("Not reachable database.\nFollow installation instructions in README.md."); // mysql_error()
mysql_select_db($dbname,$conn)or die();
mysql_query("SET NAMES 'utf8'");

array_map('trim',$_GET);
array_map('mysql_real_escape_string',$_GET);
$url=mysql_real_escape_string($_GET['url']);
?>