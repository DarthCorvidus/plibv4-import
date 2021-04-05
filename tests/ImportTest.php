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
		$this->expectExceptionMessage("[\"retention\"][\"daily\"] is missing from array");
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

	function testScalarList() {
		$array = array();
		$array["scalar"] = "value";
		$array["sports"][] = "soccer";
		$array["sports"][] = "golf";
		$array["sports"][] = "marathon";

		$importGeneric = new ImportGeneric();
		$importGeneric->addScalar("scalar", new ScalarGeneric());
		$importGeneric->addScalarList("sports", new ScalarGeneric());
		
		$import = new Import($array, $importGeneric);
		$this->assertEquals($array, $import->getArray());
	}

	function testScalarListDefaulted() {
		$array = array();
		$array["scalar"] = "value";
		
		$result = $array;
		$result["sports"][] = "Dodgeball";

		$scalarDefaulted = new ScalarGeneric();
		$scalarDefaulted->setDefault("Dodgeball");
		
		$importGeneric = new ImportGeneric();
		$importGeneric->addScalar("scalar", new ScalarGeneric());
		$importGeneric->addScalarList("sports", $scalarDefaulted);
		
		$import = new Import($array, $importGeneric);
		$this->assertEquals($result, $import->getArray());
	}

	function testScalarListMandatory() {
		$array = array();
		$array["scalar"] = "value";
		
		$scalarMandatory = new ScalarGeneric();
		$scalarMandatory->setMandatory();
		
		$importGeneric = new ImportGeneric();
		$importGeneric->addScalar("scalar", new ScalarGeneric());
		$importGeneric->addScalarList("sports", $scalarMandatory);
		
		$import = new Import($array, $importGeneric);
		$this->expectException(ImportException::class);
		$this->expectExceptionMessage("[\"sports\"][] is mandatory, needs to contain at least one value");
		$import->getArray();
	}

	function testScalarListOptional() {
		$array = array();
		$array["scalar"] = "value";
		
		$scalarMandatory = new ScalarGeneric();
		
		$importGeneric = new ImportGeneric();
		$importGeneric->addScalar("scalar", new ScalarGeneric());
		$importGeneric->addScalarList("sports", $scalarMandatory);
		
		$import = new Import($array, $importGeneric);
		$this->assertEquals($array, $import->getArray());
	}

	function testImportList() {
		$array = array();
		$array["scalar"] = "value";
		$array["jobs"][0]["source"] = "/home/";
		$array["jobs"][0]["target"] = "/backup/home/";
		$array["jobs"][1]["source"] = "/data/";
		$array["jobs"][1]["target"] = "/backup/data/";
		
		$importJobs = new ImportGeneric();
		$importJobs->addScalar("source", new ScalarGeneric());
		$importJobs->addScalar("target", new ScalarGeneric());
		
		
		$importMain = new ImportGeneric();
		$importMain->addScalar("scalar", new ScalarGeneric());
		$importMain->addImportList("jobs", $importJobs);
		

		$import = new Import($array, $importMain);
		$this->assertEquals($array, $import->getArray());
	}
	
	function testImportListOptional() {
		$array = array();
		$array["scalar"] = "value";

		$importJobs = new ImportGeneric();
		$importJobs->addScalar("source", new ScalarGeneric());
		$importJobs->addScalar("target", new ScalarGeneric());
		
		
		$importMain = new ImportGeneric();
		$importMain->addScalar("scalar", new ScalarGeneric());
		$importMain->addImportList("jobs", $importJobs);
		
		$import = new Import($array, $importMain);
		$this->assertEquals($array, $import->getArray());
		
	}
	
	function testImportListDefaulted() {
		$array = array();
		$array["scalar"] = "value";

		$result = $array;
		$result["jobs"][0]["source"] = "/home/";
		$result["jobs"][0]["target"] = "/backup/";
		
		$defaulted = new ScalarGeneric();
		$defaulted->setDefault("/home/");
		$importJobs = new ImportGeneric();
		
		$importJobs->addScalar("source", $defaulted);
		
		$defaulted = new ScalarGeneric();
		$defaulted->setDefault("/backup/");
		$importJobs->addScalar("target", $defaulted);
		
		
		$importMain = new ImportGeneric();
		$importMain->addScalar("scalar", new ScalarGeneric());
		$importMain->addImportList("jobs", $importJobs);
		
		$import = new Import($array, $importMain);
		$this->assertEquals($result, $import->getArray());
	}

	function testImportListMandatory() {
		$array = array();
		$array["scalar"] = "value";

		$mandatory = new ScalarGeneric();
		$mandatory->setMandatory();

		$importJobs = new ImportGeneric();
		$importJobs->addScalar("source", $mandatory);
		$importJobs->addScalar("target", $mandatory);
		
		
		$importMain = new ImportGeneric();
		$importMain->addScalar("scalar", new ScalarGeneric());
		$importMain->addImportList("jobs", $importJobs);
		
		$import = new Import($array, $importMain);
		$this->expectException(ImportException::class);
		$this->expectExceptionMessage("[\"jobs\"][][\"source\"] is missing from array");
		$import->getArray();
	}

	
	function testRecursion() {
		$array["level1"]["level2"]["level3"]["scalar"] = "15";
		$importScalar = new ScalarGeneric();
		#$importScalar->setMandatory();
		
		
		$level3 = new ImportGeneric();
		$level3->addScalar("scalar", $importScalar);
		
		$level2 = new ImportGeneric();
		$level2->addImportModel("level3", $level3);
		
		$level1 = new ImportGeneric();
		$level1->addImportModel("level2", $level2);
		
		$importModel = new ImportGeneric();
		$importModel->addImportModel("level1", $level1);
		
		$import = new Import($array, $importModel);
		$this->assertEquals($array, $import->getArray());
	}
	
	function testRecursionError() {
		$array = array();
		$array["level1"]["level2"]["level3"] = array();
		
		$importScalar = new ScalarGeneric();
		$importScalar->setMandatory();
		
		
		$level3 = new ImportGeneric();
		$level3->addScalar("scalar", $importScalar);
		
		$level2 = new ImportGeneric();
		$level2->addImportModel("level3", $level3);
		
		$level1 = new ImportGeneric();
		$level1->addImportModel("level2", $level2);
		
		$importModel = new ImportGeneric();
		$importModel->addImportModel("level1", $level1);
		
		$import = new Import($array, $importModel);
		$this->expectException(ImportException::class);
		$this->expectExceptionMessage("[\"level1\"][\"level2\"][\"level3\"][\"scalar\"] is missing from array");
		$import->getArray();
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