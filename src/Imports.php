<?php
namespace plibv4\import;
class Imports {
	/**
	 * @var list<Import>
	 */
	private array $imports;
	function __construct() {
		$this->imports = array();
	}
	
	function addImport(Import $import): void {
		$this->imports[] = $import;
	}
	
	function getCount(): int {
		return count($this->imports);
	}
	
	function getImport(int $i): Import {
		return $this->imports[$i];
	}
}
