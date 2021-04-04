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
	function __construct(array $array, ImportModel $model) {
		$this->array = $array;
		$this->model = $model;
		$this->importScalars();
		$this->validateScalars();
		$this->checkUnexpected();
	}
	
	public function checkUnexpected() {
		foreach($this->array as $key => $value) {
			if(!isset($this->imported[$key])) {
				throw new ImportException("[\"".$key."\"] with value '".$value."' is not expected in array");
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

	function getArray() {
		return $this->imported;
	}
}