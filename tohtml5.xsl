<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<!-- The last attribute below is the HTML5 compatibility attribute -->
<xsl:output method="html" encoding="utf-8" indent="yes" doctype-system="about:legacy-compat"/>

<xsl:template match="/rss/channel">
<html>
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
    <title><xsl:value-of select="title/text()"/></title>
    <link rel="alternate" type="application/rss+xml" title="RSS" href="index.xml"/>
    <style>
.date { color: lightgray }
hr { background-color: lightgray; height: 1px; border: 0 }
    </style>
</head>
<body>
    <h1><xsl:value-of select="title/text()"/></h1>
    <xsl:apply-templates select="item"/>
    <hr/>
    <h2>Setup</h2>
    It seems that you have installed this fine blog. Very good!
    <h3>On web server</h3>
    Before adding links to this blog, please perform the following steps:
    <ol>
        <li>Edit the password in the line <code><b>TODO</b> 2</code> in the file <code>index.php</code>. By default it is empty, and the blog software will complain about it!</li>
        <li>Edit the address of this blog in the line <code><b>TODO</b> 5</code> in the file <code>index.xml</code> (in the <code>&lt;link&gt;</code> tag). This is the same address as you are currently seeing in the addressbar of your web browser.</li>
        <li>You can add your name to the feed inside the <code>managingEditor</code> tag in the file <code>index.xml</code>.</li>
        <li>Actually, you only need these 3 files to run the blog: <code>index.php</code>, <code>index.xml</code>, and <code>tohtml5.xsl</code>. You can remove the rest.</li>
    </ol>
    <h3>In web browser</h3>
    <p>Click the following link and enter the password from the step 1 in the previous section and press the <code>OK</code> button. The bookmarklet code will be opened in a new tab or page; bookmark the current page and change the bookmark address to the bookmarklet code created.</p>
    <p>If you entered a wrong password, the blog software will complain about it. Repeat the steps in this section and enter the correct password, replacing the bookmark address afterwards.</p>
    <a>
        <xsl:attribute name="href">javascript:(function() {
            var url = window.location.href;
            if (/^http:/.test(url)) {
                alert('The page uses insecure connection!\nPlease use an address starting with https://');
                return;
            }
            var password = prompt('Please enter the password you entered into script:');
            if (password === null) {
                return;
            }
            var bookmarkletContents = [
                'javascript:(function() {',
                'var text = prompt(\'Enter the description:\');',
                'if (text === null) {',
                '    return;',
                '}',
                'var url = \'' + url.replace('\'', '\\\'') + '\';',
                'var password = \'' + password.replace('\'', '\\\'') + '\';',
                'function connectionError(link, text) {',
                '    var retry = confirm(\'Connection error! Retry?\');',
                '    if (retry) {',
                '        addArticle(link, text);',
                '    }',
                '}',
                'function addArticle(link, text) {',
                '    /* Collect parameters */',
                '    var scriptElementId = \'script-\' + Date.now();',
                '    var timeoutId = setTimeout(connectionError, 5000, link, text);',
                '    /* Add script element */',
                '    var script = document.createElement(\'script\');',
                '    script.id = scriptElementId;',
                '    script.src = url + \'?p=\' + encodeURIComponent(password) + \'&amp;l=\' + encodeURIComponent(link) + \'&amp;t=\' + encodeURIComponent(text) + \'&amp;s=\' + encodeURIComponent(scriptElementId) + \'&amp;o=\' + encodeURIComponent(timeoutId);',
                '    document.body.appendChild(script);',
                '}',
                'addArticle(url, text);',
                '})();'
            ];
            var container = document.createElement('p');
            container.textContent = 'Bookmarklet: ';
            document.body.appendChild(container);
            var bookmarklet = document.createElement('a');
            bookmarklet.href = bookmarkletContents.join('\n');
            bookmarklet.textContent = 'Describe page in blog';
            container.appendChild(bookmarklet);
        })();
        </xsl:attribute>
        Create bookmarklet
    </a>
</body>
</html>
</xsl:template>

<xsl:template match="item">
    <p>
        <xsl:apply-templates select="pubDate"/>
        <xsl:text> </xsl:text>
        <xsl:value-of select="title/text()"/>
        <xsl:text> </xsl:text>
        <a>
            <xsl:attribute name="href"><xsl:value-of select="link/text()"/></xsl:attribute>
            <xsl:attribute name="target">_blank</xsl:attribute>
            <xsl:value-of select="link/text()"/>
        </a>
    </p>
</xsl:template>

<xsl:template match="pubDate">
    <xsl:variable name="monthName"><xsl:value-of select="substring(text(), 9, 3)"/></xsl:variable>
    <span>
        <xsl:attribute name="class">date</xsl:attribute>
        <xsl:value-of select="substring(text(), 13, 4)"/>
        <xsl:text>-</xsl:text>
        <xsl:choose>
            <xsl:when test="$monthName = 'Jan'">01</xsl:when>
            <xsl:when test="$monthName = 'Feb'">02</xsl:when>
            <xsl:when test="$monthName = 'Mar'">03</xsl:when>
            <xsl:when test="$monthName = 'Apr'">04</xsl:when>
            <xsl:when test="$monthName = 'May'">05</xsl:when>
            <xsl:when test="$monthName = 'Jun'">06</xsl:when>
            <xsl:when test="$monthName = 'Jul'">07</xsl:when>
            <xsl:when test="$monthName = 'Aug'">08</xsl:when>
            <xsl:when test="$monthName = 'Sep'">09</xsl:when>
            <xsl:when test="$monthName = 'Oct'">10</xsl:when>
            <xsl:when test="$monthName = 'Nov'">11</xsl:when>
            <xsl:when test="$monthName = 'Dec'">12</xsl:when>
        </xsl:choose>
        <xsl:text>-</xsl:text>
        <xsl:value-of select="substring(text(), 6, 2)"/>
    </span>
</xsl:template>

</xsl:stylesheet>
