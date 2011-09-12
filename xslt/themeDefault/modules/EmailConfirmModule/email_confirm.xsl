
<xsl:template>
	<xsl:choose>
		<xsl:when test="data/@error">
			<xsl:value-of select="data/@error" />
		</xsl:when>
		<xsl:when test="data/@success">
			<p>Вы успешно подтвердили свой почтовый ящик!</p>
		</xsl:when>
	</xsl:choose>
</xsl:template>