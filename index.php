<?php
include('connect.php');
$result=mysql_query("SELECT * FROM $dbtable WHERE url='$url'");
while($row=mysql_fetch_array($result,MYSQL_ASSOC)){
    $row[]=array('row'=>array_map('htmlspecialchars',$row));
    $title=$row['title'];
    $content=$row['content'];
}
$path='/jquery/address/samples/json';
?>
<html>
<head>
<meta charset="utf-8">
<title><?=$title;?></title>
<link type="text/css" href="<?=$path;?>/styles.css" rel="stylesheet">
<script src="<?=$path;?>/jquery-1.4.4.min.js"></script>
<script src="<?=$path;?>/jquery.address-1.3.2.min.js?crawlable=true&state=<?=$path;?>"></script>
<script>
var el=['header','nav','article'];for(var i=el.length-1;i>=0;i--){document.createElement(el[i]);}
$.address.init(function(){
    $('li a').address();
}).change(function(event){
    $('li a').each(function(){
        if($(this).attr('href')==($.address.state()+event.path)){
            $(this).parent('li').addClass('selected').focus();
        }else{
            $(this).parent('li').removeClass('selected');
        }
    });
    $.ajaxSetup({cache:true,scriptCharset:'utf-8',contentType:'application/json; charset=UTF-8'}); // Cyrillic and Semitic $.getJSON requests somehow does not work on IE. Besides of displaying #!/контакты IE in address bar has #!/%D7%A6%D7%95%D7%A8-%D7%A7%D7%A9%D7%A8 
    $.getJSON(event.path.substr(1)+'.json',function(data){
        document.title=data.title; // Faster then $('title').html(data.title); http://jsperf.com/rename-title
        $('#content').html(data.content);
    });
});
</script>
</head>
<body>
<header>
<h1>jQuery Address JSON</h1>
<nav><ul>
<?php
$result=mysql_query("SELECT * FROM $dbtable ORDER BY orderid ASC");
while($row=mysql_fetch_array($result, MYSQL_ASSOC)){
    $row[]=array('row'=>array_map('htmlspecialchars',$row));
    $url=$row['url'];
    $title=$row['title'];
    echo $nav='      <li';if($_GET['url']==$url){echo ' class="selected"';}echo "><a href=\"$path/$url\">$title</a></li>\n";
}
?>
</ul></nav>
<article id="content"><?php echo $content; mysql_close($conn);?></article>
</header>
</body>
</html>