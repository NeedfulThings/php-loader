<?php

namespace Loader;

require_once __DIR__ . '/ExtensionFilter.php';

class Loader {
	
	protected $cache;
	protected $name;
	protected $classes = null;
	protected $namespaces = array();
	
	protected function __construct($name, \Cache\Cache $cache){
		$this->name = 'Loader/' . $name;
		$this->cache = $cache;
	}
	
	// Autoloader
	public function register(){
		spl_autoload_register(array($this, 'autoload'));
		
		return $this;
	}
	
	public function unregister(){
		spl_autoload_unregister(array($this, 'autoload'));
		
		return $this;
	}
	
	public function autoload($class){
		if (!$this->classes) $this->load();
		
		$class = strtolower($class);
		if (empty($this->classes[$class])) return false;
		if (!class_exists($class, false)) require_once $this->classes[$class];
		
		return true;
	}
	
	// Exists
	public function classExists($class){
		if (!$this->classes) $this->load();
		
		$class = strtolower($class);
		return !empty($this->classes[$class]) || class_exists($class, false);
	}
	
	// Namespaces
	public function add($path, $namespace = null){
		$path = realpath($path);
		if (!$namespace) $namespace = basename($path);
		
		$this->namespaces[] = array(
			'name' => strtolower($namespace),
			'path' => $path
		);
		
		return $this;
	}
	
	// Classes
	protected function getClassList($namespace){
		$path = $namespace['path'];
		if (!is_dir($path)) return array();
		
		$length = strlen($path) + 1;
		$list = array();
		foreach (new ExtensionFilter(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path))) as $file){
			$realPath = $file->getRealPath();
			$info = pathinfo($realPath);
			
			$name = array($namespace['name']);
			if ($info['dirname'] != $path) $name[] = substr($info['dirname'], $length);
			$name[] = $info['filename'];
			
			$list[strtolower(implode('\\', $name))] = $realPath;
		}
		
		return $list;
	}
	
	// Magic
	protected function load(){
		$self = $this;
		$classes = $this->cache->retrieve($this->name);
		if (!$classes){
			$classes = array();
			foreach ($this->namespaces as $namespace)
				$classes = array_merge($classes, $this->getClassList($namespace));
			
			$this->cache->store($this->name, $classes);
		}
		
		$this->classes = $classes;
	}
	
	// Static
	public static function create($name, \Cache\Cache $cache){
		$class = static::getClassName();
		
		return new $class($name, $cache);
	}
	
	protected static function getClassName(){
		return __CLASS__;
	}
	
}