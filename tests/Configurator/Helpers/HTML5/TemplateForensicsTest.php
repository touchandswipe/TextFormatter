<?php

namespace s9e\TextFormatter\Tests\Configurator\Helpers\HTML5;

use s9e\TextFormatter\Tests\Test;
use s9e\TextFormatter\Configurator\Helpers\HTML5\TemplateForensics;

/**
* @covers s9e\TextFormatter\Configurator\Helpers\HTML5\TemplateForensics
*/
class TemplateForensicsTest extends Test
{
	public function runCase($title, $xslSrc, $rule, $xslTrg = null)
	{
		$st = '<xsl:template xmlns:xsl="http://www.w3.org/1999/XSL/Transform">';
		$et = '</xsl:template>';

		$src = new TemplateForensics($st . $xslSrc . $et);
		$trg = new TemplateForensics($st . $xslTrg . $et);

		$methods = array(
			'allowChild'           => array('assertTrue',  'allowsChild'),
			'allowDescendant'      => array('assertTrue',  'allowsDescendant'),
			'allowText'            => array('assertTrue',  'allowsText'),
			'autoReopen'           => array('assertTrue',  'autoReopen'),
			'!autoReopen'          => array('assertFalse', 'autoReopen'),
			'denyText'             => array('assertFalse', 'allowsText'),
			'denyChild'            => array('assertFalse', 'allowsChild'),
			'denyDescendant'       => array('assertFalse', 'allowsDescendant'),
			'closeParent'          => array('assertTrue',  'closesParent'),
			'!closeParent'         => array('assertFalse', 'closesParent'),
			'denyAll'              => array('assertTrue',  'denyAll'),
			'!denyAll'             => array('assertFalse', 'denyAll'),
			'isBlock'              => array('assertTrue',  'isBlock'),
			'!isBlock'             => array('assertFalse', 'isBlock'),
			'isTransparent'        => array('assertTrue',  'isTransparent'),
			'!isTransparent'       => array('assertFalse', 'isTransparent'),
			'isVoid'               => array('assertTrue',  'isVoid'),
			'!isVoid'              => array('assertFalse', 'isVoid'),
			'preservesWhitespace'  => array('assertTrue',  'preservesWhitespace'),
			'!preservesWhitespace' => array('assertFalse', 'preservesWhitespace')
		);

		list($assert, $method) = $methods[$rule];

		$this->$assert($src->$method($trg), $title);
	}

	// Start of content generated by ../../../../scripts/patchTemplateForensicsTest.php
	/**
	* @testdox <span> does not allow <div> as child
	*/
	public function testD335F821()
	{
		$this->runCase(
			'<span> does not allow <div> as child',
			'<span><xsl:apply-templates/></span>',
			'denyChild',
			'<div><xsl:apply-templates/></div>'
		);
	}

	/**
	* @testdox <span> does not allow <div> as child even with a <span> sibling
	*/
	public function test114C6685()
	{
		$this->runCase(
			'<span> does not allow <div> as child even with a <span> sibling',
			'<span><xsl:apply-templates/></span>',
			'denyChild',
			'<span>xxx</span><div><xsl:apply-templates/></div>'
		);
	}

	/**
	* @testdox <span> and <div> does not allow <span> and <div> as child
	*/
	public function testE416F9F5()
	{
		$this->runCase(
			'<span> and <div> does not allow <span> and <div> as child',
			'<span><xsl:apply-templates/></span><div><xsl:apply-templates/></div>',
			'denyChild',
			'<span/><div/>'
		);
	}

	/**
	* @testdox <li> closes parent <li>
	*/
	public function test93A27904()
	{
		$this->runCase(
			'<li> closes parent <li>',
			'<li/>',
			'closeParent',
			'<li><xsl:apply-templates/></li>'
		);
	}

	/**
	* @testdox <div> closes parent <p>
	*/
	public function test1D189E22()
	{
		$this->runCase(
			'<div> closes parent <p>',
			'<div/>',
			'closeParent',
			'<p><xsl:apply-templates/></p>'
		);
	}

	/**
	* @testdox <p> closes parent <p>
	*/
	public function test94ADCE2C()
	{
		$this->runCase(
			'<p> closes parent <p>',
			'<p/>',
			'closeParent',
			'<p><xsl:apply-templates/></p>'
		);
	}

	/**
	* @testdox <div> does not close parent <div>
	*/
	public function test80EA2E75()
	{
		$this->runCase(
			'<div> does not close parent <div>',
			'<div/>',
			'!closeParent',
			'<div><xsl:apply-templates/></div>'
		);
	}

	/**
	* @testdox <span> does not close parent <span>
	*/
	public function test576AB9F1()
	{
		$this->runCase(
			'<span> does not close parent <span>',
			'<span/>',
			'!closeParent',
			'<span><xsl:apply-templates/></span>'
		);
	}

	/**
	* @testdox <a> denies <a> as descendant
	*/
	public function test176B9DB6()
	{
		$this->runCase(
			'<a> denies <a> as descendant',
			'<a><xsl:apply-templates/></a>',
			'denyDescendant',
			'<a/>'
		);
	}

	/**
	* @testdox <a> allows <img> with no usemap attribute as child
	*/
	public function testFF711579()
	{
		$this->runCase(
			'<a> allows <img> with no usemap attribute as child',
			'<a><xsl:apply-templates/></a>',
			'allowChild',
			'<img/>'
		);
	}

	/**
	* @testdox <a> denies <img usemap="#foo"> as child
	*/
	public function testF13726A8()
	{
		$this->runCase(
			'<a> denies <img usemap="#foo"> as child',
			'<a><xsl:apply-templates/></a>',
			'denyChild',
			'<img usemap="#foo"/>'
		);
	}

	/**
	* @testdox <div><a> allows <div> as child
	*/
	public function test0266A932()
	{
		$this->runCase(
			'<div><a> allows <div> as child',
			'<div><a><xsl:apply-templates/></a></div>',
			'allowChild',
			'<div/>'
		);
	}

	/**
	* @testdox <span><a> denies <div> as child
	*/
	public function test8E52F053()
	{
		$this->runCase(
			'<span><a> denies <div> as child',
			'<span><a><xsl:apply-templates/></a></span>',
			'denyChild',
			'<div/>'
		);
	}

	/**
	* @testdox <audio> with no src attribute allows <source> as child
	*/
	public function test3B294484()
	{
		$this->runCase(
			'<audio> with no src attribute allows <source> as child',
			'<audio><xsl:apply-templates/></audio>',
			'allowChild',
			'<source/>'
		);
	}

	/**
	* @testdox <audio src="..."> denies <source> as child
	*/
	public function testE990B9F2()
	{
		$this->runCase(
			'<audio src="..."> denies <source> as child',
			'<audio src="{@src}"><xsl:apply-templates/></audio>',
			'denyChild',
			'<source/>'
		);
	}

	/**
	* @testdox <a> is considered transparent
	*/
	public function test922375F7()
	{
		$this->runCase(
			'<a> is considered transparent',
			'<a><xsl:apply-templates/></a>',
			'isTransparent'
		);
	}

	/**
	* @testdox <a><span> is not considered transparent
	*/
	public function test314E8100()
	{
		$this->runCase(
			'<a><span> is not considered transparent',
			'<a><span><xsl:apply-templates/></span></a>',
			'!isTransparent'
		);
	}

	/**
	* @testdox <span><a> is not considered transparent
	*/
	public function test444B39F8()
	{
		$this->runCase(
			'<span><a> is not considered transparent',
			'<span><a><xsl:apply-templates/></a></span>',
			'!isTransparent'
		);
	}

	/**
	* @testdox A template composed entirely of a single <xsl:apply-templates/> is considered transparent
	*/
	public function test70793519()
	{
		$this->runCase(
			'A template composed entirely of a single <xsl:apply-templates/> is considered transparent',
			'<xsl:apply-templates/>',
			'isTransparent'
		);
	}

	/**
	* @testdox <span> allows <unknownElement> as child
	*/
	public function test79E09FE9()
	{
		$this->runCase(
			'<span> allows <unknownElement> as child',
			'<span><xsl:apply-templates/></span>',
			'allowChild',
			'<unknownElement/>'
		);
	}

	/**
	* @testdox <unknownElement> allows <span> as child
	*/
	public function test4289BD7D()
	{
		$this->runCase(
			'<unknownElement> allows <span> as child',
			'<unknownElement><xsl:apply-templates/></unknownElement>',
			'allowChild',
			'<span/>'
		);
	}

	/**
	* @testdox <textarea> allows text nodes
	*/
	public function test1B650F69()
	{
		$this->runCase(
			'<textarea> allows text nodes',
			'<textarea><xsl:apply-templates/></textarea>',
			'allowText'
		);
	}

	/**
	* @testdox <table> disallows text nodes
	*/
	public function test96675F41()
	{
		$this->runCase(
			'<table> disallows text nodes',
			'<table><xsl:apply-templates/></table>',
			'denyText'
		);
	}

	/**
	* @testdox <table><tr><td> allows "Hi"
	*/
	public function test1B2ACE03()
	{
		$this->runCase(
			'<table><tr><td> allows "Hi"',
			'<table><tr><td><xsl:apply-templates/></td></tr></table>',
			'allowChild',
			'Hi'
		);
	}

	/**
	* @testdox <div><table> disallows "Hi"
	*/
	public function test5F404614()
	{
		$this->runCase(
			'<div><table> disallows "Hi"',
			'<div><table><xsl:apply-templates/></table></div>',
			'denyChild',
			'Hi'
		);
	}

	/**
	* @testdox <table> disallows <xsl:value-of/>
	*/
	public function test4E1E4A38()
	{
		$this->runCase(
			'<table> disallows <xsl:value-of/>',
			'<table><xsl:apply-templates/></table>',
			'denyChild',
			'<xsl:value-of select="@foo"/>'
		);
	}

	/**
	* @testdox <table> disallows <xsl:text>Hi</xsl:text>
	*/
	public function test78E6A7D9()
	{
		$this->runCase(
			'<table> disallows <xsl:text>Hi</xsl:text>',
			'<table><xsl:apply-templates/></table>',
			'denyChild',
			'<xsl:text>Hi</xsl:text>'
		);
	}

	/**
	* @testdox <table> allows <xsl:text>  </xsl:text>
	*/
	public function test107CB766()
	{
		$this->runCase(
			'<table> allows <xsl:text>  </xsl:text>',
			'<table><xsl:apply-templates/></table>',
			'allowChild',
			'<xsl:text>  </xsl:text>'
		);
	}

	/**
	* @testdox <b> should be reopened automatically
	*/
	public function test22F9A918()
	{
		$this->runCase(
			'<b> should be reopened automatically',
			'<b><xsl:apply-templates/></b>',
			'autoReopen'
		);
	}

	/**
	* @testdox <b><u> should be reopened automatically
	*/
	public function test7AB1C861()
	{
		$this->runCase(
			'<b><u> should be reopened automatically',
			'<b><u><xsl:apply-templates/></u></b>',
			'autoReopen'
		);
	}

	/**
	* @testdox <div> should not be reopened automatically
	*/
	public function test3068CA4C()
	{
		$this->runCase(
			'<div> should not be reopened automatically',
			'<div><xsl:apply-templates/></div>',
			'!autoReopen'
		);
	}

	/**
	* @testdox "Hi" should not be reopened automatically
	*/
	public function testA216F4AE()
	{
		$this->runCase(
			'"Hi" should not be reopened automatically',
			'Hi',
			'!autoReopen'
		);
	}

	/**
	* @testdox A template composed entirely of a single <xsl:apply-templates/> should not be reopened automatically
	*/
	public function test357C2DF0()
	{
		$this->runCase(
			'A template composed entirely of a single <xsl:apply-templates/> should not be reopened automatically',
			'<xsl:apply-templates/>',
			'!autoReopen'
		);
	}

	/**
	* @testdox <img> denies all descendants
	*/
	public function test44F68EAD()
	{
		$this->runCase(
			'<img> denies all descendants',
			'<img/>',
			'denyAll'
		);
	}

	/**
	* @testdox <hr><xsl:apply-templates/></hr> denies all descendants
	*/
	public function test19DAC173()
	{
		$this->runCase(
			'<hr><xsl:apply-templates/></hr> denies all descendants',
			'<hr><xsl:apply-templates/></hr>',
			'denyAll'
		);
	}

	/**
	* @testdox <div><hr><xsl:apply-templates/></hr></div> denies all descendants
	*/
	public function testB9757F1A()
	{
		$this->runCase(
			'<div><hr><xsl:apply-templates/></hr></div> denies all descendants',
			'<div><hr><xsl:apply-templates/></hr></div>',
			'denyAll'
		);
	}

	/**
	* @testdox <style> denies all descendants even if it has an <xsl:apply-templates/> child
	*/
	public function testFC5CC479()
	{
		$this->runCase(
			'<style> denies all descendants even if it has an <xsl:apply-templates/> child',
			'<style><xsl:apply-templates/></style>',
			'denyAll'
		);
	}

	/**
	* @testdox <span> does not deny all descendants if it has an <xsl:apply-templates/> child
	*/
	public function test8F0B951C()
	{
		$this->runCase(
			'<span> does not deny all descendants if it has an <xsl:apply-templates/> child',
			'<span><xsl:apply-templates/></span>',
			'!denyAll'
		);
	}

	/**
	* @testdox <span> denies all descendants if it does not have an <xsl:apply-templates/> child
	*/
	public function test83C38AC9()
	{
		$this->runCase(
			'<span> denies all descendants if it does not have an <xsl:apply-templates/> child',
			'<span></span>',
			'denyAll'
		);
	}

	/**
	* @testdox <colgroup span="2"> denies all descendants
	*/
	public function test3508F0F3()
	{
		$this->runCase(
			'<colgroup span="2"> denies all descendants',
			'<colgroup span="2"><xsl:apply-templates/></colgroup>',
			'denyAll'
		);
	}

	/**
	* @testdox <colgroup> denies all descendants
	*/
	public function testD01E4AFA()
	{
		$this->runCase(
			'<colgroup> denies all descendants',
			'<colgroup><xsl:apply-templates/></colgroup>',
			'!denyAll'
		);
	}

	/**
	* @testdox <pre> preserves whitespace
	*/
	public function test3A51B52B()
	{
		$this->runCase(
			'<pre> preserves whitespace',
			'<pre><xsl:apply-templates/></pre>',
			'preservesWhitespace'
		);
	}

	/**
	* @testdox <pre><code> preserves whitespace
	*/
	public function test8F524772()
	{
		$this->runCase(
			'<pre><code> preserves whitespace',
			'<pre><code><xsl:apply-templates/></code></pre>',
			'preservesWhitespace'
		);
	}

	/**
	* @testdox <span> does not preserve whitespace
	*/
	public function test9EE485B2()
	{
		$this->runCase(
			'<span> does not preserve whitespace',
			'<span><xsl:apply-templates/></span>',
			'!preservesWhitespace'
		);
	}

	/**
	* @testdox <img/> is void
	*/
	public function test5D210713()
	{
		$this->runCase(
			'<img/> is void',
			'<img><xsl:apply-templates/></img>',
			'isVoid'
		);
	}

	/**
	* @testdox <img> is void even with a <xsl:apply-templates/> child
	*/
	public function test53CD3F08()
	{
		$this->runCase(
			'<img> is void even with a <xsl:apply-templates/> child',
			'<img><xsl:apply-templates/></img>',
			'isVoid'
		);
	}

	/**
	* @testdox <span> is not void
	*/
	public function test2218364A()
	{
		$this->runCase(
			'<span> is not void',
			'<span><xsl:apply-templates/></span>',
			'!isVoid'
		);
	}

	/**
	* @testdox <blockquote> is a block-level element
	*/
	public function test602395E3()
	{
		$this->runCase(
			'<blockquote> is a block-level element',
			'<blockquote><xsl:apply-templates/></blockquote>',
			'isBlock'
		);
	}

	/**
	* @testdox <span> is not a block-level element
	*/
	public function testE222869D()
	{
		$this->runCase(
			'<span> is not a block-level element',
			'<span><xsl:apply-templates/></span>',
			'!isBlock'
		);
	}
	// End of content generated by ../../../../scripts/patchTemplateForensicsTest.php

	public function getData()
	{
		return array(
			array(
				'<span> does not allow <div> as child',
				'<span><xsl:apply-templates/></span>',
				'denyChild',
				'<div><xsl:apply-templates/></div>'
			),
			array(
				'<span> does not allow <div> as child even with a <span> sibling',
				'<span><xsl:apply-templates/></span>',
				'denyChild',
				'<span>xxx</span><div><xsl:apply-templates/></div>'
			),
			array(
				'<span> and <div> does not allow <span> and <div> as child',
				'<span><xsl:apply-templates/></span><div><xsl:apply-templates/></div>',
				'denyChild',
				'<span/><div/>'
			),
			array(
				'<li> closes parent <li>',
				'<li/>',
				'closeParent',
				'<li><xsl:apply-templates/></li>'
			),
			array(
				'<div> closes parent <p>',
				'<div/>',
				'closeParent',
				'<p><xsl:apply-templates/></p>'
			),
			array(
				'<p> closes parent <p>',
				'<p/>',
				'closeParent',
				'<p><xsl:apply-templates/></p>'
			),
			array(
				'<div> does not close parent <div>',
				'<div/>',
				'!closeParent',
				'<div><xsl:apply-templates/></div>'
			),
			// This test mainly exist to ensure nothing bad happens with HTML tags that don't have
			// a "cp" value in TemplateForensics::$htmlElements
			array(
				'<span> does not close parent <span>',
				'<span/>',
				'!closeParent',
				'<span><xsl:apply-templates/></span>'
			),
			array(
				'<a> denies <a> as descendant',
				'<a><xsl:apply-templates/></a>',
				'denyDescendant',
				'<a/>'
			),
			array(
				'<a> allows <img> with no usemap attribute as child',
				'<a><xsl:apply-templates/></a>',
				'allowChild',
				'<img/>'
			),
			array(
				'<a> denies <img usemap="#foo"> as child',
				'<a><xsl:apply-templates/></a>',
				'denyChild',
				'<img usemap="#foo"/>'
			),
			array(
				'<div><a> allows <div> as child',
				'<div><a><xsl:apply-templates/></a></div>',
				'allowChild',
				'<div/>'
			),
			array(
				'<span><a> denies <div> as child',
				'<span><a><xsl:apply-templates/></a></span>',
				'denyChild',
				'<div/>'
			),
			array(
				'<audio> with no src attribute allows <source> as child',
				'<audio><xsl:apply-templates/></audio>',
				'allowChild',
				'<source/>'
			),
			array(
				'<audio src="..."> denies <source> as child',
				'<audio src="{@src}"><xsl:apply-templates/></audio>',
				'denyChild',
				'<source/>'
			),
			array(
				'<a> is considered transparent',
				'<a><xsl:apply-templates/></a>',
				'isTransparent'
			),
			array(
				'<a><span> is not considered transparent',
				'<a><span><xsl:apply-templates/></span></a>',
				'!isTransparent'
			),
			array(
				'<span><a> is not considered transparent',
				'<span><a><xsl:apply-templates/></a></span>',
				'!isTransparent'
			),
			array(
				'A template composed entirely of a single <xsl:apply-templates/> is considered transparent',
				'<xsl:apply-templates/>',
				'isTransparent'
			),
			array(
				'<span> allows <unknownElement> as child',
				'<span><xsl:apply-templates/></span>',
				'allowChild',
				'<unknownElement/>'
			),
			array(
				'<unknownElement> allows <span> as child',
				'<unknownElement><xsl:apply-templates/></unknownElement>',
				'allowChild',
				'<span/>'
			),
			array(
				'<textarea> allows text nodes',
				'<textarea><xsl:apply-templates/></textarea>',
				'allowText'
			),
			array(
				'<table> disallows text nodes',
				'<table><xsl:apply-templates/></table>',
				'denyText'
			),
			array(
				'<table><tr><td> allows "Hi"',
				'<table><tr><td><xsl:apply-templates/></td></tr></table>',
				'allowChild',
				'Hi'
			),
			array(
				'<div><table> disallows "Hi"',
				'<div><table><xsl:apply-templates/></table></div>',
				'denyChild',
				'Hi'
			),
			array(
				'<table> disallows <xsl:value-of/>',
				'<table><xsl:apply-templates/></table>',
				'denyChild',
				'<xsl:value-of select="@foo"/>'
			),
			array(
				'<table> disallows <xsl:text>Hi</xsl:text>',
				'<table><xsl:apply-templates/></table>',
				'denyChild',
				'<xsl:text>Hi</xsl:text>'
			),
			array(
				'<table> allows <xsl:text>  </xsl:text>',
				'<table><xsl:apply-templates/></table>',
				'allowChild',
				'<xsl:text>  </xsl:text>'
			),
			array(
				'<b> should be reopened automatically',
				'<b><xsl:apply-templates/></b>',
				'autoReopen'
			),
			array(
				'<b><u> should be reopened automatically',
				'<b><u><xsl:apply-templates/></u></b>',
				'autoReopen'
			),
			array(
				'<div> should not be reopened automatically',
				'<div><xsl:apply-templates/></div>',
				'!autoReopen'
			),
			array(
				'"Hi" should not be reopened automatically',
				'Hi',
				'!autoReopen'
			),
			array(
				'A template composed entirely of a single <xsl:apply-templates/> should not be reopened automatically',
				'<xsl:apply-templates/>',
				'!autoReopen'
			),
			array(
				'<img> denies all descendants',
				'<img/>',
				'denyAll'
			),
			array(
				'<hr><xsl:apply-templates/></hr> denies all descendants',
				'<hr><xsl:apply-templates/></hr>',
				'denyAll'
			),
			array(
				'<div><hr><xsl:apply-templates/></hr></div> denies all descendants',
				'<div><hr><xsl:apply-templates/></hr></div>',
				'denyAll'
			),
			array(
				'<style> denies all descendants even if it has an <xsl:apply-templates/> child',
				'<style><xsl:apply-templates/></style>',
				'denyAll'
			),
			array(
				'<span> does not deny all descendants if it has an <xsl:apply-templates/> child',
				'<span><xsl:apply-templates/></span>',
				'!denyAll'
			),
			array(
				'<span> denies all descendants if it does not have an <xsl:apply-templates/> child',
				'<span></span>',
				'denyAll'
			),
			array(
				'<colgroup span="2"> denies all descendants',
				'<colgroup span="2"><xsl:apply-templates/></colgroup>',
				'denyAll'
			),
			array(
				'<colgroup> denies all descendants',
				'<colgroup><xsl:apply-templates/></colgroup>',
				'!denyAll'
			),
			array(
				'<pre> preserves whitespace',
				'<pre><xsl:apply-templates/></pre>',
				'preservesWhitespace'
			),
			array(
				'<pre><code> preserves whitespace',
				'<pre><code><xsl:apply-templates/></code></pre>',
				'preservesWhitespace'
			),
			array(
				'<span> does not preserve whitespace',
				'<span><xsl:apply-templates/></span>',
				'!preservesWhitespace'
			),
			array(
				'<img/> is void',
				'<img><xsl:apply-templates/></img>',
				'isVoid'
			),
			array(
				'<img> is void even with a <xsl:apply-templates/> child',
				'<img><xsl:apply-templates/></img>',
				'isVoid'
			),
			array(
				'<span> is not void',
				'<span><xsl:apply-templates/></span>',
				'!isVoid'
			),
			array(
				'<blockquote> is a block-level element',
				'<blockquote><xsl:apply-templates/></blockquote>',
				'isBlock'
			),
			array(
				'<span> is not a block-level element',
				'<span><xsl:apply-templates/></span>',
				'!isBlock'
			),
		);
	}
}