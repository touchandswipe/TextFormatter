<?xml version="1.0" encoding="utf-8"?><xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"><xsl:output method="html" encoding="utf-8" indent="no"/><xsl:template match="p"><p><xsl:apply-templates/></p></xsl:template><xsl:template match="br"><br/></xsl:template><xsl:template match="e|i|s"/><xsl:template match="LEFT"><div style="text-align:left"><xsl:apply-templates/></div></xsl:template></xsl:stylesheet>