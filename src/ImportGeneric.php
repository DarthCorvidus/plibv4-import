<?php
/**
 * @copyright (c) 2021, Claus-Christoph Küthe
 * @author Claus-Christoph Küthe <floss@vm01.telton.de>
 * @license LGPL
 */
class ImportGeneric implements ImportModel {
	/** @var array<string, UserValue> */
	private $scalarModels = array();
	/** @var array<string, ImportModel> */
	private $importModel = array();
	/** @var array<string, UserValue> */
	private array $scalarList = array();
	/** @var array<string, ImportModel> */
	private array $importList = array();
	function addScalar(string $name, UserValue $model): void {
		$this->scalarModels[$name] = $model;
	}
	
	public function getScalarModel($name): UserValue {
		return $this->scalarModels[$name];
	}

	public function getScalarNames():array {
		return array_keys($this->scalarModels);
	}

	public function addImportModel(string $name, ImportModel $import): void {
		$this->importModel[$name] = $import;
	}
	
	public function getImportModel($name): ImportModel {
		return $this->importModel[$name];
	}

	
	public function getImportNames(): array {
		return array_keys($this->importModel);
	}

	public function addScalarList(string $name, UserValue $model): void {
		$this->scalarList[$name] = $model;
	}
	
	public function getScalarListModel(string $name): UserValue {
		return $this->scalarList[$name];
	}
	
	/**
	 * @return list<string>
	 */
	public function getScalarListNames(): array {
		return array_keys($this->scalarList);
	}

	public function addImportList(string $name, ImportModel $model): void {
		$this->importList[$name] = $model;
	}
	
	public function getImportListModel($name): ImportModel {
		return $this->importList[$name];
	}

	public function getImportListNames(): array {
		return array_keys($this->importList);
	}

}