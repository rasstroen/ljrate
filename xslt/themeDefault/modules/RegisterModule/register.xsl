
<xsl:template>
	<h2>Регистрация</h2>
	<xsl:choose>
		<xsl:when test="$profile/@authorized = 1">
			<!-- юзер авторизован. какая регистрация?-->
		</xsl:when>
		<xsl:otherwise>
			<xsl:if test="data/@result">
				<!-- была попытка зарегистрироваться-->	
				<xsl:choose>
					<xsl:when test="data/@success">
						<!-- успешная регистрация-->
						<p>Вы успешно зарегистрированы. Проверьте почтовый ящик чтобы зайти на сайт</p>
					</xsl:when>
					<xsl:otherwise>
						<p>Возникли проблемы при попытке зарегистрироваться:</p>
						<xsl:call-template name="RegisterDrawForm"></xsl:call-template>
					</xsl:otherwise>
				</xsl:choose>
			</xsl:if>
			<xsl:if test="not(data/@result)">
				<!-- просто рисуем форму регистрации-->
				<xsl:call-template name="RegisterDrawForm"></xsl:call-template>
			</xsl:if>
		</xsl:otherwise>
	</xsl:choose>
</xsl:template>
<xsl:template name="RegisterDrawForm">
	<form method="post">
		<input type="hidden" value="RegisterWriteModule" name="writemodule" />
		<div>email</div>
		<div>
			<xsl:value-of select="data/@email_error" />
		</div>
		<div>
			<input name="email" value="{data/@email}" />
		</div>
		<div>пароль</div>
		<div>
			<xsl:value-of select="data/@password_error" />
		</div>
		<div>
			<input name="password" type="password" value="" />
		</div>
		<div>никнейм</div>
		<div>
			<xsl:value-of select="data/@nickname_error" />
		</div>
		<div>
			<input name="nickname" value="{data/@nickname}" />
		</div>
		<div>
			<input type="submit" value="зарегистрироваться" />	
		</div>
	</form>
</xsl:template>