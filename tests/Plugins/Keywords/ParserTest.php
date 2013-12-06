<?php

namespace s9e\TextFormatter\Tests\Plugins\Keywords;

use s9e\TextFormatter\Configurator;
use s9e\TextFormatter\Plugins\Keywords\Parser;
use s9e\TextFormatter\Tests\Plugins\ParsingTestsRunner;
use s9e\TextFormatter\Tests\Plugins\ParsingTestsJavaScriptRunner;
use s9e\TextFormatter\Tests\Plugins\RenderingTestsRunner;
use s9e\TextFormatter\Tests\Test;

/**
* @covers s9e\TextFormatter\Plugins\Keywords\Parser
*/
class ParserTest extends Test
{
	use ParsingTestsRunner;
	use ParsingTestsJavaScriptRunner;
//	use RenderingTestsRunner;

	public function getParsingTests()
	{
		return [
			[
				'foo bar baz',
				'<rt><KEYWORD value="foo">foo</KEYWORD> bar baz</rt>',
				[],
				function ($configurator)
				{
					$configurator->Keywords->add('foo');
				}
			],
			[
				'foo bar baz',
				'<rt><FOO value="foo">foo</FOO> bar baz</rt>',
				['tagName' => 'FOO'],
				function ($configurator)
				{
					$configurator->Keywords->add('foo');
				}
			],
			[
				'foo bar baz',
				'<rt><KEYWORD foo="foo">foo</KEYWORD> bar baz</rt>',
				['attrName' => 'foo'],
				function ($configurator)
				{
					$configurator->Keywords->add('foo');
				}
			],
			[
				'foo foo foo',
				'<rt><KEYWORD value="foo">foo</KEYWORD> <KEYWORD value="foo">foo</KEYWORD> <KEYWORD value="foo">foo</KEYWORD></rt>',
				[],
				function ($configurator)
				{
					$configurator->Keywords->add('foo');
				}
			],
			[
				'foo foo foo',
				'<rt><KEYWORD value="foo">foo</KEYWORD> foo foo</rt>',
				[],
				function ($configurator)
				{
					$configurator->Keywords->add('foo');
					$configurator->Keywords->onlyFirst = true;
				}
			],
			[
				'foo foo bar bar',
				'<rt><KEYWORD value="foo">foo</KEYWORD> foo <KEYWORD value="bar">bar</KEYWORD> bar</rt>',
				[],
				function ($configurator)
				{
					$configurator->Keywords->add('foo');
					$configurator->Keywords->add('bar');
					$configurator->Keywords->onlyFirst = true;
				}
			],
		];
	}
}