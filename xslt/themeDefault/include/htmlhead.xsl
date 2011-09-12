<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns="http://www.w3.org/1999/xhtml" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:template name="htmlhead">
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />	
		<title>
			<xsl:value-of select="$page/@title"></xsl:value-of>
		</title>
		<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.6.2/jquery.min.js"></script>
		<link rel="stylesheet" href="{$prefix}static/themeDefault/css/themeDefault.css" ></link>
	</xsl:template>
</xsl:stylesheet>
