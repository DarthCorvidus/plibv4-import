<?php
/**
 * @copyright (c) 2021, Claus-Christoph Küthe
 * @author Claus-Christoph Küthe <floss@vm01.telton.de>
 * @license LGPL
 */
Interface ScalarModel {
	function getDefault(): string;
	function hasDefault(): bool;
	function isMandatory(): bool;
}