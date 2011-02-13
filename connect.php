<?php
$dbhost='localhost';
$dbuser= 'root';
$dbpass='';

$dbname='test';
$dbtable='ajax_seo'; // Database table set by ajax_seo.sql

$con=@mysql_connect($dbhost,$dbuser,$dbpass)or die("Not reachable database.\nFollow installation instructions in README.md."); // mysql_error()
mysql_select_db($dbname,$con)or die();
mysql_query("SET NAMES 'utf8'");

array_map('trim',$_GET);
array_map('mysql_real_escape_string',$_GET);
$url=(isset($_GET['url']) ? mysql_real_escape_string($_GET['url']) : NULL);
$urlid=(isset($urlid) ? $urlid : NULL);

# Return 404 error, if url does not exist
class validate{
    public $title;
    public $content;
    function status($status){
        header('Location:',true,$status);
        $this->title="$status Page not found";
        $this->content='Sorry, this page cannot be found.';
    }
}
?>