<?php

/**
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2015 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Plugins\MediaEmbed\Configurator;

use ArrayObject;
use s9e\TextFormatter\Configurator\ConfigProvider;
use s9e\TextFormatter\Configurator\JavaScript\Dictionary;

class MediaSiteCollection extends ArrayObject implements ConfigProvider
{
	/**
	* {@inheritdoc}
	*/
	public function asConfig()
	{
		$map = [];
		foreach ($this as $siteId => $siteConfig)
		{
			if (isset($siteConfig['host']))
			{
				foreach ((array) $siteConfig['host'] as $host)
				{
					$map[$host] = $siteId;
				}
			}

			if (isset($siteConfig['scheme']))
			{
				foreach ((array) $siteConfig['scheme'] as $scheme)
				{
					$map[$scheme . ':'] = $siteId;
				}
			}
		}

		return new Dictionary($map);
	}
}