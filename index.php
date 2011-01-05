<?php
include('connect.php');
$result=mysql_query("SELECT * FROM $dbtable WHERE url='$url'");
while($row=@mysql_fetch_array($result,MYSQL_ASSOC)){
    $row[]=array('row'=>array_map('htmlspecialchars',$row));
    $title=$row['title'];
    $content=$row['content'];
}
$path='/jsonp-ajax-seo'; // Path to JSONP Ajax SEO; if you use root dir, leave it empty.
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
        url:encodeURIComponent(event.path.substr(1))+'.json',//'http://other.domain.here/'+encodeURIComponent(event.path.substr(1))+'.json',
        dataType:'jsonp',
        //jsonp:'callback',
        //cache:false,
        //async:false,
        jsonpCallback:'a',
        //timeout:5000,
        //beforeSend:function(data){
        //  $('#content').html('Loading...');
        //},
        success:function(data){
            document.title=data.title;
            $('#content').html(data.content);
        }//,
        //error:function(request,status,error){
        //    $('#content').html('The request failed. Try to refresh page.');
        //}
    });
});
</script>
</head>
<body>
<header>
<h1>JSONP Ajax SEO</h1>
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
<p><a href="https://github.com/laukstein/jsonp-ajax-seo" title="GitHub repository for JSONP Ajax SEO">Latest JSONP Ajax SEO in GitHub</a> | <a href="https://github.com/laukstein/jsonp-ajax-seo/issues" title="Report a bug or issue for JSONP Ajax SEO">Report a bug or issue</a></p>
</header>
</body>
</html>