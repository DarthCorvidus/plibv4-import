<?php
/**
 * @copyright (c) 2025, Claus-Christoph Küthe
 * @author Claus-Christoph Küthe <floss@vm01.telton.de>
 * @license LGPL
 */

/**
 * ArrayFetch is a less complicated alternative to Import. 
 */
class ArrayFetch {
	private array $array = [];
	function __construct(array $array) {
		$this->array = $array;
	}
	
	function hasKey(string $key): bool {
		return array_key_exists($key, $this->array);
	}
	
	function asString(string $key, ?string $default = null): string {
		if(!$this->hasKey($key)) {
		    if ($default === null) {
				throw new \OutOfBoundsException("key $key not available in array");
			}
			return $default;
		}
		if($this->array[$key] === null && $default === null) {
			throw new \RuntimeException("key ".$key." has type null");
		}

		$type = gettype($this->array[$key]);
		if(!in_array($type, array("float", "double", "integer", "string"))) {
			throw new \RuntimeException("invalid type to import as string: ".$type);
		}
	return (string)$this->array[$key];
	}
	
	function asInt(string $key, ?int $default = null): int {
		if (!$this->hasKey($key)) {
		    if ($default === null) {
				throw new \OutOfBoundsException("key $key not available in array");
			}
			return $default;
		}

		if($this->array[$key] === null && $default === null) {
			throw new \RuntimeException("key ".$key." has type null");
		}

		$type = gettype($this->array[$key]);
		if(!in_array($type, array("int", "string"))) {
			throw new \RuntimeException("invalid type to import as int: ".$type);
		}
		
		if(filter_var($this->array[$key], FILTER_VALIDATE_INT) === false) {
			throw new \RuntimeException("unable to cast to integer");
		}
	return (int)$this->array[$key];
	}
	
	function asFloat(string $key, ?float $default = null): float {
		if (!$this->hasKey($key)) {
			if ($default === null) {
				throw new \OutOfBoundsException("key $key not available in array");
			}
			return $default;
		}

		$value = $this->array[$key];

		if ($value === null && $default === null) {
			throw new \RuntimeException("key $key has type null");
		}

		if (!is_float($value) && !is_int($value) && !is_string($value)) {
			throw new \RuntimeException("invalid type to import as float: " . gettype($value));
		}

		if (!is_numeric($value)) {
			throw new \RuntimeException("unable to cast key $key value to float");
		}
    return (float)$value;
	}

	function asArray(string $key): array {
		if (!$this->hasKey($key)) {
			throw new \OutOfBoundsException("key $key not available in array");
		}

		$value = $this->array[$key];

		if ($value === null) {
			throw new \RuntimeException("key $key has type null");
		}

		if(!is_array($value)) {
			throw new \RuntimeException("key $key is not an array, but ".gettype($value));
		}
    return $value;
	}
	
	function asArrayFetch(string $key): ArrayFetch {
		$array = $this->asArray($key);
	return new ArrayFetch($array);
	}

	function byUserValue(string $key, UserValue $userValue): string {
		$string = $this->asString($key, "");
		$userValue->setValue($string);
	return $userValue->getValue();
	}
}
