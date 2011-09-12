
<xsl:template>
	<xsl:for-each select="data/profile">
		<div>
		<img src="{$prefix}static/upload/avatars/{@picture}?{$profile/@lastSave}"></img>	
		</div>
		<div>
			ник:<xsl:value-of select="@nickname" />
		</div>
		<div>
			роль:<xsl:value-of select="@rolename" />
		</div>
		<div>
			добавил:
			<ul>
				<li><em>0</em> книг</li>
				<li><em>0</em> рецензий</li>
				<li><a href="#">вся польза</a></li>
			</ul>
		</div>
		<div>
			<ul>
				<li>
					<a href="{$prefix}user/{@id}">профиль</a>
				</li>
				<li>
					<a href="#">стена</a>
				</li>
				<li>
					<a href="#">полки</a>
				</li>
				<li>
					<a href="#">рецензии</a>
				</li>
				<li>
					<a href="{$prefix}user/{@id}/friends">друзья</a>
				</li>
			</ul>
		</div>
	</xsl:for-each>
	
</xsl:template>