<?php
/**
 * @copyright (c) 2025, Claus-Christoph Küthe
 * @author Claus-Christoph Küthe <floss@vm01.telton.de>
 * @license LGPL
 */
namespace plibv4\import\load;

use JsonException;
use plibv4\import\ArrayFetch;

/**
 * JSON
 *
 * Utility class for parsing and accessing JSON data with type-safe array access.
 * Wraps JSON parsing and provides convenient access to the parsed data through ArrayFetch.
 */
class JSON {
	private ArrayFetch $json;
	
	/**
	 * Construct from JSON string
	 *
	 * Parses a JSON string and wraps the result in an ArrayFetch for type-safe access.
	 *
	 * @param string $jsonString Valid JSON string to parse
	 * @throws JsonException If the JSON string is invalid or cannot be parsed
	 */
	function __construct(string $jsonString) {
		/** @var array<array-key, mixed> */
		$jsonArray = json_decode($jsonString, true, 512, JSON_THROW_ON_ERROR);
		$this->json = new ArrayFetch($jsonArray);
	}

	/**
	 * Create JSON instance from file
	 *
	 * Reads a JSON file from the given path and creates a JSON instance.
	 * Convenience method that combines file reading and JSON parsing.
	 *
	 * @param string $path Path to the JSON file
	 * @return JSON New JSON instance with parsed file contents
	 * @throws \RuntimeException If the file cannot be read
	 * @throws JsonException If the file content is not valid JSON
	 */
	static function fromFile(string $path): JSON {
		$jsonString = File::fromPath($path);
		return new JSON($jsonString);
	}

	/**
	 * Get ArrayFetch instance
	 *
	 * Returns the ArrayFetch wrapper for type-safe access to the parsed JSON data.
	 *
	 * @return ArrayFetch ArrayFetch instance containing the parsed JSON data
	 */
	function getArrayFetch(): ArrayFetch {
		return $this->json;
	}

	/**
	 * Get raw array
	 *
	 * Returns the parsed JSON data as a raw PHP array.
	 *
	 * @return array The parsed JSON data as an associative array
	 */
	function getArray(): array {
		return $this->json->getRawArray();
	}
}