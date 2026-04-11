<?php
/**
 * @copyright (c) 2021, Claus-Christoph Küthe
 * @author Claus-Christoph Küthe <floss@vm01.telton.de>
 * @license LGPL
 */
namespace plibv4\import;
use plibv4\uservalue\UserValue;
use plibv4\uservalue\MandatoryException;
use plibv4\validate\ValidateException;
/**
 * Import
 * 
 * Uses an import model to import values from an array, considering default
 * values, mandatory values, validators and conversions.
 */
class Import {
	private ArrayFetch $fetch;
	private ImportModel $model;
	/** @var list<string> */
	private array $path = array();
	/** @var array<string, string> */
	private array $scalars = array();
	/** @var array<string, list<string>> */
	private array $scalarLists = array();
	/** @var array<string, list<Import>> */
	private array $dictionaryLists = array();
	/** @var array<string, Import> */
	private array $dictionaries = array();

	/**
	 * Construct with the array you want to import from and an import model.
	 * @param array $array
	 * @param ImportModel $model
	 * @param list<string> $path
	 */
	function __construct(array $array, ImportModel $model, array $path = array()) {
		$this->fetch = new ArrayFetch($array);
		$this->model = $model;
		$this->path = $path;
		$this->import();
	}

	#private function setPath(array $path): void {
	#	$this->path = $path;
	#}
	
	/**
	 * 
	 * @return list<string>
	 */
	private function getPath():array {
	return $this->path;
	}
	
	private function getErrorPath(string $name):string {
		$path = $this->path;
		$path[] = $name;
		$niced = array();
		foreach ($path as $value) {
			if($value==="") {
				$niced[] = "[]";
				continue;
			}
			$niced[] = "[\"".$value."\"]";
		}
	return implode("", $niced);
	}
	
	
	private function checkUnexpected(): void {
		/**
		 * Type of $value cannot be determined and does not need to be
		 * determined.
		 * @psalm-suppress MixedAssignment
		 */
		foreach($this->fetch->getKeys() as $name) {
			/**
			 * To satisfy psalm, but in the end, Import wants to have an array
			 * which is array<string, mixed> at the top level, but there is no
			 * way to guarantee that, so array<array-key, mixed> has to be used
			 * as type for Import.
			 */
			$name = (string)$name;
			if(!isset($this->scalars[$name]) and $this->fetch->isScalar($name)) {
				throw new ImportException("Unexpected scalar at key: ".$this->getErrorPath($name));
			}

			if(!isset($this->scalarLists[$name]) and !isset($this->dictionaryLists[$name]) and !isset($this->dictionaries[$name]) and $this->fetch->isArray($name)) {
				throw new ImportException("Unexpected array at key: ".$this->getErrorPath($name));
			}

		}
	}
	
	private function noValue(string $key): bool {
		if(!$this->fetch->hasKey($key)) {
			return true;
		}
		if($this->fetch->isScalar($key) && $this->fetch->asString($key)==="") {
			return true;
		}
		if($this->fetch->isArray($key) && $this->fetch->asArray($key)===array()) {
			return true;
		}
	return false;
	}
	
	private function importScalars(): void {
		foreach($this->model->getScalarNames() as $value) {
			try {
				$userValue = $this->model->getScalarModel($value);
				if($this->fetch->hasKey($value)) {
					$userValue->setValue($this->fetch->asString($value));
				}
				if($userValue->getValue()==="") {
					continue;
				}
				#$this->imported[$value] = $userValue->getValue();
				$this->scalars[$value] = $userValue->getValue();
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
				$import = new Import(array(), $this->model->getImportModel($name), $mypath);

				
				$array = $import->getArray();
				// If $import returned an empty array - ie all values are
				// optional and none was defaulted - skip value altogether.
				if(empty($array)) {
					continue;
				}
				$this->dictionaries[$name] = $import;
				continue;
			}
			$import = new Import($this->fetch->asArray($name), $this->model->getImportModel($name), $mypath);
			$this->dictionaries[$name] = $import;
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
			if(!$this->fetch->hasKey($name)) {
				$value = $userValue->getValue();
				if($value==="") {
					return;
				}
				#$this->imported[$name][] = $userValue->getValue();
				$this->scalarLists[$name][] = $userValue->getValue();
				return;
			}

			if(!$this->fetch->isArray($name)) {
				throw new ImportException($this->getErrorPath($name)." is not an array");
			}

			/**
			 * Type opf $value cannot be determined at this point.
			 * @psalm-suppress MixedAssignment
			 */
			foreach($this->fetch->asArray($name) as $value) {
				$userValue->setValue($value);
				#$this->imported[$name][] = $userValue->getValue();
				$this->scalarLists[$name][] = $userValue->getValue();
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
				$mypath[] = "";
				$import = new Import(array(), $this->model->getImportListModel($name), $mypath);
				$array = $import->getArray();
				// If $import returned an empty array - ie all values are
				// optional and none was defaulted - skip value altogether.
				if(empty($array)) {
					continue;
				}
				$this->dictionaryLists[$name][] = $import;
				continue;
			}

			if(!$this->fetch->isArray($name)) {
				throw new ImportException($this->getErrorPath($name)." is not an array");
			}
			/**
			 * Type opf $sub cannot be determined at this point.
			 * @psalm-suppress MixedAssignment
			 */
			foreach($this->fetch->asArray($name) as $id => $sub) {
				$keyName = (string)$id;
				$mypath[] = $keyName;
				if(!is_array($sub)) {
					throw new ImportException($this->getErrorPath($name)."[".$keyName."]: array expected");
				}
				$importModel = $this->model->getImportListModel($name);
				$import = new Import($sub, $importModel);
				$this->dictionaryLists[$name][] = $import;
			}
		}
	}

	private function import(): void {
		$this->importScalars();
		$this->importLists();
		
		$this->importDictionaries();
		$this->importDictionaryList();
				
		$this->checkUnexpected();
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
		/** @var array<array-key, mixed> */
		$array = array();
		$array = array_merge($array, $this->scalars);
		$array = array_merge($array, $this->scalarLists);
		foreach($this->dictionaryLists as $name => $dictList) {
			foreach($dictList as $import) {
				$array[$name][] = $import->getArray();
			}
		}

		foreach($this->dictionaries as $name => $import) {
			$array[$name] = $import->getArray();
		}
	return $array;
	}
}