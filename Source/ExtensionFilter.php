<?php

namespace Loader;

class ExtensionFilter extends \FilterIterator {
	
	protected $iterator;
	protected $extension = array();
	
	public function __construct(\RecursiveIteratorIterator $iterator, $extension = null){
		$this->extension = (array)($extension ?: 'php');
		
		parent::__construct($iterator);
		$this->iterator = $iterator;
	}
	
	public function accept(){
		return !$this->iterator->isLink() &&
			($this->iterator->isDir() || in_array(strtolower(pathinfo($this->current(), PATHINFO_EXTENSION)), $this->extension));
	}
	
}