<?php

/**
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2015 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\RulesGenerators;

use s9e\TextFormatter\Configurator\Helpers\TemplateForensics;
use s9e\TextFormatter\Configurator\RulesGenerators\Interfaces\BooleanRulesGenerator;

class ManageParagraphs implements BooleanRulesGenerator
{
	/**
	* @var TemplateForensics
	*/
	protected $p;

	/**
	* Constructor
	*
	* Prepares the TemplateForensics for <p/>
	*
	* @return void
	*/
	public function __construct()
	{
		$this->p = new TemplateForensics('<p><xsl:apply-templates/></p>');
	}

	/**
	* {@inheritdoc}
	*/
	public function generateBooleanRules(TemplateForensics $src)
	{
		$rules = [];

		if ($src->allowsChild($this->p) && $src->isBlock() && !$this->p->closesParent($src))
		{
			$rules['createParagraphs'] = true;
		}

		if ($src->closesParent($this->p))
		{
			$rules['breakParagraph'] = true;
		}

		return $rules;
	}
}