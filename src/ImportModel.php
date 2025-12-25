<?php
/**
 * @copyright (c) 2021, Claus-Christoph Küthe
 * @author Claus-Christoph Küthe <floss@vm01.telton.de>
 * @license LGPL
 */
namespace plibv4\import;
use plibv4\uservalue\UserValue;
/**
 * ImportModel
 * 
 * An ImportModel is a representation of an array, it basically tells Import
 * how it should import/validate/convert values from a given array.
 */
interface ImportModel {
	/**
	 * get scalar names
	 * 
	 * Return a list of names that should be imported as scalar values, whereas
	 * the name has to correspondent with an array key that contains a scalar
	 * value.
	 */
	function getScalarNames(): array;
	/**
	 * Get scalar model
	 * 
	 * Return the scalar model for a specific array key.
	 * @param string $name
	 */
	function getScalarModel(string $name): UserValue;
	
	/**
	 * Get scalar list names
	 * 
	 * Return a list of names that should be imported as an array containing
	 * scalar values.
	 * @return list<string> List of names
	 */
	function getScalarListNames(): array;
	
	/**
	 * Get Scalar List model
	 * 
	 * Return a scalar model to be applied to the list below $name.
	 * @param string $name
	 */
	function getScalarListModel(string $name): UserValue;
	
	/**
	 * Get Import Names
	 * 
	 * Return a list of names that should be imported as an associative array,
	 * basically an import model within an import model, to account for nested
	 * associative arrays such as $array["birth"]["location"] = "New York",
	 * $array["birth"]["time"] = "18:15:00".
	 * @return list<string>
	 */
	function getImportNames(): array;
	/**
	 * Get Import Model
	 * 
	 * Return an import model to be applied to the associative array below
	 * $name.
	 * @param string $name
	 */
	function getImportModel(string $name): ImportModel;
	
	/**
	 * Get Import List Names
	 * 
	 * Get a list of names that should be imported as a numeric array containing
	 * associative arrays.
	 * @return list<string>
	 */
	function getImportListNames(): array;
	/**
	 * Get Import List Model
	 * 
	 * Returns an import model which will be applied to each entry of a list
	 * below an array key $name.
	 * @param string $name
	 * @return ImportModel
	 */
	function getImportListModel(string $name): ImportModel;
}