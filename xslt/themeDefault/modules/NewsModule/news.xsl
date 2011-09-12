
<xsl:template>
	It's news_main.xsl file from xslt/NewsModule folder. I'll process module node from xml with name="NewsModule"
	<xsl:for-each select="data/news/item">
		<div class="newsItem">
			<xsl:value-of select="position()"></xsl:value-of>
			<xsl:text>)</xsl:text>
			<a href="{@url}">
				<xsl:value-of select="@title"></xsl:value-of>
			</a>
		</div>
	</xsl:for-each>
</xsl:template>