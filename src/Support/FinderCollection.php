<?php

namespace InterNACHI\Modular\Support;

use Illuminate\Support\LazyCollection;
use Illuminate\Support\Traits\ForwardsCalls;
use Symfony\Component\Finder\Finder;

/**
 * @mixin \Illuminate\Support\LazyCollection
 * @mixin \Symfony\Component\Finder\Finder
 */
class FinderCollection
{
	use ForwardsCalls;
	
	/**
	 * @var \Symfony\Component\Finder\Finder
	 */
	protected $finder;
	
	/**
	 * @var \Illuminate\Support\LazyCollection
	 */
	protected $collection;
	
	public static function forFiles(): self
	{
		return (new static())->files();
	}
	
	public static function forDirectories(): self
	{
		return (new static())->directories();
	}
	
	public function __construct(Finder $finder = null)
	{
		$this->finder = $finder ?? new Finder();
		$this->collection = new LazyCollection();
	}
	
	public function __call($name, $arguments)
	{
		// Forward the call either to the Finder or the LazyCollection depending
		// on the method (always giving precedence to the Finder class)
		if (is_callable([$this->finder, $name])) {
			$result = $this->forwardCallTo($this->finder, $name, $arguments);
		} else {
			$this->collection->source = $this->finder;
			$result = $this->forwardCallTo($this->collection, $name, $arguments);
		}
		
		// If we get a Finder object back, update our internal reference and chain
		if ($result instanceof Finder) {
			$this->finder = $result;
			return $this;
		}
		
		// If we get a Collection object back, update our internal reference and chain
		if ($result instanceof LazyCollection) {
			$this->collection = $result;
			return $this;
		}
		
		// Otherwise, just return the new result (in the case of toBase() or sum()-type calls)
		return $result;
	}
}