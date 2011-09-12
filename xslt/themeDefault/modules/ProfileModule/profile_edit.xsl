
<xsl:template>
	<xsl:variable name="profile" select="data/profile" />
	<script src="{$prefix}static/themeDefault/js/profileModule.js"></script>
	<form method="post" enctype="multipart/form-data" action="{$prefix}user/{$profile/@id}">
		<input type="hidden" name="writemodule" value="ProfileWriteModule" />
		<input type="hidden" name="id" value="{$profile/@id}" />
		<div>
			<h3>
				<xsl:value-of select="$profile/@nickname"></xsl:value-of>
			</h3>
		</div>
		<div>Аватар</div>
		
		<div>
			<input type="file" name="picture"></input>
		</div>
		<div>
			<xsl:call-template name="profile_edit_cityLoader">
				<xsl:with-param name="current_city" select="$profile/@city_id" />
			</xsl:call-template>
		</div>
		<div>Дата рождения ДД-ММ-ГГГГ</div>
		<div><input name="bday" value="{$profile/@bday}" /></div>
		<div>
			facebook:
			<input name="link_fb" value="{$profile/@link_fb}"></input>
		</div>
		<div>
			livejournal:
			<input name="link_lj" value="{$profile/@link_lj}"></input>
		</div>
		<div>
			vkontakte:
			<input name="link_vk" value="{$profile/@link_vk}"></input>
		</div>
		<div>
			twitter:
			<input name="link_tw" value="{$profile/@link_tw}"></input>
		</div>
		<div>
			<input type="submit" value="save profile"/>	
		</div>
	</form>
</xsl:template>
<xsl:template name="profile_edit_cityLoader">
	<xsl:param name="current_city"></xsl:param>
	<div>Страна:</div>
	<div id="counry_div">загружаем...</div>
	<div>Город:</div>
	<div id="city_div">загружаем...</div>
	<script>
		<xsl:text>profileModule_cityInit('counry_div','city_div','</xsl:text>
		<xsl:value-of select="$current_city"></xsl:value-of>
		<xsl:text>','</xsl:text>
		<xsl:value-of select="$prefix"></xsl:value-of>
		<xsl:text>');</xsl:text>
	</script>
</xsl:template>