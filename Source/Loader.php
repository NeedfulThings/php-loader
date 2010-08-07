<?php

namespace Loader;

require_once __DIR__ . '/ExtensionFilter.php';

class Loader {
	
	protected $cache;
	protected $options = array(
		'debug' => false,
	);
	protected $name;
	protected $classes = null;
	protected $namespaces = array();
	
	protected function __construct($name, \Cache\Cache $cache, $options = array()){
		$this->name = 'Loader/' . $name;
		$this->cache = $cache;

		$this->options = array_merge($this->options, $options);
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
	protected function getName($namespace, $file, $path){
		$directory = pathinfo($file, PATHINFO_DIRNAME);
		
		$name = array($namespace['name']);
		if ($directory != $path) $name[] = substr($directory, strlen($path) + 1);
		$name[] = basename($file, '.php');
		
		return strtolower(str_replace('\\\\', '\\', implode('\\', $name)));
	}
	
	protected function getClassList($namespace){
		$path = $namespace['path'];
		if (!is_dir($path)) return array();
		
		$length = strlen($path) + 1;
		$list = array();
		foreach (new ExtensionFilter(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path))) as $file){
			$realPath = $file->getRealPath();
			$list[$this->getName($namespace, $realPath, $path)] = $realPath;
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

			if (!$this->options['debug'])
				$this->cache->store($this->name, $classes);
		}
		
		$this->classes = $classes;
	}
	
	// Static
	public static function create($name, \Cache\Cache $cache, $options = array()){
		$class = static::getClassName();
		
		return new $class($name, $cache, $options);
	}
	
	protected static function getClassName(){
		return __CLASS__;
	}
	
}