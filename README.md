PHP-Loader (PHP 5.3)
===================

Provides a class to automatically load classes given by nested directory structures. Does not need
any file system checks, should be fast (O(1)).

### Requirements

* [http://github.com/cpojer/php-cache](php-cache)

### Specs

* Run the "run" script in the Specs directory. Expects "php-cache" on the same level as "php-loader"

### Example
	
	use Cache\Cache;
	use Loader\Loader;
	
	$cache = new Cache(..);
	$loader = Loader::create('MainLoader', $cache)->add($pathTo, $namespace)->register();
	
	new \MyNamespace\MyClass; // Yay!
	
	$loader->classExists('MyNamespace\MyClass'); // true
	
	$loader->unregister();

See the Source or Specs for more