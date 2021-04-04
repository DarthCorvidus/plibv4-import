<?php
/**
 * @copyright (c) 2021, Claus-Christoph Küthe
 * @author Claus-Christoph Küthe <floss@vm01.telton.de>
 * @license LGPL
 */
class Import {
	private $array = array();
	private $imported = array();
	private $model;
	private $path = array();
	function __construct(array $array, ImportModel $model) {
		$this->array = $array;
		$this->model = $model;
		$this->importScalars();
		$this->validateScalars();
		$this->convertScalars();
		$this->importDictionaries();
		$this->checkUnexpected();
	}
	
	private function addPath($element) {
		$this->path[] = $element;
	}
	
	private function getPath($key) {
		$path = $this->path;
		$path[] = "[\"".$key."\"]";
		return implode("", $path);
	}
	
	public function checkUnexpected() {
		foreach($this->array as $key => $value) {
			if(!isset($this->imported[$key]) and is_scalar($value)) {
				throw new ImportException($this->getPath($key)." with value '".$value."' is not expected in array");
			}
			if(!isset($this->imported[$key]) and is_array($value)) {
				throw new ImportException($this->getPath($key)." is not expected in array");
			}

		}
	}
	
	function noValue($key) {
		if(!isset($this->array[$key])) {
			return true;
		}
		if($this->array[$key]==="") {
			return true;
		}
		if($this->array[$key]===array()) {
			return true;
		}
	return false;
	}
	
	private function importScalars() {
		foreach($this->model->getScalarNames() as $value) {
			if($this->noValue($value) and $this->model->getScalarModel($value)->hasDefault()) {
				$this->imported[$value] = $this->model->getScalarModel($value)->getDefault();
				continue;
			}
			if($this->noValue($value) and $this->model->getScalarModel($value)->isMandatory()) {
				throw new ImportException("[\"".$value."\"] is missing from array");
			}
			if($this->noValue($value)) {
				continue;
			}
			$this->imported[$value] = $this->array[$value];
		}
	}
	
	private function validateScalars() {
		foreach($this->model->getScalarNames() as $key => $value) {
			if(!$this->model->getScalarModel($value)->hasValidate()) {
				continue;
			}
			try {
				$this->model->getScalarModel($value)->getValidate()->validate($this->imported[$value]);
			} catch(ValidateException $e) {
				throw new ImportException("Validation failed for [\"".$value."\"]: ".$e->getMessage());
			}
		}
	}

	private function convertScalars() {
		foreach($this->model->getScalarNames() as $key => $value) {
			if(!$this->model->getScalarModel($value)->hasConvert()) {
				continue;
			}
			$this->imported[$value] = $this->model->getScalarModel($value)->getConvert()->convert($this->imported[$value]);
		}
	}
	
	private function importDictionaries() {
		foreach($this->model->getImportNames() as $name) {
			if($this->noValue($name)) {
				$import = new Import(array(), $this->model->getImportModel($name));
				$array = $import->getArray();
				// If $import returned an empty array - ie all values are
				// optional and none was defaulted - skip value altogether.
				if(empty($array)) {
					continue;
				}
				$this->imported[$name] = $array;
				continue;
			}
			$import = new Import($this->array[$name], $this->model->getImportModel($name));
			$this->imported[$name] = $import->getArray();
		}
	}
	
	function getArray() {
		return $this->imported;
	}
}