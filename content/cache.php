<?php
class datemod{
    public $gmtime;
    function date($files,$dbtable,$url){
        foreach($files as $val){
            $mod=date('Y/m/d H:i:s',filemtime($val));
            $array[]=$mod;
        }
        $fmod=max($array);
        $result=mysql_query("SELECT DATE_FORMAT(pubdate,'%Y/%m/%d %H:%i:%s') AS pubdate FROM $dbtable WHERE url='$url'");
        while($row=@mysql_fetch_array($result,MYSQL_ASSOC)){
            $row[]=array('row'=>array_map('htmlspecialchars',$row));
            $pubdate=$row['pubdate'];
        }
        $maxmod=max($fmod,$pubdate);
        $this->gmtime=date('D, d M Y H:i:s T',strtotime($maxmod));
    }
    function cache($gmtime){
        if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])||isset($_SERVER['HTTP_IF_NONE_MATCH'])){
            if($_SERVER['HTTP_IF_MODIFIED_SINCE']==$gmtime){
                header('Status:304 Not Modified',true,304);
                exit();
            }
        }
        header("Last-Modified:$gmtime");
    }
}
?>