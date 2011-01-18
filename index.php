<?php

$path='/ajax-seo'; // Server path to Ajax SEO. If you use root dir, leave it empty.

include('connect.php');
$result=mysql_query("SELECT * FROM $dbtable WHERE url='$url'");
while($row=@mysql_fetch_array($result,MYSQL_ASSOC)){
    $row[]=array('row'=>array_map('htmlspecialchars',$row));
    $title=$row['title'];
    $content=$row['content'];
}
?>
<html>
<head>
<meta charset=utf-8>
<title><?=$title;?></title>
<link rel=stylesheet href="<?=$path;?>/styles.css">
<script src="<?=$path;?>/jquery-1.4.4.min.js"></script>
<script src="<?=$path;?>/jquery.address.js?crawlable=true&state=<?=$path;?>"></script>
<script>
var el=['header','nav','article'];for(var i=el.length-1;i>=0;i--){document.createElement(el[i]);} // Add HTML5 tag support for old browsers
$.address.init(function(){
    $('li a').address();
}).change(function(event){
    $('li a').each(function(){
        if($(this).attr('href')==($.address.state()+event.path)){
            $(this).parent('li').addClass('selected').focus();
        }else{
            $(this).parent('li').removeClass();
        }
    });
    $('#content').ajaxStart(function(){     // Solution for beforeSend
        $(this).html('Loading...');
    });
    var timer=window.setTimeout(function(){ // Implement for timeout
        $('#content').html('Loading seems to be taking a while.');
    },1000);
    $.ajax({
        type:"GET",
        url:/*'http://lab.laukstein.com/ajax-seo/'+*/encodeURIComponent(event.path.substr(1))+'.json',
        dataType:'jsonp',
        //jsonp:'callback',
        //cache:false,
        //async:false,
        jsonpCallback:'a',
        success:function(data){
            window.clearTimeout(timer);
            document.title=data.title;
            $('#content').html(data.content);
        },
        error:function(){
            window.clearTimeout(timer);
            $('#content').html('The request failed.');
        }
    });
});
</script>
</head>
<body>
<header>
<h1>Ajax SEO</h1>
<nav>
<ul>
<?php
$result=mysql_query("SELECT * FROM $dbtable ORDER BY orderid ASC");
while($row=@mysql_fetch_array($result,MYSQL_ASSOC)){
    $row[]=array('row'=>array_map('htmlspecialchars',$row));
    $url=$row['url'];
    $title=$row['title'];
    echo $nav='      <li';if($_GET['url']==$url){echo ' class=selected';}echo "><a href=\"$path/$url\" title=\"$title\">$title</a></li>\n";
}
?>
</ul>
</nav>
<article id=content><?php echo $content; echo $error; mysql_close($conn);?></article>
<p><a href=//github.com/laukstein/ajax-seo title="GitHub repository for Ajax SEO">Latest Ajax SEO in GitHub</a> | <a href=//github.com/laukstein/ajax-seo/issues title="Report an issue">Report an issue</a></p>
</header>
</body>
</html>