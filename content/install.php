<?php
// Define MySQL connection and error status
if(MYSQL_CON){
    $change=file_get_contents($f);
    $change=preg_replace("/define\('(MYSQL_CON)',true\);/","define('$1',false);",$change);
    $change=preg_replace("/define\('(MYSQL_ERROR)',true\);/","define('$1',false);",$change);
    if(!@is_writable($f)){@chmod($f,0755);} // Change connect.php file permissions if needed
    $fopen=fopen($f,'w');
    fwrite($fopen,$change);
    fclose($fopen);
    header('Location:'.$_SERVER['REQUEST_URI']);
    exit;
}

// Add MySQL settings
if($_SERVER['REQUEST_METHOD']=='POST'){
    $change=file_get_contents($f);
    $change=preg_replace("/define\('(MYSQL_HOST)','(.*)'\);/","define('$1','".trim($_POST['host'])."');",$change);
    $change=preg_replace("/define\('(MYSQL_USER)','(.*)'\);/","define('$1','".trim($_POST['user'])."');",$change);
    $change=preg_replace("/define\('(MYSQL_PASS)','(.*)'\);/","define('$1','".trim($_POST['pass'])."');",$change);
    $change=preg_replace("/define\('(MYSQL_DB)','(.*)'\);/","define('$1','".trim($_POST['db'])."');",$change);
    $change=preg_replace("/define\('(MYSQL_TABLE)','(.*)'\);/","define('$1','".trim($_POST['table'])."');",$change);
    $change=preg_replace("/define\('(MYSQL_ERROR)',false\);/","define('$1',true);",$change);
    if(!@is_writable($f)){@chmod($f,0755);} // Change connect.php file permissions if needed
    $fopen=fopen($f,'w');
    fwrite($fopen,$change);
    fclose($fopen);
    header('Location:'.$_SERVER['REQUEST_URI']);
    exit;
}

// Set REP header to disalow Installation page from SERPs snipping and archiving
header('X-Robots-Tag:index, noarchive, nosnippet',true);

// Installer setup
$name='Installation';
$title_installation=' - '.$name;
$error=((MYSQL_ERROR)? '<span id=error>Could not connect to server</span>': null);
$installation='<style>
ul.installation{
    padding:0;
    list-style:none;
}
ul.installation label{
    display:inline-block;
    width:105px;
}
form{height:241px;}
input{
    padding:2px;
    font-size:15px;
    line-height:20px;
}
#install{
    margin-left:105px;
    padding:2px 15px;
    border-radius:3px;
    border:1px solid #ccc;
    background-color:#ddd;
    cursor:pointer;
}
#install:hover{background-color:#e3e3e3;}
#error{
    margin-left:105px;
    color:#ff2121;
}
</style>
<h1>MySQL connection details</h1>
<form method=post>
<ul class=installation>
    <li><label for=db>Database Name</label><input id=db type=text name=db value="'.MYSQL_DB.'" placeholder=ajax_seo>
    <li><label for=user>User Name</label><input id=user type=text name=user value="'.MYSQL_USER.'">
    <li><label for=pass>Password</label><input id=pass type=password name=pass>
    <li><label for=host>Database Host</label><input id=host name=host value="'.MYSQL_HOST.'" placeholder=localhost>
    <li><label for=table>Table</label><input id=table type=text name=table value="'.MYSQL_TABLE.'">
    <li><input id=install type=submit name=install value=Install><input type=hidden name=submitted value=true>
</ul>'.$error.'
</form>
Your MySQL connection details will be saved in connect.php, after you can open and edit it trough text editor.';
?>