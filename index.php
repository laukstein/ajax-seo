<?php
# Prevent XSS and SQL Injection
if(strpos($_SERVER['HTTP_HOST'],$_SERVER['SERVER_NAME'])===false){
    header('Content-Type:text/plain');
    header('X-Robots-Tag:none',true);
    header('Status:400 Bad Request',true,400);
    exit('400 Bad Request');
}

# Gzip
if(!ob_start('ob_gzhandler')){ob_start();}

# Database settings
include('content/connect.php');
    
/*# HTTP header caching
include('content/cache.php');
$datemod=new datemod();
$datemod->date(array('.htaccess','index.php','content/.htaccess','content/httpd.conf','content/php.ini','content/connect.php','content/api.php','content/cache.php'),MYSQL_TABLE,$url);
$datemod->cache($datemod->gmtime);*/

if(MYSQL_CON){
    # JSON/JSONP respond
    if(isset($_GET['api'])){include('content/api.php');exit();}
    
    $title=(isset($title)? $title: null);
    $content=(isset($content)? $content: null);
    
    $result=mysql_query("SELECT url,name,title,content FROM ".MYSQL_TABLE." WHERE url='$url'");
    while($row=@mysql_fetch_array($result,MYSQL_ASSOC)){
        $row[]=array('row'=>array_map('htmlspecialchars',$row));
        $urlid=$row['url'];
        $name=$row['name'];
        $title=$row['title'];
        $content=$row['content'];
    }
    
    # Return 404 error, if url does not exist
    $validate=new validate($url);
    if($url==$urlid){}else{
        $validate->status();
        $title=$validate->title;
        $content=$validate->content;
    }
}

$note=(isset($note)? $note: null);
$name=(isset($name)? $name: null);
$additional_title=' - Ajax SEO';
$title_installation=(isset($title_installation)? $title_installation: null);
$installation=(isset($installation)? $installation: null);

?>
<!DOCTYPE html>
<html>
<head>
<meta charset=utf-8>
<title><?php echo$name.$additional_title?></title>
<link rel=stylesheet href=<?php echo$path?>images/style.css>
<meta name=description content="Ajax SEO maximized performance - speed, accessibility, user-friendly">
<meta name=keywords content=ajax,seo,crawl,performance,speed,availability,user-friendly>
<script>/*Add HTML5 tag support for old browsers*/var el=['header','nav','article','footer'];for(var i=el.length-1;i>=0;i--){document.createElement(el[i]);}</script>
</head>
<body itemscope itemtype="http://schema.org/WebPage">
<?php
if($note==null){}else{
    echo"<div id=note>$note</div>";
}
?>
<div id=container>
<header>
<a id=logo href=<?php echo$path?> title="Ajax SEO maximized performance" rel=home>Ajax SEO<?php echo$title_installation;?></a>
<nav>
<?php
if(MYSQL_CON){
    $result=mysql_query('SELECT url,name,title FROM '.MYSQL_TABLE.' ORDER BY `order` ASC');
    while($row=@mysql_fetch_array($result,MYSQL_ASSOC)){
        $row[]=array('row'=>array_map('htmlspecialchars',$row));
        echo'<a';
        if($url==$row['url']){echo' class=selected';}
        echo" href=\"$path{$row['url']}\"";
        if(strlen($row['title'])>0){if($row['name']!==$row['title']){echo" title=\"{$row['title']}\"";}}
        echo">{$row['name']}</a>\r\n";
    }
}
?>
</nav>
<article>
<span id=content>
<?php
if(MYSQL_CON){
    if(strlen($title)>0){if($name!==$title){$name=$title;}}
    echo"<h1>$name</h1>\r\n<p>$content</p>\r\n";
    mysql_close($con);
}else{
    echo$installation;
}
?>
</span>
</article>
</header>
<footer>
<nav itemprop=breadcrumb>
    <a href=//github.com/laukstein/ajax-seo title="GitHub repository for Ajax SEO">Latest Ajax SEO in GitHub</a> >
    <a href=//github.com/laukstein/ajax-seo/zipball/master title="Download latest Ajax SEO from GitHub">Download</a> >
    <a href=//github.com/laukstein/ajax-seo/issues>Report an issue</a>
</nav>
</footer>
</div>
<?php if(MYSQL_CON){ ?>
<script src=//ajax.googleapis.com/ajax/libs/jquery/1.6.4/jquery.min.js></script>
<script>window.jQuery || document.write('<script src=<?php echo$path?>images/jquery-1.6.4.min.js>\x3C/script>')</script>
<script src=<?php echo$path?>images/jquery.address.js></script>
<script>
var nav=$('header nav a');
$.address.crawlable(1).state('<?php if(strlen(utf8_decode($path))>1){echo substr($path,0,-1);}else{echo$path;}?>').init(function(){
    nav.address();
}).change(function(e){
    var content=$('#content');
    var timer=window.setTimeout(function(){ // Implement for timeout
        content.html('Loading seems to be taking a while.');
    },3800),clearTimeout=window.clearTimeout(timer);
    $.ajax({
        type:'GET',
        url:/*'http://lab.laukstein.com/ajax-seo/'+*/'api'+(e.path.length!=1 ? '/'+encodeURIComponent(e.path.toLowerCase().substr(1)) : ''),
        dataType:'json',        // jsonp
        cache:true,
        //jsonpCallback:'i',    // JSONP cache issue
        beforeSend:function(){
            document.title='Loading...';
            content.fadeTo(200,0.33);
        },
        success:function(data,textStatus,jqXHR){
            clearTimeout;
            nav.each(function(){
                if($(this).attr('href')==(($.address.state()+decodeURI(e.path)).replace(/\/\//,'/'))){
                    $(this).addClass('selected').focus();
                }else{
                    $(this).removeAttr('class');
                }
            });
            document.title=data.title+'<?php echo$additional_title?>';
            content.fadeTo(20,1).removeAttr('style').html(data.content);
            if($.browser.msie){content.removeAttr('filter');}
        },
        error:function(jqXHR,textStatus,errorThrown){
            clearTimeout;
            nav.each(function(){
                $(this).removeAttr('class');
            });
            document.title='404 Page not found';
            content.fadeTo(20,1).removeAttr('style').html('<h1>404 Page not found</h1>\r<p>Sorry, this page cannot be found.</p>\r');
            if($.browser.msie){content.removeAttr('filter');}
        }
    });
});
// Optimized async Google Analytics snippet
//var _gaq=[['_setAccount','UA-XXXXXXXX-X'],['_trackPageview']];(function(d,t){var g=d.createElement(t),s=d.getElementsByTagName(t)[0];g.async=1;g.src='//www.google-analytics.com/ga.js';s.parentNode.insertBefore(g,s)}(document,'script'))
</script>
<?php } ?>
</body>
</html>