<?xml version="1.0" encoding="utf-8"?><xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"><xsl:output method="html" encoding="utf-8" indent="no"/><xsl:template match="p"><p><xsl:apply-templates/></p></xsl:template><xsl:template match="br"><br/></xsl:template><xsl:template match="e|i|s"/><xsl:template match="CODE"><pre data-s9e-livepreview-postprocess="if('undefined'!==typeof hljs){{var a=this.innerHTML;a in hljs._?this.innerHTML=hljs._[a]:(Object.keys&amp;&amp;7&lt;Object.keys(hljs._).length&amp;&amp;(hljs._={{}}),hljs.highlightBlock(this.firstChild),hljs._[a]=this.innerHTML)}};"><code class="{@lang}"><xsl:apply-templates/></code></pre><xsl:if test="not(following::CODE)"><script>if("undefined"===typeof hljs){var a=document.getElementsByTagName("head")[0],b=document.createElement("link");b.type="text/css";b.rel="stylesheet";b.href="highlight.css";a.appendChild(b);b=document.createElement("script");b.type="text/javascript";b.onload=function(){hljs._={};hljs.initHighlighting()};b.async=!0;b.src="highlight.js";a.appendChild(b)};</script></xsl:if></xsl:template></xsl:stylesheet>