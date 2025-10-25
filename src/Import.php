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
	private array $array = array();
	private array $scalars = array();
	private array $imported = array();
	private ImportModel $model;
	/** @var list<string|null> */
	private array $path = array();
	/**
	 * Construct with the array you want to import from and an import model.
	 * @param array $array
	 * @param ImportModel $model
	 */
	function __construct(array $array, ImportModel $model) {
		$this->array = $array;
		$this->model = $model;
	}

	private function setPath(array $path): void {
		$this->path = $path;
	}
	
	private function getPath():array {
	return $this->path;
	}
	
	private function getErrorPath(string $name):string {
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
	
	
	private function checkUnexpected(): void {
		foreach($this->array as $key => $value) {
			if(!isset($this->imported[$key]) and is_scalar($value)) {
				throw new ImportException("Unexpected key ".$this->getErrorPath($key)." in array");
			}
			if(!isset($this->imported[$key]) and is_array($value)) {
				throw new ImportException($this->getErrorPath($key)." is not expected in array");
			}

		}
	}
	
	private function noValue(string $key): bool {
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
	
	private function importScalars(): void {
		foreach($this->model->getScalarNames() as $value) {
			try {
				$userValue = $this->model->getScalarModel($value);
				if(isset($this->array[$value])) {
					$userValue->setValue($this->array[$value]);
				}
				if($userValue->getValue()==="") {
					continue;
				}
				$this->imported[$value] = $userValue->getValue();
			} catch (MandatoryException $e) {
				throw new ImportException($this->getErrorPath($value).": ".$e->getMessage());
			} catch (ValidateException $e) {
				throw new ImportException($this->getErrorPath($value).": ".$e->getMessage());
			}
		}
	}
	
	private function importDictionaries(): void {
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
	
	private function importLists(): void {
		foreach($this->model->getScalarListNames() as $name) {
			$this->importList($name);
		}
	}
	
	private function importList(string $name): void {
		$userValue = $this->model->getScalarListModel($name);
		
		try {
			if(!isset($this->array[$name])) {
				$value = $userValue->getValue();
				if($value==="") {
					return;
				}
				$this->imported[$name][] = $userValue->getValue();
				return;
			}

			if(!is_array($this->array[$name])) {
				throw new ImportException($this->getErrorPath($name)." is not an array");
			}

			foreach($this->array[$name] as $value) {
				$userValue->setValue($value);
				$this->imported[$name][] = $userValue->getValue();
			}
		} catch (MandatoryException $e) {
			throw new ImportException($this->getErrorPath($name)."[] is mandatory, needs to contain at least one value");
		} catch (ValidateException $e ) {
			throw new ImportException($this->getErrorPath($name)."[]: ".$e->getMessage());
		}
	}

	private function importDictionaryList(): void {
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
	 * @return array<mixed, mixed>
	 * @throws ImportException
	 */
	function getArray(): array {
		if($this->imported===array()) {
			$this->importScalars();
			$this->importLists();
			
			$this->importDictionaries();
			$this->importDictionaryList();
					
			$this->checkUnexpected();
		}
	return $this->imported;
	}
}