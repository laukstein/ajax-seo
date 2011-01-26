<?php
include('connect.php');
$result=mysql_query("SELECT title,content FROM $dbtable WHERE url='$url'");
while($row=@mysql_fetch_array($result,MYSQL_ASSOC)){
    $row[]=array('row'=>array_map('htmlspecialchars',$row));
    $title=$row['title'];
    $content=$row['content'];
}
if(str_replace('\\','/',pathinfo($_SERVER['SCRIPT_NAME'],PATHINFO_DIRNAME))!='/'){
    $path=str_replace('\\','/',pathinfo($_SERVER['SCRIPT_NAME'],PATHINFO_DIRNAME)).'/';
}else{
    $path=str_replace('\\','/',pathinfo($_SERVER['SCRIPT_NAME'],PATHINFO_DIRNAME));
}
?>
<html>
<head>
<meta charset=utf-8>
<title><?=$title;?></title>
<link rel=stylesheet href=<?=$path;?>styles.css>
<meta name=description content="Ajax SEO maximized performance - speed, availability, user-friendly">
<meta name=keywords content=ajax,seo,crawl,perform,speed,availability>
<script src=//ajax.googleapis.com/ajax/libs/jquery/1.4.4/jquery.js></script>
<script>!window.jQuery&&document.write(unescape('%3Cscript src=<?=$path;?>jquery-1.4.4.min.js%3E%3C/script%3E'))</script>
<script src=<?=$path;?>jquery.address.js?crawlable=true&state=<?=substr($path,0,-1);?>></script>
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
        //jsonpCallback:'a',
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
<script src=<?=$path;?>jquery.lint.js></script>
</head>
<body>
<header>
<h1>Ajax SEO</h1>
<nav>
<ul>
<?php
$result=mysql_query("SELECT url,title FROM $dbtable ORDER BY orderid ASC");
while($row=@mysql_fetch_array($result,MYSQL_ASSOC)){
    $row[]=array('row'=>array_map('htmlspecialchars',$row));
    echo $nav='      <li';if($_GET['url']==$row['url']){echo ' class=selected';}echo "><a href=\"$path{$row['url']}\" title=\"{$row['title']}\">{$row['title']}</a></li>\n";
}
?>
</ul>
</nav>
<article id=content><?php echo $content; echo $error; mysql_close($con);?></article>
<p><a href=//github.com/laukstein/ajax-seo title="GitHub repository for Ajax SEO">Latest Ajax SEO in GitHub</a> | <a href=//github.com/laukstein/ajax-seo/zipball/master title="Download latest Ajax SEO from GitHub">Download</a> | <a href=//github.com/laukstein/ajax-seo/issues title="Report an issue">Report an issue</a></p>
</header>
<script>var _gaq=[['_setAccount','UA-11883501-1'],['_setDomainName','.laukstein.com'],['_trackPageview']];(function(d,t){var g=d.createElement(t),s=d.getElementsByTagName(t)[0];g.async=1;g.src='//www.google-analytics.com/ga.js';s.parentNode.insertBefore(g,s)}(document,'script'))</script>
</body>
</html>