<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"/>
	<xsl:output omit-xml-declaration="yes"/>
	<xsl:output indent="yes"/>
	<xsl:include href="include/variables.xsl" />
	<xsl:include href="include/htmlhead.xsl" />
	<xsl:include href="include/staticblocks.xsl" />
	<xsl:template match="/">
		<html>
			<head>
				<xsl:call-template name="htmlhead"></xsl:call-template>
			</head>
			<table width="100%" border="1">
				<tr>
					<td colspan="3">
						<xsl:call-template name="main_top_block" />
					</td>
				</tr>
				<tr>
					<td valign="top" style="width:300px">
						<xsl:apply-templates select="//module[@block = 'left']" />
					</td>
					<td valign="top">
						<xsl:apply-templates select="//module[@block = 'content']" />
					</td>
					<td valign="top" style="width:300px">
					<xsl:apply-templates select="//module[@block = 'right']" />	
					</td>
				</tr>
			</table>		
		</html>
	</xsl:template>
</xsl:stylesheet>
