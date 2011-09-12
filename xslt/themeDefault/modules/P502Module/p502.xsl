
<xsl:template>
	<xsl:if test="data/@error and data/@error!= ''">
		<h2>
			<xsl:value-of  disable-output-escaping="yes" select="data/@error_code" />
		</h2>
		<xsl:value-of  disable-output-escaping="yes" select="data/@error" />	
	</xsl:if>
</xsl:template>