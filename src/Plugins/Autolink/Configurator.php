<?php

/**
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2015 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Plugins\Autolink;

use s9e\TextFormatter\Configurator\Helpers\RegexpBuilder;
use s9e\TextFormatter\Plugins\ConfiguratorBase;

class Configurator extends ConfiguratorBase
{
	/**
	* @var string Name of attribute that stores the link's URL
	*/
	protected $attrName = 'url';

	/**
	* @var bool Whether to match strings that start with "www."
	*/
	protected $matchWww = false;

	/**
	* @var string Name of the tag used to represent links
	*/
	protected $tagName = 'URL';

	/**
	* Creates the tag used by this plugin
	*
	* @return void
	*/
	protected function setUp()
	{
		if (isset($this->configurator->tags[$this->tagName]))
		{
			return;
		}

		// Create a tag
		$tag = $this->configurator->tags->add($this->tagName);

		// Add an attribute using the default url filter
		$filter = $this->configurator->attributeFilters->get('#url');
		$tag->attributes->add($this->attrName)->filterChain->append($filter);

		// Set the default template
		$tag->template = '<a href="{@' . $this->attrName . '}"><xsl:apply-templates/></a>';
	}

	/**
	* {@inheritdoc}
	*/
	public function asConfig()
	{
		$anchor = RegexpBuilder::fromList($this->configurator->urlConfig->getAllowedSchemes()) . '://';
		if ($this->matchWww)
		{
			$anchor = '(?:' . $anchor . '|www\.)';
		}

		$config = [
			'attrName'   => $this->attrName,
			'regexp'     => '#' . $anchor . '\\S(?>[^\\s\\[\\]]*(?>\\[\\w*\\])?)++#iS',
			'tagName'    => $this->tagName
		];
		if (!$this->matchWww)
		{
			$config['quickMatch'] = '://';
		}

		return $config;
	}
}