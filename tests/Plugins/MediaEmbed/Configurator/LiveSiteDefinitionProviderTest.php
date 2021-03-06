<?php

namespace s9e\TextFormatter\Tests\Plugins\MediaEmbed\Configurator;

use s9e\TextFormatter\Plugins\MediaEmbed\Configurator\LiveSiteDefinitionProvider;
use s9e\TextFormatter\Tests\Test;

/**
* @covers s9e\TextFormatter\Plugins\MediaEmbed\Configurator\SiteDefinitionProvider
* @covers s9e\TextFormatter\Plugins\MediaEmbed\Configurator\LiveSiteDefinitionProvider
*/
class LiveSiteDefinitionProviderTest extends Test
{
	protected function generateDefinition()
	{
		$xml = "<site>
					<host>localhost</host>
					<host>127.0.0.1</host>
					<extract>!localhost/v/(?'id'\\d+)</extract>
					<iframe width='560' height='315' src='//localhost/e/{@id}'/>
				</site>";
		$siteId   = uniqid('mediaembed');
		$filepath = sys_get_temp_dir() . '/' . $siteId . '.xml';
		self::$tmpFiles[] = $filepath;
		file_put_contents($filepath, $xml);

		return $siteId;
	}

	/**
	* @testdox has() returns TRUE if the site config exists
	*/
	public function testHas()
	{
		$siteId   = $this->generateDefinition();
		$provider = new LiveSiteDefinitionProvider(sys_get_temp_dir());
		$this->assertTrue($provider->has($siteId));
	}

	/**
	* @testdox has('unknown') returns FALSE
	*/
	public function testHasFalse()
	{
		$siteId   = $this->generateDefinition();
		$provider = new LiveSiteDefinitionProvider(sys_get_temp_dir());
		$this->assertFalse($provider->has('unknown'));
	}

	/**
	* @testdox has('*invalid*') throws an exception
	* @expectedException InvalidArgumentException
	* @expectedExceptionMessage Invalid site ID
	*/
	public function testHasInvalid()
	{
		$provider = new LiveSiteDefinitionProvider(sys_get_temp_dir());
		$provider->has('*invalid*');
	}

	/**
	* @testdox getIds() returns a list of siteIds
	*/
	public function testGetIds()
	{
		$siteId   = $this->generateDefinition();
		$provider = new LiveSiteDefinitionProvider(sys_get_temp_dir());
		$siteIds  = $provider->getIds();
		$this->assertInternalType('array', $siteIds);
		$this->assertContains($siteId, $siteIds);
	}

	/**
	* @testdox get('foo') returns a configuration if foo.xml exists
	*/
	public function testGet()
	{
		$siteId     = $this->generateDefinition();
		$provider   = new LiveSiteDefinitionProvider(sys_get_temp_dir());
		$siteConfig = $provider->get($siteId);
		$this->assertInternalType('array', $siteConfig);
		$this->assertArrayHasKey('host', $siteConfig);
		$this->assertContains('localhost', $siteConfig['host']);
	}

	/**
	* @testdox get('unknown') throws an exception
	* @expectedException RuntimeException
	* @expectedExceptionMessage Unknown media site
	*/
	public function testGetUnknown()
	{
		$provider   = new LiveSiteDefinitionProvider(sys_get_temp_dir());
		$siteConfig = $provider->get('unknown');
	}

	/**
	* @testdox get('*invalid*') throws an exception
	* @expectedException InvalidArgumentException
	* @expectedExceptionMessage Invalid site ID
	*/
	public function testGetInvalid()
	{
		$provider   = new LiveSiteDefinitionProvider(sys_get_temp_dir());
		$siteConfig = $provider->get('*invalid*');
	}

	/**
	* @testdox The constructor throws an exception if the dir does not exist
	* @expectedException InvalidArgumentException
	* @expectedExceptionMessage Invalid site directory
	*/
	public function testPathInvalid()
	{
		new LiveSiteDefinitionProvider('/invalid/path');
	}

	/**
	* @testdox get() correctly interprets multiple nodes of the same name in XML
	*/
	public function testGetMultipleNodes()
	{
		$siteId     = $this->generateDefinition();
		$provider   = new LiveSiteDefinitionProvider(sys_get_temp_dir());
		$siteConfig = $provider->get($siteId);
		$this->assertInternalType('array', $siteConfig);
		$this->assertArrayHasKey('host', $siteConfig);
		$this->assertContains('localhost', $siteConfig['host']);
		$this->assertContains('127.0.0.1', $siteConfig['host']);
	}
}