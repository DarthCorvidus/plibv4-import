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
		$importGeneric->addScalar("name", UserValue::asMandatory());
		$importGeneric->addScalar("species", UserValue::asMandatory());
		$import = new Import($array, $importGeneric);
		$this->assertEquals($import->getArray(), $result);
	}
	
	function testImportScalarEmpty() {
		$array = array("name"=>"Maggie");
		$result = array("name"=>"Maggie");
		$importGeneric = new ImportGeneric();
		$importGeneric->addScalar("name", UserValue::asMandatory());
		$importGeneric->addScalar("species", UserValue::asOptional());
		$import = new Import($array, $importGeneric);
		$this->assertEquals($array, $import->getArray());
	}
	

	function testImportScalarDefaulted() {
		$array = array("name"=>"Maggie", "species"=>"Magpie");
		$result = array("name"=>"Maggie", "species"=>"Magpie", "location"=>"Europe");
		$importGeneric = new ImportGeneric();
		$importGeneric->addScalar("name", UserValue::asMandatory());
		$importGeneric->addScalar("species", UserValue::asMandatory());
		$location = UserValue::asMandatory();
		$location->setValue("Europe");
		$importGeneric->addScalar("location", $location);
		$import = new Import($array, $importGeneric);
		$this->assertEquals($import->getArray(), $result);
	}

	function testMandatorySet() {
		$array = array("name"=>"Maggie", "species"=>"Magpie", "location"=>"Europe");
		$result = array("name"=>"Maggie", "species"=>"Magpie", "location"=>"Europe");
		$importGeneric = new ImportGeneric();
		$importGeneric->addScalar("name", UserValue::asMandatory());
		$importGeneric->addScalar("species", UserValue::asMandatory());
		$importGeneric->addScalar("location", UserValue::asMandatory());
		$import = new Import($array, $importGeneric);
		$this->assertEquals($import->getArray(), $result);
		
	}

	function testMandatoryEmpty() {
		$array = array("name"=>"Maggie", "species"=>"Magpie");
		$importGeneric = new ImportGeneric();
		$importGeneric->addScalar("name", UserValue::asMandatory());
		$importGeneric->addScalar("species", UserValue::asMandatory());
		$importGeneric->addScalar("location", UserValue::asMandatory());
		$import = new Import($array, $importGeneric);
		$this->expectException(ImportException::class);
		$this->expectExceptionMessage("[\"location\"]: value is mandatory");
		$import->getArray();
	}
	
	function testValidate() {
		$array = array("maxDuration"=>"04:00:00");
		$result = array("maxDuration"=>"04:00:00");

		$importGeneric = new ImportGeneric();
		$validate = UserValue::asMandatory();
		$validate->setValidate(new ValidateTime());
		$importGeneric->addScalar("maxDuration", $validate);
		
		$import = new Import($array, $importGeneric);
		$this->assertEquals($import->getArray(), $result);
	}

	function testImportScalarValidateEmpty() {
		$array = array("name"=>"Maggie");
		
		$validatedScalar = UserValue::asOptional();
		$validatedScalar->setValidate(new ValidateDate(ValidateDate::ISO));
		
		$importGeneric = new ImportGeneric();
		$importGeneric->addScalar("name", UserValue::asMandatory());
		$importGeneric->addScalar("birthday", $validatedScalar);
		
		$import = new Import($array, $importGeneric);
		$this->assertEquals($array, $import->getArray());
	}

	function testValidateFail() {
		$array = array("maxDuration"=>"4h");

		$importGeneric = new ImportGeneric();
		$validate = UserValue::asMandatory();
		$validate->setValidate(new ValidateTime());
		$importGeneric->addScalar("maxDuration", $validate);
		
		$import = new Import($array, $importGeneric);
		$this->expectException(ImportException::class);
		#$this->expectExceptionMessage("Validate failed for [\"maxDuration\"]: ");
		$import->getArray();
	}
	
	function testValidateDefaulted() {
		$result = array("maxDuration"=>"08:00:00");

		$importGeneric = new ImportGeneric();
		$validate = UserValue::asMandatory();
		$validate->setValidate(new ValidateTime());
		$validate->setValue("08:00:00");
		$importGeneric->addScalar("maxDuration", $validate);
		
		$import = new Import(array(), $importGeneric);
		$this->assertEquals($import->getArray(), $result);
	}
	
	function testValidateDefaultedFail() {
		$importGeneric = new ImportGeneric();
		$validate = UserValue::asMandatory();
		$validate->setValidate(new ValidateTime());
		$this->expectException(ValidateException::class);
		$validate->setValue("8h");
		$importGeneric->addScalar("maxDuration", $validate);
		
		$import = new Import(array(), $importGeneric);
		$this->expectException(ImportException::class);
		#$this->expectExceptionMessage("Validate failed for [\"maxDuration\"]: ");
		$import->getArray();
	}
	
	function testConvert() {
		$array = array("maxDuration"=>"01:00:00");
		$result = array("maxDuration"=>"3600");

		$importGeneric = new ImportGeneric();
		$convert = UserValue::asMandatory();
		$convert->setConvert(new ConvertTime(ConvertTime::HMS, ConvertTime::SECONDS));
		$importGeneric->addScalar("maxDuration", $convert);
		$import = new Import($array, $importGeneric);
		$this->assertEquals($import->getArray(), $result);
		
	}

	function testConvertEmpty() {
		$array = array("key"=>"value");

		$importGeneric = new ImportGeneric();
		$convert = UserValue::asOptional();
		$convert->setConvert(new ConvertTime(ConvertTime::HMS, ConvertTime::SECONDS));
		$importGeneric->addScalar("key", UserValue::asMandatory());
		$importGeneric->addScalar("maxDuration", $convert);
		
		$import = new Import($array, $importGeneric);
		$this->assertEquals($import->getArray(), $array);
		
	}

	function testImportDictionary() {
		$input["source"] = "/home/";
		$input["target"] = "/backup/";
		$input["retention"]["daily"] = "180";
		$input["retention"]["weekly"] = "52";
		$input["retention"]["monthly"] = "24";
		$input["retention"]["yearly"] = "10";
		
		
		$importRetention = new ImportGeneric();
		$importRetention->addScalar("daily", UserValue::asMandatory());
		$importRetention->addScalar("weekly", UserValue::asMandatory());
		$importRetention->addScalar("monthly", UserValue::asMandatory());
		$importRetention->addScalar("yearly", UserValue::asMandatory());
		
		$importModel = new ImportGeneric();
		$importModel->addScalar("source", UserValue::asMandatory());
		$importModel->addScalar("target", UserValue::asMandatory());
		$importModel->addImportModel("retention", $importRetention);
		
		$import = new Import($input, $importModel);
		$this->assertEquals($input, $import->getArray());
	}

	function testImportDictionaryOptional() {
		$input["source"] = "/home/";
		$input["target"] = "/backup/";
		
		$importRetention = new ImportGeneric();
		$importRetention->addScalar("daily", UserValue::asOptional());
		$importRetention->addScalar("weekly", UserValue::asOptional());
		$importRetention->addScalar("monthly", UserValue::asOptional());
		$importRetention->addScalar("yearly", UserValue::asOptional());
		
		$importModel = new ImportGeneric();
		$importModel->addScalar("source", UserValue::asMandatory());
		$importModel->addScalar("target", UserValue::asMandatory());
		$importModel->addImportModel("retention", $importRetention);
		
		$import = new Import($input, $importModel);
		$this->assertEquals($input, $import->getArray());
	}
	
	function testImportDictionaryMandatory() {
		$input["source"] = "/home/";
		$input["target"] = "/backup/";
		
		$importRetention = new ImportGeneric();
		$importRetention->addScalar("daily", UserValue::asMandatory());
		$importRetention->addScalar("weekly", UserValue::asOptional());
		$importRetention->addScalar("monthly", UserValue::asOptional());
		$importRetention->addScalar("yearly", UserValue::asOptional());
		
		$importModel = new ImportGeneric();
		$importModel->addScalar("source", UserValue::asMandatory());
		$importModel->addScalar("target", UserValue::asMandatory());
		$importModel->addImportModel("retention", $importRetention);
		
		$import = new Import($input, $importModel);
		$this->expectException(ImportException::class);
		$this->expectExceptionMessage("[\"retention\"][\"daily\"]: value is mandatory");
		$import->getArray();
	}
	
	function testImportDictionaryDefaulted() {
		$input["source"] = "/home/";
		$input["target"] = "/backup/";
		$result = $input;
		$result["retention"]["daily"] = 365;
		
		$importRetention = new ImportGeneric();
		$defaulted = UserValue::asOptional();
		$defaulted->setValue("365");
		$importRetention->addScalar("daily", $defaulted);
		$importRetention->addScalar("weekly", UserValue::asOptional());
		$importRetention->addScalar("monthly", UserValue::asOptional());
		$importRetention->addScalar("yearly", UserValue::asOptional());
		
		$importModel = new ImportGeneric();
		$importModel->addScalar("source", UserValue::asMandatory());
		$importModel->addScalar("target", UserValue::asMandatory());
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
		$importGeneric->addScalar("scalar", UserValue::asMandatory());
		$importGeneric->addScalarList("sports", UserValue::asMandatory());
		
		$import = new Import($array, $importGeneric);
		$this->assertEquals($array, $import->getArray());
	}

	function testScalarListScalarInsteadOfList() {
		$array = array();
		$array["scalar"] = "value";
		$array["sports"] = "soccer";

		$importGeneric = new ImportGeneric();
		$importGeneric->addScalar("scalar", UserValue::asMandatory());
		$importGeneric->addScalarList("sports", UserValue::asMandatory());
		
		$import = new Import($array, $importGeneric);
		$this->expectException(ImportException::class);
		$this->expectExceptionMessage("[\"sports\"] is not an array");
		$import->getArray();
	}

	
	function testScalarListDefaulted() {
		$array = array();
		$array["scalar"] = "value";
		
		$result = $array;
		$result["sports"][] = "Dodgeball";

		$scalarDefaulted = UserValue::asMandatory();
		$scalarDefaulted->setValue("Dodgeball");
		
		$importGeneric = new ImportGeneric();
		$importGeneric->addScalar("scalar", UserValue::asMandatory());
		$importGeneric->addScalarList("sports", $scalarDefaulted);
		
		$import = new Import($array, $importGeneric);
		$this->assertEquals($result, $import->getArray());
	}
	
	function testScalarListMandatory() {
		$array = array();
		$array["scalar"] = "value";
		
		$scalarMandatory = UserValue::asMandatory();
		
		$importGeneric = new ImportGeneric();
		$importGeneric->addScalar("scalar", UserValue::asMandatory());
		$importGeneric->addScalarList("sports", $scalarMandatory);
		
		$import = new Import($array, $importGeneric);
		$this->expectException(ImportException::class);
		$this->expectExceptionMessage("[\"sports\"][] is mandatory, needs to contain at least one value");
		$import->getArray();
	}
	
	function testScalarListOptional() {
		$array = array();
		$array["scalar"] = "value";
		
		$scalarMandatory = UserValue::asOptional();
		
		$importGeneric = new ImportGeneric();
		$importGeneric->addScalar("scalar", UserValue::asMandatory());
		$importGeneric->addScalarList("sports", $scalarMandatory);
		
		$import = new Import($array, $importGeneric);
		$this->assertEquals($array, $import->getArray());
	}

	function testScalarListValidatePass() {
		$array = array();
		$array["scalar"] = "value";
		$array["time"][] = "08:00:00";
		
		$scalarMandatory = UserValue::asMandatory();
		$scalarMandatory->setValidate(new ValidateTime());
		
		$importGeneric = new ImportGeneric();
		$importGeneric->addScalar("scalar", UserValue::asMandatory());
		$importGeneric->addScalarList("time", $scalarMandatory);
		
		$import = new Import($array, $importGeneric);
		
		$this->assertEquals($array, $import->getArray());
	}
	
	function testScalarListValidateFail() {
		$array = array();
		$array["scalar"] = "value";
		$array["time"][] = "8h";
		
		$scalarMandatory = UserValue::asMandatory();
		$scalarMandatory->setValidate(new ValidateTime());
		
		$importGeneric = new ImportGeneric();
		$importGeneric->addScalar("scalar", UserValue::asMandatory());
		$importGeneric->addScalarList("time", $scalarMandatory);
		
		$this->expectException(ImportException::class);
		$this->expectExceptionMessage("[\"time\"][]: invalid format, time expected (HH:MM:SS)");
		$import = new Import($array, $importGeneric);
		
		$this->assertEquals($array, $import->getArray());
	}

	function testScalarListConvert() {
		$array = array();
		$array["scalar"] = "value";
		$array["time"][] = "02:00:00";

		$expect = array();
		$expect["scalar"] = "value";
		$expect["time"][] = "7200";

		
		$scalarMandatory = UserValue::asMandatory();
		$scalarMandatory->setValidate(new ValidateTime());
		$scalarMandatory->setConvert(new ConvertTime(ConvertTime::HMS, ConvertTime::SECONDS));
		
		$importGeneric = new ImportGeneric();
		$importGeneric->addScalar("scalar", UserValue::asMandatory());
		$importGeneric->addScalarList("time", $scalarMandatory);
		
		$import = new Import($array, $importGeneric);
		
		$this->assertEquals($expect, $import->getArray());
	}

	function testImportList() {
		$array = array();
		$array["scalar"] = "value";
		$array["jobs"][0]["source"] = "/home/";
		$array["jobs"][0]["target"] = "/backup/home/";
		$array["jobs"][1]["source"] = "/data/";
		$array["jobs"][1]["target"] = "/backup/data/";
		
		$importJobs = new ImportGeneric();
		$importJobs->addScalar("source", UserValue::asMandatory());
		$importJobs->addScalar("target", UserValue::asMandatory());
		
		
		$importMain = new ImportGeneric();
		$importMain->addScalar("scalar", UserValue::asMandatory());
		$importMain->addImportList("jobs", $importJobs);
		

		$import = new Import($array, $importMain);
		$this->assertEquals($array, $import->getArray());
	}
	
	
	function testImportListOptional() {
		$array = array();
		$array["scalar"] = "value";

		$importJobs = new ImportGeneric();
		$importJobs->addScalar("source", UserValue::asOptional());
		$importJobs->addScalar("target", UserValue::asOptional());
		
		
		$importMain = new ImportGeneric();
		$importMain->addScalar("scalar", UserValue::asMandatory());
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
		
		$importJobs = new ImportGeneric();
		
		$source = UserValue::asMandatory();
		$source->setValue("/home/");
		
		
		$importJobs->addScalar("source", $source);
		
		$target = UserValue::asMandatory();
		$target->setValue("/backup/");
		$importJobs->addScalar("target", $target);
		
		
		$importMain = new ImportGeneric();
		$importMain->addScalar("scalar", UserValue::asMandatory());
		$importMain->addImportList("jobs", $importJobs);
		
		$import = new Import($array, $importMain);
		$this->assertEquals($result, $import->getArray());
	}
	
	function testImportListMandatory() {
		$array = array();
		$array["scalar"] = "value";

		$importJobs = new ImportGeneric();
		$importJobs->addScalar("source", UserValue::asMandatory());
		$importJobs->addScalar("target", UserValue::asMandatory());
		
		
		$importMain = new ImportGeneric();
		$importMain->addScalar("scalar", UserValue::asMandatory());
		$importMain->addImportList("jobs", $importJobs);
		
		$import = new Import($array, $importMain);
		$this->expectException(ImportException::class);
		$this->expectExceptionMessage("[\"jobs\"][][\"source\"]: value is mandatory");
		$import->getArray();
	}

	
	function testRecursion() {
		$array["level1"]["level2"]["level3"]["scalar"] = "15";
		$importScalar = UserValue::asMandatory();

		
		
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
		
		$importScalar = UserValue::asMandatory();
		
		
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
		$this->expectExceptionMessage("[\"level1\"][\"level2\"][\"level3\"][\"scalar\"]: value is mandatory");
		$import->getArray();
	}

	
	
	function testUnexpected() {
		$array = array("name"=>"Maggie", "species"=>"Magpie", "beak"=>"nice");
		$importGeneric = new ImportGeneric();
		$importGeneric->addScalar("name", UserValue::asMandatory());
		$importGeneric->addScalar("species", UserValue::asMandatory());
		
		$import = new Import($array, $importGeneric);
		$this->expectException(ImportException::class);
		$this->expectExceptionMessage("[\"beak\"] with value 'nice' is not expected in array");
		$import->getArray();
	}
}