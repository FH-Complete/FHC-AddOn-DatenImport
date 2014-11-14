<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="text" encoding="UTF-8"/>
	<xsl:strip-space elements="*" />
	
	<xsl:template match="/Erhalter">
		<xsl:text>"ErhKz","MeldeDatum","StgKz","OrgFormCode","PersKz","OrgFormTeilCode","GeburtsDatum","Geschlecht","SVNR","StaatsangehoerigkeitCode","HeimatPLZ","HeimatGemeinde","HeimatStrasse","HeimatNation","ZugangCode","ZugangDatum","BeginnDatum","BeendigungsDatum","Ausbildungssemester","StudStatusCode","StandortCode","BMWFfoerderrelevant"&#xa;</xsl:text>
		<xsl:apply-templates select="StudierendenBewerberMeldung/StudiengangStamm" />
	</xsl:template>
	
	<xsl:template match="StudierendenBewerberMeldung/StudiengangStamm/StudiengangDetail">
		<xsl:apply-templates select="Student" />
	</xsl:template>

	<xsl:template match="StudiengangDetail/Student">
		<xsl:text>"</xsl:text>
		<xsl:value-of select="/Erhalter/ErhKz"/><xsl:text>","</xsl:text>
		<xsl:value-of select="/Erhalter/MeldeDatum"/><xsl:text>","</xsl:text>
		<xsl:value-of select="/Erhalter/StudierendenBewerberMeldung/StudiengangStamm/StgKz"/><xsl:text>","</xsl:text>
		<xsl:value-of select="/Erhalter/StudierendenBewerberMeldung/StudiengangStamm/OrgFormCode"/><xsl:text>","</xsl:text>
		<xsl:value-of select="PersKz"/><xsl:text>","</xsl:text>
		<xsl:value-of select="/Erhalter/StudierendenBewerberMeldung/StudiengangStamm/StudiengangDetail/OrgFormTeilCode"/><xsl:text>","</xsl:text>
		<xsl:value-of select="GeburtsDatum"/><xsl:text>","</xsl:text>
		<xsl:value-of select="Geschlecht"/><xsl:text>","</xsl:text>
		<xsl:value-of select="SVNR"/><xsl:text>","</xsl:text>
		<xsl:value-of select="StaatsangehoerigkeitCode"/><xsl:text>","</xsl:text>
		<xsl:value-of select="HeimatPLZ"/><xsl:text>","</xsl:text>
		<xsl:value-of select="HeimatGemeinde"/><xsl:text>","</xsl:text>
		<xsl:value-of select="HeimatStrasse"/><xsl:text>","</xsl:text>
		<xsl:value-of select="HeimatNation"/><xsl:text>","</xsl:text>
		<xsl:value-of select="ZugangCode"/><xsl:text>","</xsl:text>
		<xsl:value-of select="ZugangDatum"/><xsl:text>","</xsl:text>
		<xsl:value-of select="BeginnDatum"/><xsl:text>","</xsl:text>
		<xsl:value-of select="BeendigungsDatum"/><xsl:text>","</xsl:text>
		<xsl:value-of select="Ausbildungssemester"/><xsl:text>","</xsl:text>
		<xsl:value-of select="StudStatusCode"/><xsl:text>","</xsl:text>
		<xsl:value-of select="StandortCode"/><xsl:text>","</xsl:text>
		<xsl:value-of select="BMWFfoerderrelevant"/>
		<xsl:text>"&#xa;</xsl:text>
	</xsl:template>
</xsl:stylesheet>
