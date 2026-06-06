<?php
/**
 * @copyright (c) 2025, Claus-Christoph Küthe
 * @author Claus-Christoph Küthe <floss@vm01.telton.de>
 * @license LGPL
 */
namespace plibv4\import\load;
use RuntimeException;

/**
 * File
 *
 * Utility class for loading file contents with proper error handling.
 */
class File {
	/**
	 * Load file contents from path
	 *
	 * Reads the entire file content from the given path and returns it as a string.
	 * Uses a custom error handler to convert PHP warnings/errors into RuntimeException.
	 *
	 * @param string $path Path to the file to read
	 * @return string The file contents
	 * @throws RuntimeException If the file cannot be read (e.g., file not found, permission denied)
	 */
	static function fromPath(string $path): string {
		set_error_handler(__CLASS__.'::errorHandler');
		try {
			/** @var string */
			$content = file_get_contents($path);
		} finally {
			restore_error_handler();
		}
	return $content;
	}

	/**
	 * Error handler for file operations
	 *
	 * Converts PHP errors/warnings into RuntimeException for better error handling.
	 * This is used internally by fromPath() to handle file_get_contents() errors.
	 *
	 * @param int $errno Error number
	 * @param string $errstr Error message
	 * @param string $errfile File where the error occurred
	 * @param int $errline Line number where the error occurred
	 * @return void
	 * @throws RuntimeException Always throws with the error message
	 */
	static function errorHandler(int $errno, string $errstr, string $errfile, int $errline): void {
		throw new RuntimeException($errstr);
	}
}