<?php
include_once('connect.php');
header('Content-type:application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');

$result=mysql_query("SELECT url,title,content FROM $dbtable WHERE url='$url'");
while($row=@mysql_fetch_array($result,MYSQL_ASSOC)){
    $row[]=array('row'=>array_map('htmlspecialchars',$row));
    $array=array('url'=>$row['url'],'title'=>$row['title'],'content'=>"<h1>{$row['title']}</h1>\r\n<p>{$row['content']}</p>\r\n");
    $json=str_replace('\\/','/',json_encode($array));
    echo (isset($_GET['callback']) ? mysql_real_escape_string($_GET['callback']).'('.$json.')' : $json);
}
mysql_close($con);
?>