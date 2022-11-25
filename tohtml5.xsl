<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<!-- The last attribute below is the HTML5 compatibility attribute -->
<xsl:output method="html" encoding="utf-8" indent="yes" doctype-system="about:legacy-compat"/>

<xsl:template match="/rss/channel">
<html>
<head>
    <title><xsl:value-of select="title/text()"/></title>
    <xsl:apply-templates/>
</head>
<body>
    <xsl:apply-templates/>
</body>
</html>
</xsl:template>

</xsl:stylesheet>
