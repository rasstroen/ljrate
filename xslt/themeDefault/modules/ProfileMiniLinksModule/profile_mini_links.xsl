
<xsl:template>
	<xsl:variable name="profile" select="data/profile" />
	<xsl:if test="$profile/@id != $current_user/@id">
		<script src="{$prefix}static/themeDefault/js/profileModule.js"></script>
		<div>
			<a href="#">Написать</a>
		</div>
		<div>
			<xsl:choose>
				<xsl:when test="$profile/@following = 0">
					<a href="javascript:profileModule_addFriend({$profile/@id},'{$prefix}')">Добавить в друзья</a>
				</xsl:when>	
				<xsl:otherwise>
					<a href="javascript:profileModule_removeFriend({$profile/@id},'{$prefix}')">Удалить из друзей</a>
				</xsl:otherwise>
			</xsl:choose>
			
		</div>
		<div>
			<a href="#">В белый список</a>
		</div>
		<div>
			<a href="#">В черный список</a>
		</div>
	</xsl:if>
</xsl:template>