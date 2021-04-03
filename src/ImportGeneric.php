<?php
/**
 * @copyright (c) 2021, Claus-Christoph Küthe
 * @author Claus-Christoph Küthe <floss@vm01.telton.de>
 * @license LGPL
 */
class ImportGeneric implements ImportModel {
	private $scalarModels = array();
	function addScalar($name, ScalarModel $model) {
		$this->scalarModels[$name] = $model;
	}
	
	public function getScalarModel($name): \ScalarModel {
		return $this->scalarModels[$name];
	}

	public function getScalarNames() {
		return array_keys($this->scalarModels);
	}

}