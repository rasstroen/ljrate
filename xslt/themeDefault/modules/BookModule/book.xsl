
<xsl:template>
	<xsl:for-each select="data">
		<xsl:if test="@cover!=''">
			<img style="float:left;margin:10px;" src="{$prefix}{@cover}"/>	
		</xsl:if>
		<h2>
			<xsl:value-of select="@title" />
		</h2>
		<xsl:if test="@subtitle != ''">
			<h3>
				<xsl:value-of select="@subtitle" />
			</h3>
		</xsl:if>
		<xsl:for-each select="persons/item">
			<div>
				<i>
					<xsl:value-of select="@roleName" />
				</i>
				<xsl:text> </xsl:text>
				<xsl:value-of select="@last_name" />
				<xsl:text> </xsl:text>
				<xsl:value-of select="@first_name" />
				<xsl:text> </xsl:text>
				<xsl:value-of select="@middle_name" />
			</div>
		</xsl:for-each>
		<xsl:for-each select="genres/item">
			<div>
				<a href="{$prefix}g/{@name}">
					<xsl:value-of select="@title" />
				</a>
			</div>
		</xsl:for-each>
		<xsl:if test="rightsholder/@title != ''">Издатель:
			<a href="{$prefix}rightsholder/{rightsholder/@id}">
				<xsl:value-of select="rightsholder/@title"></xsl:value-of>
			</a>
		</xsl:if>
		<xsl:if test="@isbn != ''">
			<div>
				ISBN:
				<xsl:value-of select="@isbn"></xsl:value-of>
			</div>			
		</xsl:if>
		<xsl:if test="@annotation != ''">
			<xsl:value-of select="@annotation" disable-output-escaping="yes" />	
		</xsl:if>
		<div class="reviews">
			
			<xsl:if test="reviews/item">
				<h3>Отзывы</h3>
				<xsl:for-each select="reviews/item">
					<div>
						<img src="{$prefix}static/upload/avatars/{@picture}"></img>
						<a href="{$prefix}user/{@id_user}">
							<xsl:value-of select="@nickname" />
						</a>
					</div>	
					<div>
						<xsl:value-of select="@time" />
					</div>	
					<div>
						<xsl:value-of select="@comment" />
					</div>	
					<xsl:if test="@rate != 0">
						<div>
						Оценка
							<xsl:value-of select="@rate" />
						</div>	
					</xsl:if>
					<hr/>
				</xsl:for-each>
			</xsl:if>
		</div>
	</xsl:for-each>
</xsl:template>