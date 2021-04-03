<?php
/**
 * @copyright (c) 2021, Claus-Christoph Küthe
 * @author Claus-Christoph Küthe <floss@vm01.telton.de>
 * @license LGPL
 */
class ScalarGeneric implements ScalarModel {
	private $default;
	private $mandatory = FALSE;
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
	
}
