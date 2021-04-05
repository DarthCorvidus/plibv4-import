<?php
/**
 * @copyright (c) 2021, Claus-Christoph Küthe
 * @author Claus-Christoph Küthe <floss@vm01.telton.de>
 * @license LGPL
 */

/**
 * Import
 * 
 * Uses an import model to import values from an array, considering default
 * values, mandatory values, validators and conversions.
 */
class Import {
	private $array = array();
	private $imported = array();
	private $model;
	private $path = array();
	/**
	 * Construct with the array you want to import from and an import model.
	 * @param array $array
	 * @param ImportModel $model
	 */
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
			if($value===NULL) {
				$niced[] = "[]";
				continue;
			}
			$niced[] = "[\"".$value."\"]";
		}
	return implode("", $niced);
	}
	
	
	private function checkUnexpected() {
		foreach($this->array as $key => $value) {
			if(!isset($this->imported[$key]) and is_scalar($value)) {
				throw new ImportException($this->getErrorPath($key)." with value '".$value."' is not expected in array");
			}
			if(!isset($this->imported[$key]) and is_array($value)) {
				throw new ImportException($this->getErrorPath($key)." is not expected in array");
			}

		}
	}
	
	private function noValue($key) {
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
	
	private function importLists() {
		foreach($this->model->getScalarListNames() as $name) {
			$scalarModel = $this->model->getScalarListModel($name);
			if($this->noValue($name) and $scalarModel->hasDefault()) {
				$this->imported[$name][] = $scalarModel->getDefault();
				continue;
			}
			if($this->noValue($name) and !$scalarModel->isMandatory()) {
				continue;
			}
			if($this->noValue($name) and $scalarModel->isMandatory()) {
				throw new ImportException($this->getErrorPath($name)."[] is mandatory, needs to contain at least one value");
			}
			$this->imported[$name] = $this->array[$name];
		}
		
	}

	private function importDictionaryList() {
		foreach($this->model->getImportListNames() as $name) {
			$mypath = $this->getPath();
			$mypath[] = $name;
			if($this->noValue($name)) {
				$mypath[] = NULL;
				$import = new Import(array(), $this->model->getImportListModel($name));
				$import->setPath($mypath);
				$array = $import->getArray();
				// If $import returned an empty array - ie all values are
				// optional and none was defaulted - skip value altogether.
				if(empty($array)) {
					continue;
				}
				$this->imported[$name][] = $array;
				continue;
			}

			
			foreach($this->array[$name] as $id => $sub) {
				$mypath[] = $id;
				$importModel = $this->model->getImportListModel($name);
				$import = new Import($sub, $importModel);
				$this->imported[$name][] = $import->getArray();
			}
		}
	}

	/**
	 * Get Array
	 * 
	 * Return array according to rules laid down in import model. It also checks
	 * for missing or unexpected values (values that do not exist in import
	 * model). Throws import exception if anything goes awry; will throw through
	 * Exceptions other than ValidateException, however.
	 * @return type
	 * @throws ImportException
	 */
	function getArray() {
		if($this->imported==array()) {
			$this->importScalars();
			$this->importLists();
			$this->validateScalars();
			$this->convertScalars();
			
			$this->importDictionaries();
			$this->importDictionaryList();
					
			$this->checkUnexpected();
		}
	return $this->imported;
	}
}