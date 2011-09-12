
<xsl:template>
	<xsl:variable name="profile" select="data/profile" />
	<div>
		<h3>
			<xsl:value-of select="$profile/@nickname"></xsl:value-of>
		</h3>
	</div>
	<xsl:value-of select="$profile/@rolename"></xsl:value-of>

	<div>
		<xsl:text>Живет в городе </xsl:text>
		<xsl:value-of select="$profile/@city" disable-output-escaping="yes"></xsl:value-of>,
		<xsl:text>День рождения </xsl:text>
		<xsl:value-of select="$profile/@bdays" disable-output-escaping="yes"></xsl:value-of>
	</div>
	<xsl:if test="$profile/@id = $current_user/@id">
		<div>
			<a href="{$prefix}user/{$profile/@id}/edit">редактировать профиль</a>
		</div>
	</xsl:if>
</xsl:template>