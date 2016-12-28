<?php
//
// Sitemap stylesheet http://www.w3.org/TR/xslt-30/
//

include 'content/config.php';
include 'content/cache.php'; cache::me();

echo '<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE html>
<html xsl:version="3.0"
      xmlns:sitemap="http://www.sitemaps.org/schemas/sitemap/0.9"
      xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"
      xmlns:video="http://www.google.com/schemas/sitemap-video/1.1"
      xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
      xmlns="http://www.w3.org/1999/xhtml">
<title>Sitemap</title>
<style>
html {
    color: #222;
    line-height: 3;
    font-family: tahoma, sans-serif;
    background-color: #f8f9fa;
    -webkit-user-select: none;
       -moz-user-select: none;
        -ms-user-select: none;
            user-select: none;
}
dl {
    margin: 5em 0 3em;
    white-space: nowrap;
    counter-reset: section;
}
dt {
    position: fixed;
    z-index: 1;
    top: 0;
    left: 0;
    right: 0;
    padding-top: 2em;
    background-color: #fff;
    border-bottom: 1px solid #d9e0e2;
}
dt, dd {
    padding-left: 16%;
    padding-right: 12%;
}
dd {
    position: relative;
    margin: 0;
    color: #777;
    border-top: 1px solid #eee;
}
dd:first-of-type {
    border-top: 0;
}
dd:before {
    counter-increment: section;
    content: counter(section) ".";
    position: absolute;
    margin-left: -1em;
    text-align: right;
    text-indent: -2em;
}
dd:hover {
    background-color: #fff;
}
div {
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
}
#lastmod,
time {
    float: right;
}
div div {
    display: inline-block;
    width: 3em;
    height: 3em;
    margin-right: 1em;
    vertical-align: top;
    background-position: top center;
    background-repeat: no-repeat;
    background-size: cover;
}
a {
    position: relative;
    color: #0f4cd5;
    text-decoration: none;
    outline: 0;
    pointer-events: auto;
    -webkit-user-drag: none;
            user-drag: none;
}
a:visited {
    color: #720fc5;
}
a:hover, a:focus {
    text-decoration: underline;
}
dd > a {
    position: absolute;
    top: 0;
    right: 0;
    left: 0;
    bottom: 0;
}
</style>
<dl>
    <dt>
        <div id="lastmod">Last modified (GMT)</div>
        <div>Sitemap URL</div>
    </dt>
    <xsl:for-each select="sitemap:urlset/sitemap:url">
    <dd>
        <xsl:variable name="lastmod" select="sitemap:lastmod"/>
        <xsl:variable name="url" select="sitemap:loc"/>
        <xsl:variable name="image" select="image:image/image:loc"/>
        <xsl:if test="string($lastmod)">
            <time datetime="{$lastmod}"><xsl:value-of select="concat(substring($lastmod, 0, 5), concat(\'/\', substring($lastmod, 6, 2)), concat(\'/\', substring($lastmod, 9, 2)), concat(\' \', substring($lastmod, 12, 5)))"/></time>
        </xsl:if>
        <xsl:if test="string($url)">
            <a href="{$url}" tabindex="-1"></a>
        </xsl:if>
        <div>
            <xsl:if test="string($image)">
                <div style="background-image:url({$image})"></div>
            </xsl:if>
            <xsl:if test="string($url)">
                <a href="{$url}"><xsl:value-of select="sitemap:loc"/></a>
            </xsl:if>
        </div>
    </dd>
    </xsl:for-each>
</dl>
</html>';
