<?php
/**
 * @copyright (c) 2021, Claus-Christoph Küthe
 * @author Claus-Christoph Küthe <floss@vm01.telton.de>
 * @license LGPL
 */
class ImportGeneric implements ImportModel {
	private $scalarModels = array();
	private $importModel = array();
	private $scalarList = array();
	private $importList = array();
	function addScalar($name, ScalarModel $model) {
		$this->scalarModels[$name] = $model;
	}
	
	public function getScalarModel($name): \ScalarModel {
		return $this->scalarModels[$name];
	}

	public function getScalarNames():array {
		return array_keys($this->scalarModels);
	}

	public function addImportModel($name, ImportModel $import) {
		$this->importModel[$name] = $import;
	}
	
	public function getImportModel($name): ImportModel {
		return $this->importModel[$name];
	}

	
	public function getImportNames(): array {
		return array_keys($this->importModel);
	}

	public function addScalarList($name, ScalarModel $model) {
		$this->scalarList[$name] = $model;
	}
	
	public function getScalarListModel($name): \ScalarModel {
		return $this->scalarList[$name];
	}

	public function getScalarListNames(): array {
		return array_keys($this->scalarList);
	}

	public function addImportList($name, ImportModel $model) {
		$this->importList[$name] = $model;
	}
	
	public function getImportListModel($name): ImportModel {
		return $this->importList[$name];
	}

	public function getImportListNames(): array {
		return array_keys($this->importList);
	}

}