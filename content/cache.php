<?php
function cache($file,$timestamp){
    $gmtime=gmdate('D, d M Y H:i:s T',$timestamp);
    if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])||isset($_SERVER['HTTP_IF_NONE_MATCH'])){
        if($_SERVER['HTTP_IF_MODIFIED_SINCE']==$gmtime||str_replace('"','',stripslashes($_SERVER['HTTP_IF_NONE_MATCH']))==md5($timestamp.$file)){
            header('Status:304 Not Modified',true,304);exit();
        }
    }
    header("Last-Modified:$gmtime");
}
?>