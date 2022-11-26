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
    <xsl:apply-templates select="item"/>
    <hr/>
    <h1>Setup</h1>
    It seems that you have installed this fine blog. Very good!
    <h2>On web server</h2>
    Before adding links to this blog, please perform the following steps:
    <ol>
        <li>Edit the password in the line <code><b>TODO</b> 2</code> in the file <code>index.php</code>. By default it is empty, and the blog software will complain about it!</li>
        <li>Edit the address of this blog in the line <code><b>TODO</b> 5</code> in the file <code>index.xml</code> (in the <code>&lt;link&gt;</code> tag). This is the same address as you are currently seeing in the addressbar of your web browser.</li>
        <li>You can add your name to the feed inside the <code>managingEditor</code> tag in the file <code>index.xml</code>.</li>
        <li>Actually, you only need these 3 files to run the blog: <code>index.php</code>, <code>index.xml</code>, and <code>tohtml5.xsl</code>. You can remove the rest.</li>
    </ol>
    <h2>In web browser</h2>
    <p>Click the following link and enter the password from the step 1 in the previous section and press the <code>OK</code> button. The bookmarklet code will be opened in a new tab or page; bookmark the current page and change the bookmark address to the bookmarklet code created.</p>
    <p>If you entered a wrong password, the blog software will complain about it. Repeat the steps in this section and enter the correct password, replacing the bookmark address afterwards.</p>
    <a href="javascript:alert('Start configuration...')">Create bookmarklet</a>
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
