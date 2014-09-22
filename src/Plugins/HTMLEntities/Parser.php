<?php

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2014 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Plugins\HTMLEntities;

use s9e\TextFormatter\Plugins\ParserBase;

class Parser extends ParserBase
{
	/*
	* {@inheritdoc}
	*/
	public function parse($text, array $matches)
	{
		$tagName  = $this->config['tagName'];
		$attrName = $this->config['attrName'];

		foreach ($matches as $m)
		{
			$entity = $m[0][0];
			$chr    = \html_entity_decode($entity, \ENT_QUOTES, 'UTF-8');

			if ($chr === $entity)
				// The entity was not decoded, so we assume it's not valid and we ignore it
				continue;

			$this->parser->addSelfClosingTag($tagName, $m[0][1], \strlen($entity))->setAttribute($attrName, $chr);
		}
	}
}