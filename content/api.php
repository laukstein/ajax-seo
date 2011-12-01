<?php
header('Content-Type:application/json; charset=utf-8');
# HTTP header caching
include('content/cache.php');
$datemod=new datemod();
$datemod->date(array('.htaccess','index.php','content/.htaccess','content/httpd.conf','content/php.ini','content/connect.php','content/api.php','content/cache.php'),MYSQL_TABLE,$url);
$datemod->cache($datemod->gmtime);

$result=mysql_query("SELECT url,name,title,content FROM ".MYSQL_TABLE." WHERE url='$url'");
while($row=@mysql_fetch_array($result,MYSQL_ASSOC)){
    $row[]=array('row'=>array_map('htmlspecialchars',$row));
    $urlid=strip_tags($row['url']);
    $name=strip_tags($row['name']);
    $title=strip_tags($row['title']);
    if(strlen($title)>0){if($name!==$title){$fn=$title;}else{$fn=$name;}}else{$fn=$name;}
    $array=array('url'=>$urlid,'title'=>$name,'content'=>"<h1>$fn</h1>\r\n<p>{$row['content']}</p>\r\n");
    $json=str_replace('\\/','/',json_encode($array)); // PHP 5.4.0  json_encode($array, JSON_UNESCAPED_UNICODE);
    echo(isset($_GET['callback']) ? $_GET['callback'].'('.$json.')' : $json);
}
mysql_close($con);
# Return 404 error, if url does not exist
$validate=new validate($url);
if($url!==$urlid){
    $validate->status();
    echo (isset($_GET['callback']) ? $_GET['callback'].'({})' : '{}');
}
?>