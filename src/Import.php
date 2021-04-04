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
	}
	
	private function setPath(array $path) {
		$this->path = $path;
	}
	
	private function getPath():array {
	return $this->path;
	}
	
	private function getErrorPath($name):string {
		$path = $this->path;
		$path[] = $name;
		$niced = array();
		foreach ($path as $value) {
			$niced[] = "[\"".$value."\"]";
		}
	return implode("", $niced);
	}
	
	
	public function checkUnexpected() {
		foreach($this->array as $key => $value) {
			if(!isset($this->imported[$key]) and is_scalar($value)) {
				throw new ImportException($this->getErrorPath($key)." with value '".$value."' is not expected in array");
			}
			if(!isset($this->imported[$key]) and is_array($value)) {
				throw new ImportException($this->getErrorPath($key)." is not expected in array");
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
				throw new ImportException($this->getErrorPath($value)." is missing from array");
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
				throw new ImportException("Validation failed for ".$this->getErrorPath($value).": ".$e->getMessage());
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
			$mypath = $this->getPath();
			$mypath[] = $name;
			if($this->noValue($name)) {
				$import = new Import(array(), $this->model->getImportModel($name));

				
				$import->setPath($mypath);
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
			$import->setPath($mypath);
			$this->imported[$name] = $import->getArray();
		}
	}
	
	function getArray() {
		if($this->imported==array()) {
			$this->importScalars();
			$this->validateScalars();
			$this->convertScalars();
			$this->importDictionaries();
			$this->checkUnexpected();
		}
	return $this->imported;
	}
}