<?php
# Prevent XSS and SQL Injection
if(strpos($_SERVER['HTTP_HOST'],$_SERVER['SERVER_NAME'])===false){header('Status:400 Bad Request',true,400);exit('400 Bad Request');}
# JSON respond
if(isset($_GET['api'])){include('content/api.php');exit();}
# Gzip
if(!ob_start('ob_gzhandler')){ob_start();}
# Database settings
include('content/connect.php');
# HTTP header caching
include('content/cache.php');
$datemod=new datemod();
$datemod->date(array('.htaccess','index.php','content/.htaccess','content/httpd.conf','content/php.ini','content/connect.php','content/api.php','content/cache.php'),MYSQL_TABLE,$url);
$datemod->cache($datemod->gmtime);

$fn=(isset($fn)? $fn : null);
$content=(isset($content)? $content : null);

$result=mysql_query("SELECT url,fn,content FROM ".MYSQL_TABLE." WHERE url='$url'");
while($row=@mysql_fetch_array($result,MYSQL_ASSOC)){
    $row[]=array('row'=>array_map('htmlspecialchars',$row));
    $urlid=$row['url'];
    $fn=$row['fn'];
    $content=$row['content'];
}
# Return 404 error, if url does not exist
$validate=new validate($url);
if($url==$urlid){}else{
    $validate->status();
    $fn=$validate->fn;
    $content=$validate->content;
}
# Return dir path
if(str_replace('\\','/',pathinfo($_SERVER['SCRIPT_NAME'],PATHINFO_DIRNAME))!='/'){
    $path=str_replace('\\','/',pathinfo($_SERVER['SCRIPT_NAME'],PATHINFO_DIRNAME)).'/';
}else{
    $path=str_replace('\\','/',pathinfo($_SERVER['SCRIPT_NAME'],PATHINFO_DIRNAME));
}

$title=' - Ajax SEO';
?>
<!DOCTYPE html>
<html>
<head>
<meta charset=utf-8>
<title><?php echo$fn.$title?></title>
<link rel=stylesheet href=<?php echo$path?>images/style.css>
<link rel=author href=/humans.txt>
<meta name=description content="Ajax SEO maximized performance - speed, availability, user-friendly">
<meta name=keywords content=ajax,seo,crawl,performance,speed,availability,user-friendly>
<script>/*Add HTML5 tag support for old browsers*/var el=['header','nav','article','footer'];for(var i=el.length-1;i>=0;i--){document.createElement(el[i]);}</script>
</head>
<body>
<div id=container>
<header>
<h2><a id=logo href=<?php echo$path?> title="Ajax SEO maximized performance" rel=home>Ajax SEO</a></h2>
<nav id=nav>
<ul>
<?php
$result=mysql_query('SELECT url,fn FROM '.MYSQL_TABLE.' ORDER BY `order` ASC');
while($row=@mysql_fetch_array($result,MYSQL_ASSOC)){
    $row[]=array('row'=>array_map('htmlspecialchars',$row));
    echo$nav='      <li';if($url==$row['url']){echo ' class=selected';}echo "><a href=\"$path{$row['url']}\" title=\"{$row['fn']}\">{$row['fn']}</a>\r\n";
}
?>
</ul>
</nav>
<article>
<div id=content>
<?php echo"<h1>$fn</h1>\r\n<p>$content</p>\r\n"; mysql_close($con);?>
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
<script src=//ajax.googleapis.com/ajax/libs/jquery/1.6/jquery.min.js></script>
<script>window.jQuery || document.write('<script src=<?php echo$path?>images/jquery-1.6.min.js>\x3C/script>')</script>
<script src=<?php echo$path?>images/jquery.address.js></script>
<script>
$.address.crawlable(1).state('<?php if(strlen(utf8_decode($path))>1){echo substr($path,0,-1);}else{echo$path;}?>').init(function(){
    $('#nav a').address();
}).change(function(e){
    var timer=window.setTimeout(function(){ // Implement for timeout
        $('#content').html('Loading seems to be taking a while.');
    },3800),clearTimeout=window.clearTimeout(timer);
    $.ajax({
        type:"GET",
        url:/*'http://lab.laukstein.com/ajax-seo/'+*/'api'+(e.path.length!=1 ? '/'+encodeURIComponent(e.path.toLowerCase().substr(1)) : ''),
        dataType:'json',        // jsonp
        cache:true,
        //jsonpCallback:'i',    // JSONP cache issue
        beforeSend:function(){
            document.title='Loading...';
            $('#content').fadeTo(200,0.33);
        },
        success:function(data,textStatus,jqXHR){
            clearTimeout;
            $('#nav a').each(function(){
                if($(this).attr('href')==(($.address.state()+decodeURI(e.path)).replace(/\/\//,'/'))){
                    $(this).parent('li').addClass('selected').focus();
                }else{
                    $(this).parent('li').removeAttr('class');
                }
            });
            document.title=data.fn+'<?php echo$title?>';
            $('#content').fadeTo(20,1).html(data.content);
        },
        error:function(jqXHR,textStatus,errorThrown){
            clearTimeout;
            $('li a').each(function(){
                $(this).parent('li').removeAttr('class');
            });
            document.title='404 Page not found';
            $('#content').fadeTo(20,1).removeAttr('style').html('<h1>404 Page not found</h1>\r<p>Sorry, this page cannot be found.</p>\r');
        }
    });
});
// Optimized async Google Analytics snippet
//var _gaq=[['_setAccount','UA-XXXXXXXX-X'],['_trackPageview']];(function(d,t){var g=d.createElement(t),s=d.getElementsByTagName(t)[0];g.async=1;g.src='//www.google-analytics.com/ga.js';s.parentNode.insertBefore(g,s)}(document,'script'))
</script>
</body>
</html>