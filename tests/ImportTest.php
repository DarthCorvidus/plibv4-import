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
	
	function testImportScalarMissing() {
		$array = array("name"=>"Maggie");
		$result = array("name"=>"Maggie");
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
		$import = new Import($array, $importGeneric);
		$this->expectException(ImportException::class);
		$this->expectExceptionMessage("[\"location\"] is missing from array");
		$import->getArray();
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
		
		$importGeneric = new Import($array, $importGeneric);
		$this->expectException(ImportException::class);
		#$this->expectExceptionMessage("Validate failed for [\"maxDuration\"]: ");
		$importGeneric->getArray();
	}
	function testValidateDefaulted() {
		$result = array("maxDuration"=>"08:00:00");

		$importGeneric = new ImportGeneric();
		$validate = new ScalarGeneric();
		$validate->setValidate(new ValidateTime());
		$validate->setDefault("08:00:00");
		$importGeneric->addScalar("maxDuration", $validate);
		
		$importGeneric = new Import(array(), $importGeneric);
		$this->assertEquals($importGeneric->getArray(), $result);
	}
	
	
	function testValidateDefaultedFail() {
		$importGeneric = new ImportGeneric();
		$validate = new ScalarGeneric();
		$validate->setValidate(new ValidateTime());
		$validate->setDefault("8h");
		$importGeneric->addScalar("maxDuration", $validate);
		
		$importGeneric = new Import(array(), $importGeneric);
		$this->expectException(ImportException::class);
		#$this->expectExceptionMessage("Validate failed for [\"maxDuration\"]: ");
		$importGeneric->getArray();
	}
	
	function testConvert() {
		$array = array("maxDuration"=>"01:00:00");
		$result = array("maxDuration"=>"3600");

		$importGeneric = new ImportGeneric();
		$convert = new ScalarGeneric();
		$convert->setConvert(new ConvertTime(ConvertTime::HMS, ConvertTime::SECONDS));
		$importGeneric->addScalar("maxDuration", $convert);
		$importGeneric = new Import($array, $importGeneric);
		$this->assertEquals($importGeneric->getArray(), $result);
		
	}

	function testImportDictionary() {
		$input["source"] = "/home/";
		$input["target"] = "/backup/";
		$input["retention"]["daily"] = "180";
		$input["retention"]["weekly"] = "52";
		$input["retention"]["monthly"] = "24";
		$input["retention"]["yearly"] = "10";
		
		
		$importRetention = new ImportGeneric();
		$importRetention->addScalar("daily", new ScalarGeneric());
		$importRetention->addScalar("weekly", new ScalarGeneric());
		$importRetention->addScalar("monthly", new ScalarGeneric());
		$importRetention->addScalar("yearly", new ScalarGeneric());
		
		$importModel = new ImportGeneric();
		$importModel->addScalar("source", new ScalarGeneric());
		$importModel->addScalar("target", new ScalarGeneric());
		$importModel->addImportModel("retention", $importRetention);
		
		$import = new Import($input, $importModel);
		$this->assertEquals($input, $import->getArray());
	}

	function testImportDictionaryMissing() {
		$input["source"] = "/home/";
		$input["target"] = "/backup/";
		
		$importRetention = new ImportGeneric();
		$importRetention->addScalar("daily", new ScalarGeneric());
		$importRetention->addScalar("weekly", new ScalarGeneric());
		$importRetention->addScalar("monthly", new ScalarGeneric());
		$importRetention->addScalar("yearly", new ScalarGeneric());
		
		$importModel = new ImportGeneric();
		$importModel->addScalar("source", new ScalarGeneric());
		$importModel->addScalar("target", new ScalarGeneric());
		$importModel->addImportModel("retention", $importRetention);
		
		$import = new Import($input, $importModel);
		$this->assertEquals($input, $import->getArray());
	}
	
	function testImportDictionaryMandatory() {
		$input["source"] = "/home/";
		$input["target"] = "/backup/";
		
		$importRetention = new ImportGeneric();
		$mandatory = new ScalarGeneric();
		$mandatory->setMandatory();
		$importRetention->addScalar("daily", $mandatory);
		$importRetention->addScalar("weekly", new ScalarGeneric());
		$importRetention->addScalar("monthly", new ScalarGeneric());
		$importRetention->addScalar("yearly", new ScalarGeneric());
		
		$importModel = new ImportGeneric();
		$importModel->addScalar("source", new ScalarGeneric());
		$importModel->addScalar("target", new ScalarGeneric());
		$importModel->addImportModel("retention", $importRetention);
		
		$import = new Import($input, $importModel);
		$this->expectException(ImportException::class);
		$this->expectExceptionMessage("[\"daily\"] is missing from array");
		$import->getArray();
	}
	
	function testImportDictionaryDefaulted() {
		$input["source"] = "/home/";
		$input["target"] = "/backup/";
		$result = $input;
		$result["retention"]["daily"] = 365;
		
		$importRetention = new ImportGeneric();
		$defaulted = new ScalarGeneric();
		$defaulted->setDefault("365");
		$importRetention->addScalar("daily", $defaulted);
		$importRetention->addScalar("weekly", new ScalarGeneric());
		$importRetention->addScalar("monthly", new ScalarGeneric());
		$importRetention->addScalar("yearly", new ScalarGeneric());
		
		$importModel = new ImportGeneric();
		$importModel->addScalar("source", new ScalarGeneric());
		$importModel->addScalar("target", new ScalarGeneric());
		$importModel->addImportModel("retention", $importRetention);
		
		$import = new Import($input, $importModel);
		$this->assertEquals($result, $import->getArray());
	}

	
	function testUnexpected() {
		$array = array("name"=>"Maggie", "species"=>"Magpie", "beak"=>"nice");
		$result = array("name"=>"Maggie", "species"=>"Magpie");
		$importGeneric = new ImportGeneric();
		$importGeneric->addScalar("name", new ScalarGeneric());
		$importGeneric->addScalar("species", new ScalarGeneric());
		
		$import = new Import($array, $importGeneric);
		$this->expectException(ImportException::class);
		$this->expectExceptionMessage("[\"beak\"] with value 'nice' is not expected in array");
		$import->getArray();
	}
}