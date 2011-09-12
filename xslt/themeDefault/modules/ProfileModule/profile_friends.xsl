
<xsl:template>
	<xsl:for-each select="data/profile">
		<h2>Друзья пользователя 
			<xsl:value-of select="@nickname"></xsl:value-of>
		</h2>
		<div>Читает:</div>
		<xsl:for-each select="following/item">
			<xsl:call-template name="profileFriendsItem">
				<xsl:with-param name="profile" select="."></xsl:with-param>
			</xsl:call-template>
		</xsl:for-each>
		<br clear ="all" />
		<div>Читают:</div>
		<xsl:for-each select="followers/item">
			<xsl:call-template name="profileFriendsItem">
				<xsl:with-param name="profile" select="."></xsl:with-param>
			</xsl:call-template>
		</xsl:for-each>
	</xsl:for-each>
</xsl:template>
<xsl:template name="profileFriendsItem">
	<xsl:param name="profile"></xsl:param>
	<div class="profileFriends">
		<a href="{$prefix}user/{$profile/@id}">
			<xsl:value-of select="$profile/@nickname"></xsl:value-of>	
		</a>
		<div>
			<img src="{$prefix}static/upload/avatars/{@picture}?{$profile/@lastSave}"></img>	
		</div>
	</div>
</xsl:template>