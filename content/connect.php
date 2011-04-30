<?php
// MySQL settings
define('MYSQL_HOST','localhost');
define('MYSQL_USER','root');
define('MYSQL_PASS','');
define('MYSQL_DB','test');
define('MYSQL_TABLE','ajax_seo');
define('MYSQL_ERROR',null);

$con=@mysql_connect(MYSQL_HOST,MYSQL_USER,MYSQL_PASS);
$f='content/connect.php';

if($con){
    if(@mysql_select_db(MYSQL_DB,$con)){
        array_map('trim',$_GET);
        array_map('stripslashes',$_GET);
        array_map('mysql_real_escape_string',$_GET);
        mysql_query("SET NAMES 'utf8'");
        
        # Return 404 error, if url does not exist
        class validate{
            public $fn;
            public $content;
            function status(){
                header('Status:404 Not Found',true,404);
                $this->fn='404 Page not found';
                $this->content='Sorry, this page cannot be found.';
            }
        }
        
        $url=(isset($_GET['url'])? $_GET['url'] : null);
        $urlid=(isset($urlid)? $urlid : null);
        
        // Unset MySQL Installation error
        if(MYSQL_ERROR){
            $change=file_get_contents($f);
            $change=preg_replace("/define\('(MYSQL_ERROR)','(.*)'\);/","define('$1',null);",$change);
            $fopen=fopen($f,'w');
            fwrite($fopen,$change);
            fclose($fopen);
        }
        
        $sql="SELECT * FROM ".MYSQL_TABLE;
        $result=@mysql_query($sql);
        if(!$result){
            // Create table
            mysql_query("CREATE TABLE IF NOT EXISTS `".MYSQL_TABLE."` (
              `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
              `order` mediumint(8) unsigned NOT NULL,
              `url` varchar(70) collate utf8_unicode_ci NOT NULL,
              `fn` varchar(70) collate utf8_unicode_ci NOT NULL,
              `content` text collate utf8_unicode_ci,
              `pubdate` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=7;");
            // Insert data
            mysql_query("INSERT INTO `".MYSQL_TABLE."` (`order`, `url`, `fn`, `content`) VALUES
            (1, '', 'Home', 'Home content'),
            (2, 'about', 'About', 'About content'),
            (3, 'portfolio', 'Portfolio', 'Portfolio  content'),
            (4, 'contact', 'Contact', 'Contact  content'),
            (5, 'контакты', 'Контакты', 'Содержание контактом'),
            (6, 'צור-קשר', 'צור קשר', 'תוכן לצור קשר');");
            
            if(is_writable($f)){chmod($f,0600);}
            header('Content-Type:text/html');
            header('X-Robots-Tag: index, noarchive, nosnippet',true);
            exit('Installation has completed successfully! Try to refresh your browser.');
        }
    }
}else{
    // Not reachable database
    if($_SERVER['REQUEST_METHOD']=='POST'){
        // Replace MySQL settings
        $change=file_get_contents($f);
        $change=preg_replace("/define\('(MYSQL_HOST)','(.*)'\);/","define('$1','".trim($_POST['host'])."');",$change);
        $change=preg_replace("/define\('(MYSQL_USER)','(.*)'\);/","define('$1','".trim($_POST['user'])."');",$change);
        $change=preg_replace("/define\('(MYSQL_PASS)','(.*)'\);/","define('$1','".trim($_POST['pass'])."');",$change);
        $change=preg_replace("/define\('(MYSQL_DB)','(.*)'\);/","define('$1','".trim($_POST['db'])."');",$change);
        $change=preg_replace("/define\('(MYSQL_TABLE)','(.*)'\);/","define('$1','".trim($_POST['table'])."');",$change);
        $change=preg_replace("/define\('(MYSQL_ERROR)',null\);/","define('$1','Could not connect to server.');",$change);
        $fopen=fopen($f,'w');
        fwrite($fopen,$change);
        fclose($fopen);
        header('Location:'.$_SERVER['PHP_SELF']);
    }else{
        if(!@is_writable($f)){chmod($f,0755);}
        header('Content-Type:text/html');
        header('X-Robots-Tag: index, noarchive, nosnippet',true);
        # Return dir path
        if(str_replace('\\','/',pathinfo($_SERVER['SCRIPT_NAME'],PATHINFO_DIRNAME))!='/'){
            $path=str_replace('\\','/',pathinfo($_SERVER['SCRIPT_NAME'],PATHINFO_DIRNAME)).'/';
        }else{
            $path=str_replace('\\','/',pathinfo($_SERVER['SCRIPT_NAME'],PATHINFO_DIRNAME));
        }
        if(MYSQL_ERROR){$error='<br /><i>'.MYSQL_ERROR.'</i>';}else{$error=null;}
        exit('<!DOCTYPE html>
<html>
<head>
<meta charset=utf-8>
<title>Ajax SEO</title>
<link rel=stylesheet href='.$path.'images/style.css>
<link rel=author href='.$path.'humans.txt>
<meta name=description content="Ajax SEO maximized performance - speed, availability, user-friendly">
<meta name=keywords content=ajax,seo,crawl,performance,speed,availability,user-friendly>
<script>/*Add HTML5 tag support for old browsers*/var el=[\'header\',\'nav\',\'article\',\'footer\'];for(var i=el.length-1;i>=0;i--){document.createElement(el[i]);}</script>
</head>
<body>
<div id=container>
<header>
<h2><a id=logo href='.$path.' title="Ajax SEO maximized performance" rel=home>Ajax SEO Installation</a></h2>
<nav id=nav></nav>
<article>
<div id=content>
    <form method=post>
        <h1>MySQL Configuration</h1>
        <label for=host>Hostname</label><input name=host id=host value="'.MYSQL_HOST.'" /><br />
        <label for=user>Username</label><input type=text name=user id=user value="'.MYSQL_USER.'" /><br />
        <label for=pass>Password</label><input type=password name=pass id=pass /><br />
        <label for=db>Database</label><input type=text name=db id=db value="'.MYSQL_DB.'" /><br />
        <label for=table>Table</label><input type=text name=table id=table value="'.MYSQL_TABLE.'" /><br />
        <input type=submit value=Install name=install />'.$error.'
    </form>
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
</body>
</html>');
    }
}
?>