<?php
/**
 * @copyright (c) 2021, Claus-Christoph Küthe
 * @author Claus-Christoph Küthe <floss@vm01.telton.de>
 * @license LGPL
 */

/**
 * ScalarGeneric
 * 
 * Generic implementation for scalar model.
 */
class ScalarGeneric implements ScalarModel {
	private $default;
	private $mandatory = FALSE;
	private $validate;
	private $convert;
	public function setDefault(string $default) {
		$this->default = $default;
	}
	public function getDefault(): string {
		return $this->default;
	}

	public function hasDefault(): bool {
		return $this->default!==NULL;
	}

	public function setMandatory() {
		$this->mandatory = true;
	}

	public function isMandatory(): bool {
		return $this->mandatory;
	}
	
	public function setValidate(Validate $validate) {
		$this->validate = $validate;
	}

	public function hasValidate(): bool {
		return $this->validate!=NULL;
	}

	public function getValidate(): Validate {
		return $this->validate;
	}

	public function setConvert(Convert $convert) {
		$this->convert = $convert;
	}
	
	public function hasConvert(): bool {
		return $this->convert!=NULL;
	}
	
	
	public function getConvert(): Convert {
		return $this->convert;
	}

}
