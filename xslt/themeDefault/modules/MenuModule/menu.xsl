
<xsl:template>
	<p>It's menu on main page, dude!</p>
	<xsl:for-each select="data/menu/item">
		<div>
			<xsl:value-of select="'ПРИВЕТ'" />
		</div>
	</xsl:for-each>
</xsl:template>