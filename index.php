<?php
include('connect.php');
$result=mysql_query("SELECT * FROM $dbtable WHERE url='$url'");
while($row=@mysql_fetch_array($result,MYSQL_ASSOC)){
    $row[]=array('row'=>array_map('htmlspecialchars',$row));
    $title=$row['title'];
    $content=$row['content'];
}
$path='/address-json'; // Path to Address JSON
?>
<html>
<head>
<meta charset="utf-8">
<title><?=$title;?></title>
<link type="text/css" href="<?=$path;?>/styles.css" rel="stylesheet">
<script src="<?=$path;?>/jquery-1.4.4.min.js"></script>
<script src="<?=$path;?>/jquery.address-1.3.2.js?crawlable=true&state=<?=$path;?>"></script>
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
    $.ajax({
        type:"GET",
        url:encodeURIComponent(event.path.substr(1))+'.json',
        dataType:'json',
        //cache:false,
        async:false,
        success:function(data){
            document.title=data.title;
            $('#content').html(data.content);
        },
        error:function(request,status,error){
            $('#content').html('The request failed. Try to refresh page.');
        }
    });
});
</script>
</head>
<body>
<header>
<h1><span>jQuery</span> Address JSON</h1>
<nav><ul>
<?php
$result=mysql_query("SELECT * FROM $dbtable ORDER BY orderid ASC");
while($row=@mysql_fetch_array($result, MYSQL_ASSOC)){
    $row[]=array('row'=>array_map('htmlspecialchars',$row));
    $url=$row['url'];
    $title=$row['title'];
    echo $nav='      <li';if($_GET['url']==$url){echo ' class="selected"';}echo "><a href=\"$path/$url\" title=\"$title\">$title</a></li>\n";
}
?>
</ul></nav>
<article id="content"><?php echo $content; mysql_close($conn);?></article>
</header>
</body>
</html>