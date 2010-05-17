<?php

namespace Tests\Loader;

$prefix = __DIR__ . '/../../';
$cache = $prefix . (file_exists($prefix . 'Cache') ? 'Cache' : 'php-cache') . '/Source/';

require_once $cache . 'Cache.php';
require_once $cache . 'ICacheBackend.php';
require_once $cache . 'Backend/File.php';

use Loader\Loader;
use Cache\Cache;

class LoaderTest extends \PHPUnit_Framework_TestCase {
	
	public function setUp(){
		static $id;
		
		$this->cache = new Cache(__DIR__ . '/LoaderCacheTest/', array(
			'prefix' => 'Tests'
		));
		
		$this->loader = Loader::create(($id ? $id++ : ($id = 1)), $this->cache);
	}
	
	public function tearDown(){
		$this->loader->unregister();
		$this->cache->flush();
	}
	
	public function testLoader(){
		$this->loader->register()->add(__DIR__ . '/loader-test', 'LoaderTest');
		
		$a = new \LoaderTest\A;
		$this->assertEquals($a->who(), 'LoaderTest\\A');
		
		$this->assertFalse($this->loader->classExists('LoaderTest\\B'));
		$this->assertTrue($this->loader->classExists('LoaderTest\\Subspace\\B'));
		
		$d = new \LoaderTest\Subspace\D;
		$this->assertEquals($d->who(), 'LoaderTest\\Subspace\\D');
	}
	
	public function testUnregister(){
		$this->loader->add(__DIR__ . '/loader-test', 'LoaderTest')->unregister();
		
		// class_exists will attempt to load the class
		$this->assertFalse(class_exists('LoaderTest\\Subspace\\E'));
		
		$this->loader->register();
		$this->assertTrue(class_exists('LoaderTest\\Subspace\\E'));
		
		$e = new \LoaderTest\Subspace\E;
		$this->assertEquals($e->who(), 'LoaderTest\\Subspace\\E');
	}
	
	public function testAutoNamespace(){
		$this->loader->add(__DIR__ . '/test2')->unregister();
		$this->assertFalse($this->loader->classExists('A'));
		$this->assertTrue($this->loader->classExists('Test2\\A'));
		
		// class_exists will attempt to load the class
		$this->assertFalse(class_exists('Test2\\A'));
		
		$this->loader->register();
		$this->assertTrue(class_exists('Test2\\A'));
		
		$b = new \Test2\A;
		$this->assertEquals($b->who(), 'Test2\\A');
	}

}