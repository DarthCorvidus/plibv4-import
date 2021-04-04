<?php
declare(strict_types=1);
use PHPUnit\Framework\TestCase;
/**
 * @copyright (c) 2021, Claus-Christoph Küthe
 * @author Claus-Christoph Küthe <floss@vm01.telton.de>
 * @license LGPL
 */

/**
 * Unit tests for Import
 */
class ImportTest extends TestCase {
	function testImportScalar() {
		$array = array("name"=>"Maggie", "species"=>"Magpie");
		$result = array("name"=>"Maggie", "species"=>"Magpie");
		$importGeneric = new ImportGeneric();
		$importGeneric->addScalar("name", new ScalarGeneric());
		$importGeneric->addScalar("species", new ScalarGeneric());
		$import = new Import($array, $importGeneric);
		$this->assertEquals($import->getArray(), $result);
	}
	
	function testImportScalarDefaulted() {
		$array = array("name"=>"Maggie", "species"=>"Magpie");
		$result = array("name"=>"Maggie", "species"=>"Magpie", "location"=>"Europe");
		$importGeneric = new ImportGeneric();
		$importGeneric->addScalar("name", new ScalarGeneric());
		$importGeneric->addScalar("species", new ScalarGeneric());
		$location = new ScalarGeneric();
		$location->setDefault("Europe");
		$importGeneric->addScalar("location", $location);
		$import = new Import($array, $importGeneric);
		$this->assertEquals($import->getArray(), $result);
	}
	
	function testMandatorySet() {
		$array = array("name"=>"Maggie", "species"=>"Magpie", "location"=>"Europe");
		$result = array("name"=>"Maggie", "species"=>"Magpie", "location"=>"Europe");
		$importGeneric = new ImportGeneric();
		$importGeneric->addScalar("name", new ScalarGeneric());
		$importGeneric->addScalar("species", new ScalarGeneric());
		$location = new ScalarGeneric();
		$location->setMandatory();
		$importGeneric->addScalar("location", $location);
		$import = new Import($array, $importGeneric);
		$this->assertEquals($import->getArray(), $result);
		
	}

	function testMandatoryMissing() {
		$array = array("name"=>"Maggie", "species"=>"Magpie");
		$result = array("name"=>"Maggie", "species"=>"Magpie", "location"=>"Europe");
		$importGeneric = new ImportGeneric();
		$importGeneric->addScalar("name", new ScalarGeneric());
		$importGeneric->addScalar("species", new ScalarGeneric());
		$location = new ScalarGeneric();
		$location->setMandatory();
		$importGeneric->addScalar("location", $location);
		$this->expectException(ImportException::class);
		$this->expectExceptionMessage("[\"location\"] is missing from array");
		$import = new Import($array, $importGeneric);
	}
	
	function testValidate() {
		$array = array("maxDuration"=>"04:00:00");
		$result = array("maxDuration"=>"04:00:00");

		$importGeneric = new ImportGeneric();
		$validate = new ScalarGeneric();
		$validate->setValidate(new ValidateTime());
		$importGeneric->addScalar("maxDuration", $validate);
		
		$importGeneric = new Import($array, $importGeneric);
		$this->assertEquals($importGeneric->getArray(), $result);
	}
	
	function testValidateFail() {
		$array = array("maxDuration"=>"4h");
		$result = array("maxDuration"=>"04:00:00");

		$importGeneric = new ImportGeneric();
		$validate = new ScalarGeneric();
		$validate->setValidate(new ValidateTime());
		$importGeneric->addScalar("maxDuration", $validate);
		
		$this->expectException(ImportException::class);
		#$this->expectExceptionMessage("Validate failed for [\"maxDuration\"]: ");
		$importGeneric = new Import($array, $importGeneric);
	}
	
	function testUnexpected() {
		$array = array("name"=>"Maggie", "species"=>"Magpie", "beak"=>"nice");
		$result = array("name"=>"Maggie", "species"=>"Magpie");
		$importGeneric = new ImportGeneric();
		$importGeneric->addScalar("name", new ScalarGeneric());
		$importGeneric->addScalar("species", new ScalarGeneric());
		$this->expectException(ImportException::class);
		$this->expectExceptionMessage("[\"beak\"] with value 'nice' is not expected in array");
		$import = new Import($array, $importGeneric);
	}
}