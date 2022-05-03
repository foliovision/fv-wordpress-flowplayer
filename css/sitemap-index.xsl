<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="2.0" 
	xmlns:html="http://www.w3.org/TR/REC-html40" 
	xmlns:sitemap="http://www.sitemaps.org/schemas/sitemap/0.9" 
	sitemap:news="http://www.google.com/schemas/sitemap-news/0.9" 
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:output method="html" version="1.0" encoding="UTF-8" indent="yes"/>
<xsl:template match="/">
<html xmlns="https://www.w3.org/1999/xhtml">
<head>
	<title>FV Player Video Sitemap - Index</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<style type="text/css">body{font-family:"Lucida Grande","Lucida Sans Unicode",Tahoma,Verdana,sans-serif;font-size:13px}#header,#footer{padding:2px;margin:10px;font-size:8pt;color:gray}a{color:black}td{font-size:11px}th{text-align:left;padding-right:30px;font-size:11px}tr.high{background-color:whitesmoke}#footer img{vertical-align:bottom}</style>
</head>
<body>
	<h1><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAVCAMAAABrN94UAAAAnFBMVEUAAADCCjPCCjPCCjPCCjPCCjPCCjPCCjPCCjPCCjPCCjPCCjPCCjPCCjPCCjPCCjPCCjPCCjPCCjPCCjPCCjPCCjPCCjPCCjPCCjPCCjPCCjPCCjPCCjPCCjPCCjPCCjPCCjPCCjPCCjPCCjPCCjPCCjPCCjPGGkHKK07OO1zWXHfabIXefJLnna3rrbvvvsnzztb33uT77/H///8eueH7AAAAJnRSTlMADA8SFRgnLTAzS1pdYGZscnuKkJmcqKuxur3Dz9Lb3uHw8/b5/LxE5h0AAAC4SURBVBgZBcHBcsIgFADA5QFNos60p/7/B/bQcdSCIaG7AAAAEgCx6CeABOm6rRvgr7XniYTrdwUA4+dO4KsCAOUThXqBrcwHwPbxFtSEjwIAlWCB0B8ArBQyLtlS5La7pdehEmS8dv01ZCWNg0wQAEORDSSCE+AcqZY5cBIcAIYaY2IQHADGzA4YBB3AHOYOncIOrUFrwE6wPwEA3m8C9wMAOH+RIN+WdQX01h8DCYAlzAYAAADAP7/SRtpaCbGoAAAAAElFTkSuQmCC" alt="FV Player" title="FV Player" /> FV Player Video Sitemap - Index</h1>
	<div id="header">

	</div>
	<div id="content">
		<table cellpadding="5">
			<tr class="high">
				<th>#</th>
				<th>XML Sitemap</th>
				<th>Last Changed</th>
			</tr>
<xsl:variable name="lower" select="'abcdefghijklmnopqrstuvwxyz'"/>
<xsl:variable name="upper" select="'ABCDEFGHIJKLMNOPQRSTUVWXYZ'"/>
<xsl:for-each select="sitemap:sitemapindex/sitemap:sitemap">
			<tr><xsl:if test="position() mod 2 != 1"><xsl:attribute  name="class">high</xsl:attribute></xsl:if>
				<td><xsl:value-of select="position()"/></td>
				<td><xsl:variable name="itemURL"><xsl:value-of select="sitemap:loc"/></xsl:variable>
					<a href="{$itemURL}"><xsl:value-of select="sitemap:loc"/></a>
				</td>
				<td><xsl:value-of select="concat(substring(sitemap:lastmod,0,11),concat(' ', substring(sitemap:lastmod,12,5)))"/></td>
			</tr>
</xsl:for-each>
		</table>
	</div>
	<div id="footer">
		<p>Generated by <a href="https://foliovision.com/player" title="FV Player">FV Player</a></p>
	</div>
</body>
</html>
</xsl:template>
</xsl:stylesheet>
