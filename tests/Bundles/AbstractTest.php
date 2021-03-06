<?php

namespace s9e\TextFormatter\Tests\Bundles;

use s9e\TextFormatter\Tests\Test;

/**
* @covers s9e\TextFormatter\Bundle
*/
abstract class AbstractTest extends Test
{
	protected static function getBundleName()
	{
		$className = get_called_class();

		return substr($className, 1 + strrpos($className, '\\'), -4);
	}

	protected static function getClassName()
	{
		$bundleName = static::getBundleName();

		return 's9e\\TextFormatter\\Bundles\\' . $bundleName;
	}

	public static function setUpBeforeClass()
	{
		$className = static::getClassName();
		$className::reset();
		$className::parse('');
		$className::render('<t></t>');
		$className::getCachedParser()->registeredVars['cacheDir'] = __DIR__ . '/../.cache';
	}

	public static function tearDownAfterClass()
	{
		$className = static::getClassName();
		$className::reset();
	}

	/**
	* @testdox getCachedParser() returns an instance of s9e\TextFormatter\Parser
	*/
	public function testGetCachedParser()
	{
		$className = static::getClassName();
		$this->assertInstanceOf(
			's9e\\TextFormatter\\Parser',
			$className::getCachedParser()
		);
	}

	/**
	* @testdox getCachedParser() returns the same instance of s9e\TextFormatter\Parser
	*/
	public function testGetCachedParserSame()
	{
		$className = static::getClassName();
		$this->assertSame(
			$className::getCachedParser(),
			$className::getCachedParser()
		);
	}

	/**
	* @testdox getParser() returns an instance of s9e\TextFormatter\Parser
	*/
	public function testGetParser()
	{
		$className = static::getClassName();
		$this->assertInstanceOf(
			's9e\\TextFormatter\\Parser',
			$className::getParser()
		);
	}

	/**
	* @testdox getParser() returns a new instance of s9e\TextFormatter\Parser
	*/
	public function testGetParserNew()
	{
		$className = static::getClassName();
		$this->assertNotSame(
			$className::getParser(),
			$className::getParser()
		);
	}

	/**
	* @testdox getCachedRenderer() returns an instance of s9e\TextFormatter\Renderer
	*/
	public function testGetCachedRenderer()
	{
		$className = static::getClassName();
		$this->assertInstanceOf(
			's9e\\TextFormatter\\Renderer',
			$className::getCachedRenderer()
		);
	}

	/**
	* @testdox getCachedRenderer() returns the same instance of s9e\TextFormatter\Renderer
	*/
	public function testGetCachedRendererNew()
	{
		$className = static::getClassName();
		$this->assertSame(
			$className::getCachedRenderer(),
			$className::getCachedRenderer()
		);
	}

	/**
	* @testdox getRenderer() returns an instance of s9e\TextFormatter\Renderer
	*/
	public function testGetRenderer()
	{
		$className = static::getClassName();
		$this->assertInstanceOf(
			's9e\\TextFormatter\\Renderer',
			$className::getRenderer()
		);
	}

	/**
	* @testdox getRenderer() returns a new instance of s9e\TextFormatter\Renderer
	*/
	public function testGetRendererNew()
	{
		$className = static::getClassName();
		$this->assertNotSame(
			$className::getRenderer(),
			$className::getRenderer()
		);
	}

	/**
	* @testdox The renderer can be un/serialized
	*/
	public function testSerialize()
	{
		$className = static::getClassName();
		$renderer  = $className::getRenderer();
		$this->assertEquals($renderer, unserialize(serialize($renderer)));
	}

	/**
	* @testdox The quick renderer can handle unknown tags
	*/
	public function testQuickRendererUnknownTag()
	{
		$className = static::getClassName();
		$className::getCachedRenderer()->quickRenderingTest = '((?!))';
		$this->assertSame('unknown', $className::render('<r><UNKNOWN>unknown</UNKNOWN></r>'));
	}

	/**
	* @testdox The quick renderer can handle unknown self-closing tags
	*/
	public function testQuickRendererUnknownSelfClosingTag()
	{
		$className = static::getClassName();
		$className::getCachedRenderer()->quickRenderingTest = '((?!))';
		$this->assertSame('unknown', $className::render('<r><UNKNOWN/>unknown</r>'));
	}

	/**
	* @testdox The quick renderer can handle comments
	* @expectedException InvalidArgumentException comments
	*/
	public function testQuickRendererComment()
	{
		$className = static::getClassName();
		$className::getCachedRenderer()->quickRenderingTest = '((?!))';
		$this->assertSame('unknown', $className::render('<r><!---->unknown</r>'));
	}

	/**
	* @testdox The quick renderer can handle processing instructions
	* @expectedException InvalidArgumentException Processing
	*/
	public function testQuickRendererProcessingInstruction()
	{
		$className = static::getClassName();
		$className::getCachedRenderer()->quickRenderingTest = '((?!))';
		$this->assertSame('unknown', $className::render('<r><?x ?>unknown</r>'));
	}

	/**
	* @testdox Rendering tests
	* @dataProvider getRenderingTests
	*/
	public function testRender($text, $html, $params = [])
	{
		$className = static::getClassName();
		$className::getCachedRenderer()->quickRenderingTest = '()';

		$this->assertSame($html, $className::render($className::parse($text), $params));

		// Reset the renderer if params were set
		if ($params)
		{
			$className::reset();
		}
	}

	/**
	* @testdox Rendering tests (quick renderer)
	* @dataProvider getRenderingTests
	*/
	public function testRenderQuick($text, $html, $params = [])
	{
		$className = static::getClassName();
		$className::getCachedRenderer()->quickRenderingTest = '((?!))';

		$this->assertSame($html, $className::render($className::parse($text), $params));

		// Reset the renderer if params were set
		if ($params)
		{
			$className::reset();
		}
	}

	public function getRenderingTests()
	{
		$bundleName = static::getBundleName();

		$tests = [];
		foreach (glob(__DIR__ . '/data/' . $bundleName . '/*.txt') as $filepath)
		{
			$test = [
				file_get_contents($filepath),
				file_get_contents(substr($filepath, 0, -3) . 'html')
			];

			if (file_exists(substr($filepath, 0, -3) . 'json'))
			{
				if (!extension_loaded('json'))
				{
					continue;
				}

				$test[] = json_decode(
					file_get_contents(substr($filepath, 0, -3) . 'json'),
					true
				);
			}

			$tests[] = $test;
		}

		return $tests;
	}
}