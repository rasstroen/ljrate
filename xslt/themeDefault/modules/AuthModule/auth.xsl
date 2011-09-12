
<xsl:template>
	<xsl:if test="data/@error">
		<p>
			<i>ошибка:</i>
			<xsl:value-of select="data/@error" />	
		</p>
	</xsl:if>
	<xsl:choose>
		<xsl:when test="data/profile/@authorized = 1">
			<div>
				<a href="{$prefix}user/{data/profile/@id}">
					<xsl:value-of select="data/profile/@nickname" />
				</a>
				<a href="{$prefix}logout">
					<xsl:text>выход</xsl:text>
				</a>
			</div>
		</xsl:when>
		<xsl:otherwise>
			<div>
				<form method="post">
					<input type="hidden" name="writemodule" value="AuthWriteModule"></input>
					<input type="text" name="email"></input>
					<input type="password" name="password"></input>
					<input type="submit" value="войти"/>
				</form>
				<a href="{$prefix}register">
					<xsl:text>регистрация</xsl:text>
				</a>
			</div>
		</xsl:otherwise>
	</xsl:choose>
</xsl:template>