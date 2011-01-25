<?php
include_once('connect.php');
header('Content-type:application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');

$result=mysql_query("SELECT url,title,content FROM $dbtable WHERE url='$url'");
while($row=@mysql_fetch_array($result,MYSQL_ASSOC)){
    $row[]=array('row'=>array_map('htmlspecialchars',$row));
    $array=array('url'=>$row['url'],'title'=>$row['title'],'content'=>$row['content']);
    $json=str_replace('\'','\\',json_encode($array)); // $url=$row['url'];$title=$row['title'];$content=$row['content'];echo "{\"url\":\"$url\",\"title\":\"$title\",\"content\":\"$content\"}";
    if($_GET['callback']){
    	echo "{$_GET['callback']}($json)"; // JSONP
    }else{
    	echo $json;                        // JSON
    }
}
mysql_close($con);
?>