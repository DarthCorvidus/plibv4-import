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
		$importGeneric->addScalar("name", new UserValue());
		$importGeneric->addScalar("species", new UserValue());
		$import = new Import($array, $importGeneric);
		$this->assertEquals($import->getArray(), $result);
	}
	
	function testImportScalarMissing() {
		$array = array("name"=>"Maggie");
		$result = array("name"=>"Maggie");
		$importGeneric = new ImportGeneric();
		$importGeneric->addScalar("name", new UserValue());
		$importGeneric->addScalar("species", new UserValue(FALSE));
		$import = new Import($array, $importGeneric);
		$this->assertEquals($array, $import->getArray());
	}
	

	function testImportScalarDefaulted() {
		$array = array("name"=>"Maggie", "species"=>"Magpie");
		$result = array("name"=>"Maggie", "species"=>"Magpie", "location"=>"Europe");
		$importGeneric = new ImportGeneric();
		$importGeneric->addScalar("name", new UserValue());
		$importGeneric->addScalar("species", new UserValue());
		$location = new UserValue();
		$location->setValue("Europe");
		$importGeneric->addScalar("location", $location);
		$import = new Import($array, $importGeneric);
		$this->assertEquals($import->getArray(), $result);
	}

	function testMandatorySet() {
		$array = array("name"=>"Maggie", "species"=>"Magpie", "location"=>"Europe");
		$result = array("name"=>"Maggie", "species"=>"Magpie", "location"=>"Europe");
		$importGeneric = new ImportGeneric();
		$importGeneric->addScalar("name", new UserValue());
		$importGeneric->addScalar("species", new UserValue());
		$importGeneric->addScalar("location", new UserValue());
		$import = new Import($array, $importGeneric);
		$this->assertEquals($import->getArray(), $result);
		
	}

	function testMandatoryMissing() {
		$array = array("name"=>"Maggie", "species"=>"Magpie");
		$result = array("name"=>"Maggie", "species"=>"Magpie", "location"=>"Europe");
		$importGeneric = new ImportGeneric();
		$importGeneric->addScalar("name", new UserValue());
		$importGeneric->addScalar("species", new UserValue());
		$importGeneric->addScalar("location", new UserValue());
		$import = new Import($array, $importGeneric);
		$this->expectException(ImportException::class);
		$this->expectExceptionMessage("[\"location\"]: value is mandatory");
		$import->getArray();
	}
	
	function testValidate() {
		$array = array("maxDuration"=>"04:00:00");
		$result = array("maxDuration"=>"04:00:00");

		$importGeneric = new ImportGeneric();
		$validate = new UserValue();
		$validate->setValidate(new ValidateTime());
		$importGeneric->addScalar("maxDuration", $validate);
		
		$importGeneric = new Import($array, $importGeneric);
		$this->assertEquals($importGeneric->getArray(), $result);
	}

	function testImportScalarValidateMissing() {
		$array = array("name"=>"Maggie");
		$result = array("name"=>"Maggie");
		
		$validatedScalar = new UserValue(FALSE);
		$validatedScalar->setValidate(new ValidateDate(ValidateDate::ISO));
		
		$importGeneric = new ImportGeneric();
		$importGeneric->addScalar("name", new UserValue());
		$importGeneric->addScalar("birthday", $validatedScalar);
		
		$import = new Import($array, $importGeneric);
		$this->assertEquals($array, $import->getArray());
	}

	function testValidateFail() {
		$array = array("maxDuration"=>"4h");
		$result = array("maxDuration"=>"04:00:00");

		$importGeneric = new ImportGeneric();
		$validate = new UserValue();
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
		$validate = new UserValue();
		$validate->setValidate(new ValidateTime());
		$validate->setValue("08:00:00");
		$importGeneric->addScalar("maxDuration", $validate);
		
		$importGeneric = new Import(array(), $importGeneric);
		$this->assertEquals($importGeneric->getArray(), $result);
	}
	
	function testValidateDefaultedFail() {
		$importGeneric = new ImportGeneric();
		$validate = new UserValue();
		$validate->setValidate(new ValidateTime());
		$this->expectException(ValidateException::class);
		$validate->setValue("8h");
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
		$convert = new UserValue();
		$convert->setConvert(new ConvertTime(ConvertTime::HMS, ConvertTime::SECONDS));
		$importGeneric->addScalar("maxDuration", $convert);
		$importGeneric = new Import($array, $importGeneric);
		$this->assertEquals($importGeneric->getArray(), $result);
		
	}

	function testConvertMissing() {
		$array = array("key"=>"value");

		$importGeneric = new ImportGeneric();
		$convert = new UserValue(FALSE);
		$convert->setConvert(new ConvertTime(ConvertTime::HMS, ConvertTime::SECONDS));
		$importGeneric->addScalar("key", new UserValue());
		$importGeneric->addScalar("maxDuration", $convert);
		$importGeneric = new Import($array, $importGeneric);
		$this->assertEquals($importGeneric->getArray(), $array);
		
	}

	function testImportDictionary() {
		$input["source"] = "/home/";
		$input["target"] = "/backup/";
		$input["retention"]["daily"] = "180";
		$input["retention"]["weekly"] = "52";
		$input["retention"]["monthly"] = "24";
		$input["retention"]["yearly"] = "10";
		
		
		$importRetention = new ImportGeneric();
		$importRetention->addScalar("daily", new UserValue());
		$importRetention->addScalar("weekly", new UserValue());
		$importRetention->addScalar("monthly", new UserValue());
		$importRetention->addScalar("yearly", new UserValue());
		
		$importModel = new ImportGeneric();
		$importModel->addScalar("source", new UserValue());
		$importModel->addScalar("target", new UserValue());
		$importModel->addImportModel("retention", $importRetention);
		
		$import = new Import($input, $importModel);
		$this->assertEquals($input, $import->getArray());
	}

	function testImportDictionaryOptional() {
		$input["source"] = "/home/";
		$input["target"] = "/backup/";
		
		$importRetention = new ImportGeneric();
		$importRetention->addScalar("daily", UserValue::optional());
		$importRetention->addScalar("weekly", UserValue::optional());
		$importRetention->addScalar("monthly", UserValue::optional());
		$importRetention->addScalar("yearly", UserValue::optional());
		
		$importModel = new ImportGeneric();
		$importModel->addScalar("source", new UserValue());
		$importModel->addScalar("target", new UserValue());
		$importModel->addImportModel("retention", $importRetention);
		
		$import = new Import($input, $importModel);
		$this->assertEquals($input, $import->getArray());
	}
	
	function testImportDictionaryMandatory() {
		$input["source"] = "/home/";
		$input["target"] = "/backup/";
		
		$importRetention = new ImportGeneric();
		$importRetention->addScalar("daily", UserValue::mandatory());
		$importRetention->addScalar("weekly", UserValue::optional());
		$importRetention->addScalar("monthly", UserValue::optional());
		$importRetention->addScalar("yearly", UserValue::optional());
		
		$importModel = new ImportGeneric();
		$importModel->addScalar("source", UserValue::mandatory());
		$importModel->addScalar("target", new UserValue());
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
		$defaulted = UserValue::optional();
		$defaulted->setValue("365");
		$importRetention->addScalar("daily", $defaulted);
		$importRetention->addScalar("weekly", UserValue::optional());
		$importRetention->addScalar("monthly", UserValue::optional());
		$importRetention->addScalar("yearly", UserValue::optional());
		
		$importModel = new ImportGeneric();
		$importModel->addScalar("source", new UserValue());
		$importModel->addScalar("target", new UserValue());
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
		$importGeneric->addScalar("scalar", new UserValue());
		$importGeneric->addScalarList("sports", new UserValue());
		
		$import = new Import($array, $importGeneric);
		$this->assertEquals($array, $import->getArray());
	}

	function testScalarListScalarInsteadOfList() {
		$array = array();
		$array["scalar"] = "value";
		$array["sports"] = "soccer";

		$importGeneric = new ImportGeneric();
		$importGeneric->addScalar("scalar", new UserValue());
		$importGeneric->addScalarList("sports", new UserValue());
		
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

		$scalarDefaulted = new UserValue();
		$scalarDefaulted->setValue("Dodgeball");
		
		$importGeneric = new ImportGeneric();
		$importGeneric->addScalar("scalar", new UserValue());
		$importGeneric->addScalarList("sports", $scalarDefaulted);
		
		$import = new Import($array, $importGeneric);
		$this->assertEquals($result, $import->getArray());
	}
	
	function testScalarListMandatory() {
		$array = array();
		$array["scalar"] = "value";
		
		$scalarMandatory = UserValue::mandatory();
		
		$importGeneric = new ImportGeneric();
		$importGeneric->addScalar("scalar", new UserValue());
		$importGeneric->addScalarList("sports", $scalarMandatory);
		
		$import = new Import($array, $importGeneric);
		$this->expectException(ImportException::class);
		$this->expectExceptionMessage("[\"sports\"][] is mandatory, needs to contain at least one value");
		$import->getArray();
	}
	
	function testScalarListOptional() {
		$array = array();
		$array["scalar"] = "value";
		
		$scalarMandatory = UserValue::optional();
		
		$importGeneric = new ImportGeneric();
		$importGeneric->addScalar("scalar", new UserValue());
		$importGeneric->addScalarList("sports", $scalarMandatory);
		
		$import = new Import($array, $importGeneric);
		$this->assertEquals($array, $import->getArray());
	}

	function testScalarListValidatePass() {
		$array = array();
		$array["scalar"] = "value";
		$array["time"][] = "08:00:00";
		
		$scalarMandatory = UserValue::mandatory();
		$scalarMandatory->setValidate(new ValidateTime());
		
		$importGeneric = new ImportGeneric();
		$importGeneric->addScalar("scalar", new UserValue());
		$importGeneric->addScalarList("time", $scalarMandatory);
		
		$import = new Import($array, $importGeneric);
		
		$this->assertEquals($array, $import->getArray());
	}
	
	function testScalarListValidateFail() {
		$array = array();
		$array["scalar"] = "value";
		$array["time"][] = "8h";
		
		$scalarMandatory = UserValue::mandatory();
		$scalarMandatory->setValidate(new ValidateTime());
		
		$importGeneric = new ImportGeneric();
		$importGeneric->addScalar("scalar", new UserValue());
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

		
		$scalarMandatory = UserValue::mandatory();
		$scalarMandatory->setValidate(new ValidateTime());
		$scalarMandatory->setConvert(new ConvertTime(ConvertTime::HMS, ConvertTime::SECONDS));
		
		$importGeneric = new ImportGeneric();
		$importGeneric->addScalar("scalar", new UserValue());
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
		$importJobs->addScalar("source", new UserValue());
		$importJobs->addScalar("target", new UserValue());
		
		
		$importMain = new ImportGeneric();
		$importMain->addScalar("scalar", new UserValue());
		$importMain->addImportList("jobs", $importJobs);
		

		$import = new Import($array, $importMain);
		$this->assertEquals($array, $import->getArray());
	}
	
	
	function testImportListOptional() {
		$array = array();
		$array["scalar"] = "value";

		$importJobs = new ImportGeneric();
		$importJobs->addScalar("source", UserValue::optional());
		$importJobs->addScalar("target", UserValue::optional());
		
		
		$importMain = new ImportGeneric();
		$importMain->addScalar("scalar", new UserValue());
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
		
		$defaulted = new UserValue();
		$defaulted->setValue("/home/");
		$importJobs = new ImportGeneric();
		
		$importJobs->addScalar("source", $defaulted);
		
		$defaulted = new UserValue();
		$defaulted->setValue("/backup/");
		$importJobs->addScalar("target", $defaulted);
		
		
		$importMain = new ImportGeneric();
		$importMain->addScalar("scalar", new UserValue());
		$importMain->addImportList("jobs", $importJobs);
		
		$import = new Import($array, $importMain);
		$this->assertEquals($result, $import->getArray());
	}
	
	function testImportListMandatory() {
		$array = array();
		$array["scalar"] = "value";

		$importJobs = new ImportGeneric();
		$importJobs->addScalar("source", UserValue::mandatory());
		$importJobs->addScalar("target", UserValue::mandatory());
		
		
		$importMain = new ImportGeneric();
		$importMain->addScalar("scalar", new UserValue());
		$importMain->addImportList("jobs", $importJobs);
		
		$import = new Import($array, $importMain);
		$this->expectException(ImportException::class);
		$this->expectExceptionMessage("[\"jobs\"][][\"source\"]: value is mandatory");
		$import->getArray();
	}

	
	function testRecursion() {
		$array["level1"]["level2"]["level3"]["scalar"] = "15";
		$importScalar = new UserValue();
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
		
		$importScalar = new UserValue();
		
		
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
		$result = array("name"=>"Maggie", "species"=>"Magpie");
		$importGeneric = new ImportGeneric();
		$importGeneric->addScalar("name", new UserValue());
		$importGeneric->addScalar("species", new UserValue());
		
		$import = new Import($array, $importGeneric);
		$this->expectException(ImportException::class);
		$this->expectExceptionMessage("[\"beak\"] with value 'nice' is not expected in array");
		$import->getArray();
	}
}