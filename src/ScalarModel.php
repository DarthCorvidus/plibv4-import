<?php
/**
 * @copyright (c) 2021, Claus-Christoph Küthe
 * @author Claus-Christoph Küthe <floss@vm01.telton.de>
 * @license LGPL
 */

/**
 * Interface for a scalar import model
 * 
 * Scalar import models are supposed to import scalar values as well as to
 * validate and to convert them.
 */
Interface ScalarModel {
	/**
	 * Get default value, which will be used if value is not in array or empty.
	 */
	function getDefault(): string;
	
	/**
	 * True if model has a default, false if not.
	 */
	function hasDefault(): bool;
	
	/**
	 * True if value is mandatory, false if not.
	 */
	function isMandatory(): bool;
	
	/**
	 * True if model has a validator attached
	 */
	function hasValidate(): bool;
	
	/**
	 * Get validator if available. Won't be called if hasValidate equals FALSE.
	 */
	function getValidate(): Validate;
	
	/**
	 * True if model has converter attached
	 */
	function hasConvert(): bool;
	
	/**
	 * Get converter. Won't be called if hasConvert equals FALSE
	 */
	function getConvert(): Convert;
}