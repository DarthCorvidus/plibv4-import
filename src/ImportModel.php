<?php
/**
 * @copyright (c) 2021, Claus-Christoph Küthe
 * @author Claus-Christoph Küthe <floss@vm01.telton.de>
 * @license LGPL
 */
interface ImportModel {
	function getScalarNames(): array;
	function getScalarModel($name): ScalarModel;
	function getScalarListNames(): array;
	function getScalarListModel($name): ScalarModel;
	function getImportNames(): array;
	function getImportModel($name): ImportModel;
	function getImportListNames(): array;
	function getImportListModel($name): ImportModel;
}