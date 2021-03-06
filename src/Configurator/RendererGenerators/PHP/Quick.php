<?php

/**
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2015 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\RendererGenerators\PHP;

use RuntimeException;
use s9e\TextFormatter\Configurator\Helpers\RegexpBuilder;

class Quick
{
	/**
	* Generate the Quick renderer's source
	*
	* @param  array  $compiledTemplates Array of tagName => compiled template
	* @return string
	*/
	public static function getSource(array $compiledTemplates)
	{
		$map = [];
		$tagNames = [];
		$unsupported = [];

		foreach ($compiledTemplates as $tagName => $php)
		{
			// Ignore system tags
			if (preg_match('(^(?:br|[ieps])$)', $tagName))
			{
				continue;
			}

			$rendering = self::getRenderingStrategy($php);
			if ($rendering === false)
			{
				$unsupported[] = $tagName;
				continue;
			}

			foreach ($rendering as $i => list($strategy, $replacement))
			{
				$match = (($i) ? '/' : '') . $tagName;
				$map[$strategy][$match] = $replacement;
			}

			// Record the names of tags whose template does not contain a passthrough
			if (!isset($rendering[1]))
			{
				$tagNames[] = $tagName;
			}
		}

		$php = [];
		if (isset($map['static']))
		{
			$php[] = '	private static $static=' . self::export($map['static']) . ';';
		}
		if (isset($map['dynamic']))
		{
			$php[] = '	private static $dynamic=' . self::export($map['dynamic']) . ';';
		}
		if (isset($map['php']))
		{
			list($quickBranches, $quickSource) = self::generateBranchTable('$qb', $map['php']);

			$php[] = '	private static $attributes;';
			$php[] = '	private static $quickBranches=' . self::export($quickBranches) . ';';
		}

		if (!empty($unsupported))
		{
			$regexp = '(<' . RegexpBuilder::fromList($unsupported, ['useLookahead' => true]) . '[ />])';
			$php[] = '	public $quickRenderingTest=' . var_export($regexp, true) . ';';
		}

		$php[] = '';
		$php[] = '	protected function renderQuick($xml)';
		$php[] = '	{';
		$php[] = '		$xml = $this->decodeSMP($xml);';

		if (isset($map['php']))
		{
			// Reset saved attributes before we start rendering
			$php[] = '		self::$attributes = [];';
		}

		// Build the regexp that matches all the tags
		$regexp  = '(<(?:(?!/)(';
		$regexp .= ($tagNames) ? RegexpBuilder::fromList($tagNames) : '(?!)';
		$regexp .= ')(?: [^>]*)?>.*?</\\1|(/?(?!br/|p>)[^ />]+)[^>]*?(/)?)>)';

		$php[] = '		$html = preg_replace_callback(';
		$php[] = '			' . var_export($regexp, true) . ',';
		$php[] = "			[\$this, 'quick'],";
		$php[] = '			preg_replace(';
		$php[] = "				'(<[eis]>[^<]*</[eis]>)',";
		$php[] = "				'',";
		$php[] = '				substr($xml, 1 + strpos($xml, \'>\'), -4)';
		$php[] = '			)';
		$php[] = '		);';
		$php[] = '';
		$php[] = "		return str_replace('<br/>', '<br>', \$html);";
		$php[] = '	}';
		$php[] = '';
		$php[] = '	protected function quick($m)';
		$php[] = '	{';
		$php[] = '		if (isset($m[2]))';
		$php[] = '		{';
		$php[] = '			$id = $m[2];';
		$php[] = '';
		$php[] = '			if (isset($m[3]))';
		$php[] = '			{';
		$php[] = '				unset($m[3]);';
		$php[] = '';
		$php[] = '				$m[0] = substr($m[0], 0, -2) . \'>\';';
		$php[] = '				$html = $this->quick($m);';
		$php[] = '';
		$php[] = '				$m[0] = \'</\' . $id . \'>\';';
		$php[] = '				$m[2] = \'/\' . $id;';
		$php[] = '				$html .= $this->quick($m);';
		$php[] = '';
		$php[] = '				return $html;';
		$php[] = '			}';
		$php[] = '		}';
		$php[] = '		else';
		$php[] = '		{';
		$php[] = '			$id = $m[1];';
		$php[] = '';
		$php[] = '			$lpos = 1 + strpos($m[0], \'>\');';
		$php[] = '			$rpos = strrpos($m[0], \'<\');';
		$php[] = '			$textContent = substr($m[0], $lpos, $rpos - $lpos);';
		$php[] = '';
		$php[] = '			if (strpos($textContent, \'<\') !== false)';
		$php[] = '			{';
		$php[] = '				throw new \\RuntimeException;';
		$php[] = '			}';
		$php[] = '';
		$php[] = '			$textContent = htmlspecialchars_decode($textContent);';
		$php[] = '		}';
		$php[] = '';

		if (isset($map['static']))
		{
			$php[] = '		if (isset(self::$static[$id]))';
			$php[] = '		{';
			$php[] = '			return self::$static[$id];';
			$php[] = '		}';
			$php[] = '';
		}

		if (isset($map['dynamic']))
		{
			$php[] = '		if (isset(self::$dynamic[$id]))';
			$php[] = '		{';
			$php[] = '			list($match, $replace) = self::$dynamic[$id];';
			$php[] = '			return preg_replace($match, $replace, $m[0], 1, $cnt);';
			$php[] = '		}';
			$php[] = '';
		}

		if (isset($map['php']))
		{
			$php[] = '		if (!isset(self::$quickBranches[$id]))';
			$php[] = '		{';
		}

		// Test for <! and <? tags
		$condition = "\$id[0] === '!' || \$id[0] === '?'";
		if (!empty($unsupported))
		{
			$regexp = '(^/?' . RegexpBuilder::fromList($unsupported) . '$)';
			$condition .= ' || preg_match(' . var_export($regexp, true) . ', $id)';
		}

		$php[] = '			if (' . $condition . ')';
		$php[] = '			{';
		$php[] = '				throw new \\RuntimeException;';
		$php[] = '			}';
		$php[] = "			return '';";

		if (isset($map['php']))
		{
			$php[] = '		}';
			$php[] = '';
			$php[] = '		$attributes = [];';
			$php[] = '		if (strpos($m[0], \'="\') !== false)';
			$php[] = '		{';
			$php[] = '			preg_match_all(\'(([^ =]++)="([^"]*))S\', substr($m[0], 0, strpos($m[0], \'>\')), $matches);';
			$php[] = '			foreach ($matches[1] as $i => $attrName)';
			$php[] = '			{';
			$php[] = '				$attributes[$attrName] = $matches[2][$i];';
			$php[] = '			}';
			$php[] = '		}';
			$php[] = '';
			$php[] = '		$qb = self::$quickBranches[$id];';
			$php[] = '		' . $quickSource;
			$php[] = '';
			$php[] = '		return $html;';
		}

		$php[] = '	}';

		return implode("\n", $php);
	}

	/**
	* Export an array as PHP
	*
	* @param  array  $arr
	* @return string
	*/
	protected static function export(array $arr)
	{
		ksort($arr);
		$entries = [];

		$naturalKey = 0;
		foreach ($arr as $k => $v)
		{
			$entries[] = (($k === $naturalKey) ? '' : var_export($k, true) . '=>')
			           . ((is_array($v)) ? self::export($v) : var_export($v, true));

			$naturalKey = $k + 1;
		}

		return '[' . implode(',', $entries) . ']';
	}

	/**
	* Compute the rendering strategy for a compiled template
	*
	* @param  string     $php Template compiled for the PHP renderer
	* @return array|bool      An array containing the type of replacement ('static', 'dynamic' or
	*                         'php') and the replacement, or FALSE
	*/
	public static function getRenderingStrategy($php)
	{
		$chunks = explode('$this->at($node);', $php);
		$renderings = [];

		// If there is zero or one passthrough, we try string replacements first
		if (count($chunks) <= 2)
		{
			foreach ($chunks as $k => $chunk)
			{
				// Try a static replacement first
				$rendering = self::getStaticRendering($chunk);
				if ($rendering !== false)
				{
					$renderings[$k] = ['static', $rendering];
					continue;
				}

				// If this is the first chunk, we can try a dynamic replacement. This wouldn't work
				// for the second chunk because we wouldn't have access to the attribute values
				if ($k === 0)
				{
					$rendering = self::getDynamicRendering($chunk);
					if ($rendering !== false)
					{
						$renderings[$k] = ['dynamic', $rendering];
						continue;
					}
				}

				$renderings[$k] = false;
			}

			// If we can completely render a template with string replacements, we return now
			if (!in_array(false, $renderings, true))
			{
				return $renderings;
			}
		}

		// Test whether this template can be rendered with the Quick rendering
		$phpRenderings = self::getQuickRendering($php);
		if ($phpRenderings === false)
		{
			return false;
		}

		// Keep string rendering where possible, use PHP rendering wherever else
		foreach ($phpRenderings as $i => $phpRendering)
		{
			if (!isset($renderings[$i]) || $renderings[$i] === false)
			{
				$renderings[$i] = ['php', $phpRendering];
			}
		}

		return $renderings;
	}

	/**
	* Generate the code for rendering a compiled template with the Quick renderer
	*
	* Parse and record every code path that contains a passthrough. Parse every if-else structure.
	* When the whole structure is parsed, there are 3 possible situations:
	*  - no code path contains a passthrough, in which case we discard the data
	*  - all the code paths including the mandatory "else" branch contain a passthrough, in which
	*    case we keep the data
	*  - only some code paths contain a passthrough, in which case we throw an exception
	*
	* @param  string     $php Template compiled for the PHP renderer
	* @return array|bool      An array containing one or two strings of PHP, or FALSE
	*/
	protected static function getQuickRendering($php)
	{
		// xsl:apply-templates elements with a select expression are not supported
		if (preg_match('(\\$this->at\\((?!\\$node\\);))', $php))
		{
			return false;
		}

		// Tokenize the PHP and add an empty token as terminator
		$tokens   = token_get_all('<?php ' . $php);
		$tokens[] = [0, ''];

		// Remove the first token, which is a T_OPEN_TAG
		array_shift($tokens);
		$cnt = count($tokens);

		// Prepare the main branch
		$branch = [
			// We purposefully use a value that can never match
			'braces'      => -1,
			'branches'    => [],
			'head'        => '',
			'passthrough' => 0,
			'statement'   => '',
			'tail'        => ''
		];

		$braces = 0;
		$i = 0;
		do
		{
			// Test whether we've reached a passthrough
			if ($tokens[$i    ][0] === T_VARIABLE
			 && $tokens[$i    ][1] === '$this'
			 && $tokens[$i + 1][0] === T_OBJECT_OPERATOR
			 && $tokens[$i + 2][0] === T_STRING
			 && $tokens[$i + 2][1] === 'at'
			 && $tokens[$i + 3]    === '('
			 && $tokens[$i + 4][0] === T_VARIABLE
			 && $tokens[$i + 4][1] === '$node'
			 && $tokens[$i + 5]    === ')'
			 && $tokens[$i + 6]    === ';')
			{
				if (++$branch['passthrough'] > 1)
				{
					// Multiple passthroughs are not supported
					return false;
				}

				// Skip to the semi-colon
				$i += 6;

				continue;
			}

			$key = ($branch['passthrough']) ? 'tail' : 'head';
			$branch[$key] .= (is_array($tokens[$i])) ? $tokens[$i][1] : $tokens[$i];

			if ($tokens[$i] === '{')
			{
				++$braces;
				continue;
			}

			if ($tokens[$i] === '}')
			{
				--$braces;

				if ($branch['braces'] === $braces)
				{
					// Remove the last brace from the branch's content
					$branch[$key] = substr($branch[$key], 0, -1);

					// Jump back to the parent branch
					$branch =& $branch['parent'];

					// Copy the current index to look ahead
					$j = $i;

					// Skip whitespace
					while ($tokens[++$j][0] === T_WHITESPACE);

					// Test whether this is the last brace of an if-else structure by looking for
					// an additional elseif/else case
					if ($tokens[$j][0] !== T_ELSEIF
					 && $tokens[$j][0] !== T_ELSE)
					{
						$passthroughs = self::getBranchesPassthrough($branch['branches']);

						if ($passthroughs === [0])
						{
							// No branch was passthrough, move their PHP source back to this branch
							// then discard the data
							foreach ($branch['branches'] as $child)
							{
								$branch['head'] .= $child['statement'] . '{' . $child['head'] . '}';
							}

							$branch['branches'] = [];
							continue;
						}

						if ($passthroughs === [1])
						{
							// All branches were passthrough, so their parent is passthrough
							++$branch['passthrough'];

							continue;
						}

						// Mixed branches (with/out passthrough) are not supported
						return false;
					}
				}

				continue;
			}

			// We don't have to record child branches if we know that current branch is passthrough.
			// If a child branch contains a passthrough, it will be treated as a multiple
			// passthrough and we will abort
			if ($branch['passthrough'])
			{
				continue;
			}

			if ($tokens[$i][0] === T_IF
			 || $tokens[$i][0] === T_ELSEIF
			 || $tokens[$i][0] === T_ELSE)
			{
				// Remove the statement from the branch's content
				$branch[$key] = substr($branch[$key], 0, -strlen($tokens[$i][1]));

				// Create a new branch
				$branch['branches'][] = [
					'braces'      => $braces,
					'branches'    => [],
					'head'        => '',
					'parent'      => &$branch,
					'passthrough' => 0,
					'statement'   => '',
					'tail'        => ''
				];

				// Jump to the new branch
				$branch =& $branch['branches'][count($branch['branches']) - 1];

				// Record the PHP statement
				do
				{
					$branch['statement'] .= (is_array($tokens[$i])) ? $tokens[$i][1] : $tokens[$i];
				}
				while ($tokens[++$i] !== '{');

				// Account for the brace in the statement
				++$braces;
			}
		}
		while (++$i < $cnt);

		list($head, $tail) = self::buildPHP($branch['branches']);
		$head  = $branch['head'] . $head;
		$tail .= $branch['tail'];

		// Convert the PHP renderer source to the format used in the Quick renderer
		self::convertPHP($head, $tail, (bool) $branch['passthrough']);

		// Test whether any method call was left unconverted. If so, we cannot render this template
		if (preg_match('((?<!-)->(?!params\\[))', $head . $tail))
		{
			return false;
		}

		return ($branch['passthrough']) ? [$head, $tail] : [$head];
	}

	/**
	* Convert the two sides of a compiled template to quick rendering
	*
	* @param  string &$head
	* @param  string &$tail
	* @param  bool    $passthrough
	* @return void
	*/
	protected static function convertPHP(&$head, &$tail, $passthrough)
	{
		// Test whether the attributes must be saved when rendering the head because they're needed
		// when rendering the tail
		$saveAttributes = (bool) preg_match('(\\$node->(?:get|has)Attribute)', $tail);

		// Collect the names of all the attributes so that we can initialize them with a null value
		// to avoid undefined variable notices. We exclude attributes that seem to be in an if block
		// that tests its existence beforehand. This last part is not an accurate process as it
		// would be much more expensive to do it accurately but where it fails the only consequence
		// is we needlessly add the attribute to the list. There is no difference in functionality
		preg_match_all(
			"(\\\$node->getAttribute\\('([^']+)'\\))",
			preg_replace_callback(
				'(if\\(\\$node->hasAttribute\\(([^\\)]+)[^}]+)',
				function ($m)
				{
					return str_replace('$node->getAttribute(' . $m[1] . ')', '', $m[0]);
				},
				$head . $tail
			),
			$matches
		);
		$attrNames = array_unique($matches[1]);

		// Replace the source in $head and $tail
		self::replacePHP($head);
		self::replacePHP($tail);

		if (!$passthrough)
		{
			$head = str_replace('$node->textContent', '$textContent', $head);
		}

		if (!empty($attrNames))
		{
			ksort($attrNames);
			$head = "\$attributes+=['" . implode("'=>null,'", $attrNames) . "'=>null];" . $head;
		}

		if ($saveAttributes)
		{
			$head .= 'self::$attributes[]=$attributes;';
			$tail  = '$attributes=array_pop(self::$attributes);' . $tail;
		}
	}

	/**
	* Replace the PHP code used in a compiled template to be used by the Quick renderer
	*
	* @param  string &$php
	* @return void
	*/
	protected static function replacePHP(&$php)
	{
		if ($php === '')
		{
			return;
		}

		$php = str_replace('$this->out', '$html', $php);

		// Expression that matches a $node->getAttribute() call and captures its string argument
		$getAttribute = "\\\$node->getAttribute\\(('[^']+')\\)";

		// An attribute value escaped as ENT_NOQUOTES. We only need to unescape quotes
		$php = preg_replace(
			'(htmlspecialchars\\(' . $getAttribute . ',' . ENT_NOQUOTES . '\\))',
			"str_replace('&quot;','\"',\$attributes[\$1])",
			$php
		);

		// An attribute value escaped as ENT_COMPAT can be used as-is
		$php = preg_replace(
			'(htmlspecialchars\\(' . $getAttribute . ',' . ENT_COMPAT . '\\))',
			'$attributes[$1]',
			$php
		);

		// Character replacement can be performed directly on the escaped value provided that it is
		// then escaped as ENT_COMPAT and that replacements do not interfere with the escaping of
		// the characters &<>" or their representation &amp;&lt;&gt;&quot;
		$php = preg_replace(
			'(htmlspecialchars\\(strtr\\(' . $getAttribute . ",('[^\"&\\\\';<>aglmopqtu]+'),('[^\"&\\\\'<>]+')\\)," . ENT_COMPAT . '\\))',
			'strtr($attributes[$1],$2,$3)',
			$php
		);

		// A comparison between two attributes. No need to unescape
		$php = preg_replace(
			'(' . $getAttribute . '(!?=+)' . $getAttribute . ')',
			'$attributes[$1]$2$attributes[$3]',
			$php
		);

		// A comparison between an attribute and a literal string. Rather than unescape the
		// attribute value, we escape the literal. This applies to comparisons using XPath's
		// contains() as well (translated to PHP's strpos())
		$php = preg_replace_callback(
			'(' . $getAttribute . "===('.*?(?<!\\\\)(?:\\\\\\\\)*'))s",
			function ($m)
			{
				return '$attributes[' . $m[1] . ']===' . htmlspecialchars($m[2], ENT_COMPAT);
			},
			$php
		);
		$php = preg_replace_callback(
			"(('.*?(?<!\\\\)(?:\\\\\\\\)*')===" . $getAttribute . ')s',
			function ($m)
			{
				return htmlspecialchars($m[1], ENT_COMPAT) . '===$attributes[' . $m[2] . ']';
			},
			$php
		);
		$php = preg_replace_callback(
			'(strpos\\(' . $getAttribute . ",('.*?(?<!\\\\)(?:\\\\\\\\)*')\\)([!=]==(?:0|false)))s",
			function ($m)
			{
				return 'strpos($attributes[' . $m[1] . "]," . htmlspecialchars($m[2], ENT_COMPAT) . ')' . $m[3];
			},
			$php
		);
		$php = preg_replace_callback(
			"(strpos\\(('.*?(?<!\\\\)(?:\\\\\\\\)*')," . $getAttribute . '\\)([!=]==(?:0|false)))s',
			function ($m)
			{
				return 'strpos(' . htmlspecialchars($m[1], ENT_COMPAT) . ',$attributes[' . $m[2] . '])' . $m[3];
			},
			$php
		);

		// An attribute value used in an arithmetic comparison or operation does not need to be
		// unescaped. The same applies to empty() and isset()
		$php = preg_replace(
			'(' . $getAttribute . '(?=(?:==|[-+*])\\d+))',
			'$attributes[$1]',
			$php
		);
		$php = preg_replace(
			'((?<!\\w)(\\d+(?:==|[-+*]))' . $getAttribute . ')',
			'$1$attributes[$2]',
			$php
		);
		$php = preg_replace(
			"(empty\\(\\\$node->getAttribute\\(('[^']+')\\)\\))",
			'empty($attributes[$1])',
			$php
		);
		$php = preg_replace(
			"(\\\$node->hasAttribute\\(('[^']+')\\))",
			'isset($attributes[$1])',
			$php
		);

		// In all other situations, unescape the attribute value before use
		$php = preg_replace(
			"(\\\$node->getAttribute\\(('[^']+')\\))",
			'htmlspecialchars_decode($attributes[$1])',
			$php
		);

		if (substr($php, 0, 7) === '$html.=')
		{
			$php = '$html=' . substr($php, 7);
		}
		else
		{
			$php = "\$html='';" . $php;
		}
	}

	/**
	* Build the source for the two sides of a templates based on the structure extracted from its
	* original source
	*
	* @param  array    $branches
	* @return string[]
	*/
	protected static function buildPHP(array $branches)
	{
		$return = ['', ''];
		foreach ($branches as $branch)
		{
			$return[0] .= $branch['statement'] . '{' . $branch['head'];
			$return[1] .= $branch['statement'] . '{';

			if ($branch['branches'])
			{
				list($head, $tail) = self::buildPHP($branch['branches']);

				$return[0] .= $head;
				$return[1] .= $tail;
			}

			$return[0] .= '}';
			$return[1] .= $branch['tail'] . '}';
		}

		return $return;
	}

	/**
	* Get the unique values for the "passthrough" key of given branches
	*
	* @param  array     $branches
	* @return integer[]
	*/
	protected static function getBranchesPassthrough(array $branches)
	{
		$values = [];
		foreach ($branches as $branch)
		{
			$values[] = $branch['passthrough'];
		}

		// If the last branch isn't an "else", we act as if there was an additional branch with no
		// passthrough
		if ($branch['statement'] !== 'else')
		{
			$values[] = 0;
		}

		return array_unique($values);
	}

	/**
	* Get a string suitable as a preg_replace() replacement for given PHP code
	*
	* @param  string     $php Original code
	* @return array|bool      Array of [regexp, replacement] if possible, or FALSE otherwise
	*/
	protected static function getDynamicRendering($php)
	{
		$rendering = '';

		$literal   = "(?<literal>'((?>[^'\\\\]+|\\\\['\\\\])*)')";
		$attribute = "(?<attribute>htmlspecialchars\\(\\\$node->getAttribute\\('([^']+)'\\),2\\))";
		$value     = "(?<value>$literal|$attribute)";
		$output    = "(?<output>\\\$this->out\\.=$value(?:\\.(?&value))*;)";

		$copyOfAttribute = "(?<copyOfAttribute>if\\(\\\$node->hasAttribute\\('([^']+)'\\)\\)\\{\\\$this->out\\.=' \\g-1=\"'\\.htmlspecialchars\\(\\\$node->getAttribute\\('\\g-1'\\),2\\)\\.'\"';\\})";

		$regexp = '(^(' . $output . '|' . $copyOfAttribute . ')*$)';

		if (!preg_match($regexp, $php, $m))
		{
			return false;
		}

		// Attributes that are copied in the replacement
		$copiedAttributes = [];

		// Attributes whose value is used in the replacement
		$usedAttributes = [];

		$regexp = '(' . $output . '|' . $copyOfAttribute . ')A';
		$offset = 0;
		while (preg_match($regexp, $php, $m, 0, $offset))
		{
			// Test whether it's normal output or a copy of attribute
			if ($m['output'])
			{
				// 12 === strlen('$this->out.=')
				$offset += 12;

				while (preg_match('(' . $value . ')A', $php, $m, 0, $offset))
				{
					// Test whether it's a literal or an attribute value
					if ($m['literal'])
					{
						// Unescape the literal
						$str = stripslashes(substr($m[0], 1, -1));

						// Escape special characters
						$rendering .= preg_replace('([\\\\$](?=\\d))', '\\\\$0', $str);
					}
					else
					{
						$attrName = end($m);

						// Generate a unique ID for this attribute name, we'll use it as a
						// placeholder until we have the full list of captures and we can replace it
						// with the capture number
						if (!isset($usedAttributes[$attrName]))
						{
							$usedAttributes[$attrName] = uniqid($attrName, true);
						}

						$rendering .= $usedAttributes[$attrName];
					}

					// Skip the match plus the next . or ;
					$offset += 1 + strlen($m[0]);
				}
			}
			else
			{
				$attrName = end($m);

				if (!isset($copiedAttributes[$attrName]))
				{
					$copiedAttributes[$attrName] = uniqid($attrName, true);
				}

				$rendering .= $copiedAttributes[$attrName];
				$offset += strlen($m[0]);
			}
		}

		// Gather the names of the attributes used in the replacement either by copy or by value
		$attrNames = array_keys($copiedAttributes + $usedAttributes);

		// Sort them alphabetically
		sort($attrNames);

		// Keep a copy of the attribute names to be used in the fillter subpattern
		$remainingAttributes = array_combine($attrNames, $attrNames);

		// Prepare the final regexp
		$regexp = '(^[^ ]+';
		$index  = 0;
		foreach ($attrNames as $attrName)
		{
			// Add a subpattern that matches (and skips) any attribute definition that is not one of
			// the remaining attributes we're trying to match
			$regexp .= '(?> (?!' . RegexpBuilder::fromList($remainingAttributes) . '=)[^=]+="[^"]*")*';
			unset($remainingAttributes[$attrName]);

			$regexp .= '(';

			if (isset($copiedAttributes[$attrName]))
			{
				self::replacePlaceholder($rendering, $copiedAttributes[$attrName], ++$index);
			}
			else
			{
				$regexp .= '?>';
			}

			$regexp .= ' ' . $attrName . '="';

			if (isset($usedAttributes[$attrName]))
			{
				$regexp .= '(';

				self::replacePlaceholder($rendering, $usedAttributes[$attrName], ++$index);
			}

			$regexp .= '[^"]*';

			if (isset($usedAttributes[$attrName]))
			{
				$regexp .= ')';
			}

			$regexp .= '")?';
		}

		$regexp .= '.*)s';

		return [$regexp, $rendering];
	}

	/**
	* Get a string suitable as a str_replace() replacement for given PHP code
	*
	* @param  string      $php Original code
	* @return bool|string      Static replacement if possible, or FALSE otherwise
	*/
	protected static function getStaticRendering($php)
	{
		if ($php === '')
		{
			return '';
		}

		$regexp = "(^\\\$this->out\.='((?>[^'\\\\]+|\\\\['\\\\])*)';\$)";

		if (!preg_match($regexp, $php, $m))
		{
			return false;
		}

		return stripslashes($m[1]);
	}

	/**
	* Replace all instances of a uniqid with a PCRE replacement in a string
	*
	* @param  string  &$str    PCRE replacement
	* @param  string   $uniqid Unique ID
	* @param  integer  $index  Capture index
	* @return void
	*/
	protected static function replacePlaceholder(&$str, $uniqid, $index)
	{
		$str = preg_replace_callback(
			'(' . preg_quote($uniqid) . '(.))',
			function ($m) use ($index)
			{
				// Replace with $1 where unambiguous and ${1} otherwise
				if (is_numeric($m[1]))
				{
					return '${' . $index . '}' . $m[1];
				}
				else
				{
					return '$' . $index . $m[1];
				}
			},
			$str
		);
	}

	/**
	* Generate a series of conditionals
	*
	* @param  string $expr       Expression tested for equality
	* @param  array  $statements List of PHP statements
	* @return string
	*/
	public static function generateConditionals($expr, array $statements)
	{
		$keys = array_keys($statements);
		$cnt  = count($statements);
		$min  = (int) $keys[0];
		$max  = (int) $keys[$cnt - 1];

		if ($cnt <= 4)
		{
			if ($cnt === 1)
			{
				return end($statements);
			}

			$php = '';
			$k = $min;
			do
			{
				$php .= 'if(' . $expr . '===' . $k . '){' . $statements[$k] . '}else';
			}
			while (++$k < $max);

			$php .= '{' . $statements[$max] . '}';
			
			return $php;
		}

		$cutoff = ceil($cnt / 2);
		$chunks = array_chunk($statements, $cutoff, true);

		return 'if(' . $expr . '<' . key($chunks[1]) . '){' . self::generateConditionals($expr, array_slice($statements, 0, $cutoff, true)) . '}else' . self::generateConditionals($expr, array_slice($statements, $cutoff, null, true));
	}

	/**
	* Generate a branch table (with its source) for an array of PHP statements
	*
	* @param  string $expr       PHP expression used to determine the branch
	* @param  array  $statements Map of [value => statement]
	* @return array              Two elements: first is the branch table, second is the source
	*/
	public static function generateBranchTable($expr, array $statements)
	{
		// Map of [statement => id]
		$branchTable = [];

		// Map of [value => id]
		$branchIds = [];

		// Sort the PHP statements by the value used to identify their branch
		ksort($statements);

		foreach ($statements as $value => $statement)
		{
			if (!isset($branchIds[$statement]))
			{
				$branchIds[$statement] = count($branchIds);
			}

			$branchTable[$value] = $branchIds[$statement];
		}

		return [$branchTable, self::generateConditionals($expr, array_keys($branchIds))];
	}
}