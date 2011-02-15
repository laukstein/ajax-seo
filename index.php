<?php
# Prevent XSS and SQL Injection
if(strpos($_SERVER['HTTP_HOST'],$_SERVER['SERVER_NAME'])===false){header('HTTP/1.0 400 Bad Request');exit;}

$title=(isset($title) ? $title : NULL);
$content=(isset($content) ? $content : NULL);

include('connect.php');
$result=mysql_query("SELECT url,title,content FROM $dbtable WHERE url='$url'");
while($row=@mysql_fetch_array($result,MYSQL_ASSOC)){
    $row[]=array('row'=>array_map('htmlspecialchars',$row));
    $urlid=$row['url'];
    $title=$row['title'];
    $content=$row['content'];
}

# Return 404 error, if url does not exist
$validate=new validate($url);
if($url==$urlid){}else{
    $validate->status('404');
    $title=$validate->title;
    $content=$validate->content;
}

# Return dir path
if(str_replace('\\','/',pathinfo($_SERVER['SCRIPT_NAME'],PATHINFO_DIRNAME))!='/'){
    $path=str_replace('\\','/',pathinfo($_SERVER['SCRIPT_NAME'],PATHINFO_DIRNAME)).'/';
}else{
    $path=str_replace('\\','/',pathinfo($_SERVER['SCRIPT_NAME'],PATHINFO_DIRNAME));
}

$websitetitle=' - Ajax SEO';
?>
<!DOCTYPE html>
<html>
<head>
<meta charset=utf-8>
<title><?=$title.$websitetitle?></title>
<link rel=stylesheet href=<?=$path?>style.css>
<link rel=author href=humans.txt type=text/plain>
<meta name=description content="Ajax SEO maximized performance - speed, availability, user-friendly">
<meta name=keywords content=ajax,seo,crawl,performance,speed,availability,user-friendly>
<script>/*Add HTML5 tag support for old browsers*/var el=['header','nav','article','footer'];for(var i=el.length-1;i>=0;i--){document.createElement(el[i]);}</script>
</head>
<body>
<div id=container>
<header>
<h2><a id=logo href=<?=$path?> title="Ajax SEO maximized performance" rel=home>Ajax SEO</a></h2>
<nav>
<ul>
<?php
$result=mysql_query("SELECT url,title FROM $dbtable ORDER BY orderid ASC");
while($row=@mysql_fetch_array($result,MYSQL_ASSOC)){
    $row[]=array('row'=>array_map('htmlspecialchars',$row));
    echo $nav='      <li';if($url==$row['url']){echo ' class=selected';}echo "><a href=\"$path{$row['url']}\" title=\"{$row['title']}\">{$row['title']}</a>\r\n";
}
?>
</ul>
</nav>
<article>
<div id=content>
<?php echo "<h1>$title</h1>\r\n<p>$content</p>\r\n"; mysql_close($con);?>
</div>
</article>
</header>
<footer>
<nav>
    <ul>
        <li itemscope itemtype=//data-vocabulary.org/Breadcrumb><a href=//github.com/laukstein/ajax-seo title="GitHub repository for Ajax SEO" itemprop=url><span itemprop=title>Latest Ajax SEO in GitHub</span></a>
        <li itemscope itemtype=//data-vocabulary.org/Breadcrumb><a href=//github.com/laukstein/ajax-seo/zipball/master title="Download latest Ajax SEO from GitHub" itemprop=url><span itemprop=title>Download</span></a>
        <li itemscope itemtype=//data-vocabulary.org/Breadcrumb><a href=//github.com/laukstein/ajax-seo/issues title="Report an issue" itemprop=url><span itemprop=title>Report an issue</span></a>
    </ul>
</nav>
</footer>
</div>
<script src=//ajax.googleapis.com/ajax/libs/jquery/1.5.0/jquery.min.js></script>
<script>!window.jQuery&&document.write(unescape('%3Cscript src=<?=$path?>jquery-1.5.min.js%3E%3C/script%3E'))</script>
<script src="<?=$path?>jquery.address.js?crawlable=1&amp;state=<?if(strlen(utf8_decode($path))>1){echo substr($path,0,-1);}else{echo $path;}?>"></script>
<script>
$.address.init(function(){
    $('li a').address();
}).change(function(event){
    $('li a').each(function(){
        if($(this).attr('href')==(($.address.state()+event.path).replace(/\/\//,'/'))){
            $(this).parent('li').addClass('selected').focus();
        }else{
            $(this).parent('li').removeClass().removeAttr('class');
        }
    });
    var timer=window.setTimeout(function(){ // Implement for timeout
        $('#content').html('Loading seems to be taking a while.');
    },3800);
    $.ajax({
        type:"GET",
        url:/*'http://lab.laukstein.com/ajax-seo/'+*/encodeURIComponent(event.path.substr(1))+'.json',
        dataType:'jsonp',
        cache:true,
        jsonpCallback:'i', // JSONP cache issue
        //async:false,
        beforeSend:function(){
            document.title='Loading...<?=$websitetitle?>';
            $('#content').html('Loading...');
        },
        success:function(data){
            window.clearTimeout(timer);
            document.title=data.title+'<?=$websitetitle?>';
            $('#content').html(data.content);
        },
        error:function(){
            window.clearTimeout(timer);
            document.title='404 Page not found';
            $('#content').html('<h1>404 Page not found</h1>\r<p>Sorry, this page cannot be found.</p>\r');
        }
    });
});
</script>
<!--<script>/*Optimized async Google Analytics snippet*/var _gaq=[['_setAccount','UA-XXXXXXXX-X'],['_trackPageview']];(function(d,t){var g=d.createElement(t),s=d.getElementsByTagName(t)[0];g.async=1;g.src='//www.google-analytics.com/ga.js';s.parentNode.insertBefore(g,s)}(document,'script'))</script>-->
</body>
</html>