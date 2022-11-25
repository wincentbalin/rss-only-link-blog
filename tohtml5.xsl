<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<!-- The last attribute below is the HTML5 compatibility attribute -->
<xsl:output method="html" encoding="utf-8" indent="yes" doctype-system="about:legacy-compat"/>

<xsl:template match="/rss/channel">
<html>
<head>
    <title><xsl:value-of select="title/text()"/></title>
</head>
<body>
    <h1><xsl:value-of select="title/text()"/></h1>
    <xsl:choose>
        <xsl:when test="item"><xsl:apply-templates select="item"/></xsl:when>
        <xsl:otherwise>
            Before adding links, please perform following steps.
            <ol>
                <li>First step!!1!</li>
            </ol>
            <a href="javascript:alert('Start configuration...')">Create bookmarklet</a>
        </xsl:otherwise>
    </xsl:choose>
    
</body>
</html>
</xsl:template>

<xsl:template match="item">
    <p>
        <xsl:value-of select="description/text()"/>
        <xsl:text> </xsl:text>
        <a>
            <xsl:attribute name="href"><xsl:value-of select="link/text()"/></xsl:attribute>
            <xsl:value-of select="link/text()"/>
        </a>
    </p>
</xsl:template>

</xsl:stylesheet>
