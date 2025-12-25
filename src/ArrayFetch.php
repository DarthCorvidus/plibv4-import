<?php
/**
 * @copyright (c) 2025, Claus-Christoph Küthe
 * @author Claus-Christoph Küthe <floss@vm01.telton.de>
 * @license LGPL
 */
namespace plibv4\import;
use plibv4\uservalue\UserValue;
/**
 * ArrayFetch is a less complicated alternative to Import, which allows for
 * typed import of array values (great to smash down Psalm 'Mixed' findings).
 * Default values are ONLY applied if a key does not exist; existing keys always
 * have precedence, even values which PHP usually considers as empty. 
 */
class ArrayFetch {
	private array $array = [];
	function __construct(array $array) {
		$this->array = $array;
	}
	
	/**
	 * Set new array (so it must not be reconstructed when used in loops)
	 * @param array $array
	 * @return void
	 */
	function setArray(array $array): void {
		$this->array = $array;
	}
	
	/**
	 * Checks if a key exists (even if it is 'empty')
	 * @param string $key
	 * @return bool
	 */
	function hasKey(string $key): bool {
		return array_key_exists($key, $this->array);
	}
	
	private function assertKeyExists(string $key): void {
		if(!$this->hasKey($key)) {
			throw new \OutOfBoundsException("key $key not available in array");
		}
	}

	/**
	 * Import value from array as string, throw exception if impossible.
	 * @param string $key
	 * @param string|null $default
	 * @return string
	 * @throws \OutOfBoundsException
	 * @throws \RuntimeException
	 */
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
	
	/**
	 * Import value from array as integer, throw exception if impossible.
	 * @param string $key
	 * @param int|null $default
	 * @return int
	 * @throws \OutOfBoundsException
	 * @throws \RuntimeException
	 */
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
		if(!in_array($type, array("integer", "string"))) {
			throw new \RuntimeException("invalid type to import as int: ".$type);
		}
		
		if(filter_var($this->array[$key], FILTER_VALIDATE_INT) === false) {
			throw new \RuntimeException("unable to cast to integer");
		}
	return (int)$this->array[$key];
	}
	
	/**
	 * Import value from array as float, throw exception if impossible.
	 * @param string $key
	 * @param float|null $default
	 * @return float
	 * @throws \OutOfBoundsException
	 * @throws \RuntimeException
	 */
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

	/**
	 * Import value from array as array, works only on array.
	 * @param string $key
	 * @return array
	 * @throws \OutOfBoundsException
	 * @throws \RuntimeException
	 */
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
	
	/**
	 * Import value as boolean, works only on type bool, not on truthy/falsy
	 * values like 0, "0" or "". 
	 * @param string $key
	 * @param bool|null $default
	 * @return bool
	 * @throws \OutOfBoundsException
	 * @throws \RuntimeException
	 */
	public function asBool(string $key, ?bool $default = null): bool {
		if (!$this->hasKey($key)) {
			if ($default === null) {
				throw new \OutOfBoundsException("key $key not available in array");
			}
			return $default;
		}
		if(!is_bool($this->array[$key])) {
			throw new \RuntimeException("invalid type to import as bool: ".gettype($this->array[$key]));
		}
	return $this->array[$key];
	}
	
	/**
	 * Returns ArrayFetch if value is an array.
	 * @param string $key
	 * @return ArrayFetch
	 */
	function asArrayFetch(string $key): ArrayFetch {
		$array = $this->asArray($key);
	return new ArrayFetch($array);
	}

	/**
	 * Uses instance of UserValue to validate and convert array value, also
	 * applies mandatory rule/default value if applicable.
	 * @param string $key
	 * @param UserValue $userValue
	 * @return string
	 */
	function byUserValue(string $key, UserValue $userValue): string {
		$string = $this->asString($key, "");
		$userValue->setValue($string);
	return $userValue->getValue();
	}
	
	/**
	 * Checks if array value is of type null.
	 * @param string $key
	 * @return bool
	 */
	public function isNull(string $key): bool {
		$this->assertKeyExists($key);
		return $this->array[$key] === null;
	}
	
	/**
	 * Checks if array value is of type bool.
	 * @param string $key
	 * @return bool
	 */
	public function isBool(string $key): bool {
		$this->assertKeyExists($key);
	return is_bool($this->array[$key]);
	}
	
	/**
	 * Count internal array.
	 * @return int
	 */
	public function count(): int {
		return count($this->array);
	}
}
