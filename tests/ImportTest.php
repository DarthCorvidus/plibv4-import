<?php
declare(strict_types=1);
namespace plibv4\import;

use OutOfBoundsException;
use PHPUnit\Framework\TestCase;
use plibv4\uservalue\UserValue;
use plibv4\uservalue\MandatoryException;
use plibv4\validate\ValidateTime;
use plibv4\validate\ValidateDate;
use plibv4\validate\ValidateException;
use plibv4\convert\ConvertTime;
/**
 * @copyright (c) 2021, Claus-Christoph Küthe
 * @author Claus-Christoph Küthe <floss@vm01.telton.de>
 * @license LGPL
 */

/**
 * Unit tests for Import
 */
final class ImportTest extends TestCase {
	function testImportScalar(): void {
		$array = array("name"=>"Maggie", "species"=>"Magpie");
		$result = array("name"=>"Maggie", "species"=>"Magpie");
		$importGeneric = new ImportGeneric();
		$importGeneric->addScalar("name", UserValue::asMandatory());
		$importGeneric->addScalar("species", UserValue::asMandatory());
		$import = new Import($array, $importGeneric);
		$this->assertEquals($import->getArray(), $result);
		
		$this->assertSame(true, $import->hasString("name"));
		$this->assertSame("Maggie", $import->getString("name"));
	}

	function testImportScalarMissing(): void {
		$array = array("name"=>"Maggie", "species"=>"Magpie");
		$result = array("name"=>"Maggie", "species"=>"Magpie");
		$importGeneric = new ImportGeneric();
		$importGeneric->addScalar("name", UserValue::asMandatory());
		$importGeneric->addScalar("species", UserValue::asMandatory());

		$import = new Import($array, $importGeneric);
		$this->assertSame(false, $import->hasString("age"));
		$this->expectException(OutOfBoundsException::class);
		$this->expectExceptionMessage("[\"age\"]: key does not exist");
		
		$import->getString("age");
	}
	
	function testImportScalarEmpty(): void {
		$array = array("name"=>"Maggie");
		$result = array("name"=>"Maggie");
		$importGeneric = new ImportGeneric();
		$importGeneric->addScalar("name", UserValue::asMandatory());
		$importGeneric->addScalar("species", UserValue::asOptional());
		$import = new Import($array, $importGeneric);
		$this->assertEquals($array, $import->getArray());
	}
	

	function testImportScalarDefaulted(): void {
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

	function testMandatorySet(): void {
		$array = array("name"=>"Maggie", "species"=>"Magpie", "location"=>"Europe");
		$result = array("name"=>"Maggie", "species"=>"Magpie", "location"=>"Europe");
		$importGeneric = new ImportGeneric();
		$importGeneric->addScalar("name", UserValue::asMandatory());
		$importGeneric->addScalar("species", UserValue::asMandatory());
		$importGeneric->addScalar("location", UserValue::asMandatory());
		$import = new Import($array, $importGeneric);
		$this->assertEquals($import->getArray(), $result);
		
	}

	function testMandatoryMissing(): void {
		$array = array("name"=>"Maggie", "species"=>"Magpie");
		$importGeneric = new ImportGeneric();
		$importGeneric->addScalar("name", UserValue::asMandatory());
		$importGeneric->addScalar("species", UserValue::asMandatory());
		$importGeneric->addScalar("location", UserValue::asMandatory());

		$this->expectException(ImportException::class);
		$this->expectExceptionMessage("[\"location\"]: value is mandatory");
		new Import($array, $importGeneric);
	}

	function testValidate(): void {
		$array = array("maxDuration"=>"04:00:00");
		$result = array("maxDuration"=>"04:00:00");

		$importGeneric = new ImportGeneric();
		$validate = UserValue::asMandatory();
		$validate->setValidate(new ValidateTime());
		$importGeneric->addScalar("maxDuration", $validate);
		
		$import = new Import($array, $importGeneric);
		$this->assertEquals($import->getArray(), $result);
	}

	function testImportScalarValidateEmpty(): void {
		$array = array("name"=>"Maggie");
		
		$validatedScalar = UserValue::asOptional();
		$validatedScalar->setValidate(new ValidateDate(ValidateDate::ISO));
		
		$importGeneric = new ImportGeneric();
		$importGeneric->addScalar("name", UserValue::asMandatory());
		$importGeneric->addScalar("birthday", $validatedScalar);
		
		$import = new Import($array, $importGeneric);
		$this->assertEquals($array, $import->getArray());
	}

	function testValidateFail(): void {
		$array = array("maxDuration"=>"4h");

		$importGeneric = new ImportGeneric();
		$validate = UserValue::asMandatory();
		$validate->setValidate(new ValidateTime());
		$importGeneric->addScalar("maxDuration", $validate);

		$this->expectException(ImportException::class);
		#$this->expectExceptionMessage("Validate failed for [\"maxDuration\"]: ");
		new Import($array, $importGeneric);
	}
	
	function testValidateDefaulted(): void {
		$result = array("maxDuration"=>"08:00:00");

		$importGeneric = new ImportGeneric();
		$validate = UserValue::asMandatory();
		$validate->setValidate(new ValidateTime());
		$validate->setValue("08:00:00");
		$importGeneric->addScalar("maxDuration", $validate);
		
		$import = new Import(array(), $importGeneric);
		$this->assertEquals($import->getArray(), $result);
	}
	
	function testValidateDefaultedFail(): void {
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
	
	function testConvert(): void {
		$array = array("maxDuration"=>"01:00:00");
		$result = array("maxDuration"=>"3600");

		$importGeneric = new ImportGeneric();
		$convert = UserValue::asMandatory();
		$convert->setConvert(new ConvertTime(ConvertTime::HMS, ConvertTime::SECONDS));
		$importGeneric->addScalar("maxDuration", $convert);
		$import = new Import($array, $importGeneric);
		$this->assertEquals($import->getArray(), $result);
		
	}

	function testConvertEmpty(): void {
		$array = array("key"=>"value");

		$importGeneric = new ImportGeneric();
		$convert = UserValue::asOptional();
		$convert->setConvert(new ConvertTime(ConvertTime::HMS, ConvertTime::SECONDS));
		$importGeneric->addScalar("key", UserValue::asMandatory());
		$importGeneric->addScalar("maxDuration", $convert);
		
		$import = new Import($array, $importGeneric);
		$this->assertEquals($import->getArray(), $array);
		
	}

	function testImportDictionary(): void {
		$input = [];
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

		// get Import for retention
		$expected = new Import($input["retention"], $importRetention, array("retention"));
		$this->assertEquals(true, $import->hasImport("retention"));
		$this->assertEquals($expected, $import->getImport("retention"));
	}

	function testImportDictionaryOptional(): void {
		$input = [];
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
		
		// if retention is optional and has no values, there is no
		// Import.
		$this->assertEquals(false, $import->hasImport("retention"));
	}
	
	function testImportDictionaryMandatory(): void {
		$input = [];
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

		$this->expectException(ImportException::class);
		$this->expectExceptionMessage("[\"retention\"][\"daily\"]: value is mandatory");
		new Import($input, $importModel);
	}
	
	function testImportDictionaryDefaulted(): void {
		$input = [];
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

		/**
		 * If Import for retention is not or impartially set, but defaulted,
		 * import default values.
		 */
		$expected = new Import(array(), $importRetention, array("retention"));
		$this->assertEquals(true, $import->hasImport("retention"));
		$this->assertEquals($expected, $import->getImport("retention"));
	}

	function testScalarList(): void {
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

		$this->assertSame(true, $import->hasStringList("sports"));
		$this->assertSame($array["sports"], $import->getStringList("sports"));

	}

	function testScalarListScalarInsteadOfList(): void {
		$array = array();
		$array["scalar"] = "value";
		$array["sports"] = "soccer";

		$importGeneric = new ImportGeneric();
		$importGeneric->addScalar("scalar", UserValue::asMandatory());
		$importGeneric->addScalarList("sports", UserValue::asMandatory());

		$this->expectException(ImportException::class);
		$this->expectExceptionMessage("[\"sports\"] is not an array");
		new Import($array, $importGeneric);
	}

	
	function testScalarListDefaulted(): void {
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

		// fill with defaulted value
		$this->assertSame(true, $import->hasStringList("sports"));
		$this->assertSame($result["sports"], $import->getStringList("sports"));
	}
	
	function testScalarListMandatory(): void {
		$array = array();
		$array["scalar"] = "value";
		
		$scalarMandatory = UserValue::asMandatory();
		
		$importGeneric = new ImportGeneric();
		$importGeneric->addScalar("scalar", UserValue::asMandatory());
		$importGeneric->addScalarList("sports", $scalarMandatory);

		$this->expectException(ImportException::class);
		$this->expectExceptionMessage("[\"sports\"][] is mandatory, needs to contain at least one value");
		new Import($array, $importGeneric);
	}
	
	function testScalarListOptional(): void {
		$array = array();
		$array["scalar"] = "value";
		
		$scalarMandatory = UserValue::asOptional();
		
		$importGeneric = new ImportGeneric();
		$importGeneric->addScalar("scalar", UserValue::asMandatory());
		$importGeneric->addScalarList("sports", $scalarMandatory);
		
		$import = new Import($array, $importGeneric);
		$this->assertEquals($array, $import->getArray());

		// unfilled optional value is not there
		$this->assertSame(false, $import->hasStringList("sports"));
		$this->expectException(OutOfBoundsException::class);
		$this->expectExceptionMessage("[\"sports\"]: key does not exist" );
		$import->getStringList("sports");
	}

	function testScalarListValidatePass(): void {
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
	
	function testScalarListValidateFail(): void {
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

	function testScalarListConvert(): void {
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

	function testImportList(): void {
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
		$subImports = new Imports();
		/**
		 * @todo: Recheck pathing here. testImportListDefaulted() requires
		 * a path for the expected imports, testImportList doesn't, which
		 * is a bug in Import.
		 */
		$subImports->addImport(new Import($array["jobs"][0], $importJobs));
		$subImports->addImport(new Import($array["jobs"][1], $importJobs));

		$this->assertEquals($array, $import->getArray());
		$this->assertEquals($subImports, $import->getImportList("jobs"));
	}
	
	
	function testImportListOptional(): void {
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
		$this->assertSame(false, $import->hasImportList("jobs"));
		
	}
	
	function testImportListDefaulted(): void {
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

		$imports = new Imports();
		/**
		 * Create an Import with an empty array to match the behavior of $import above.
		 * Since $array["jobs"] doesn't exist, both will populate values from defaults.
		 * This allows us to verify that $import->getImportList("jobs") correctly
		 * returns an Imports object with the same defaulted values.
		 */
		$imports->addImport(new Import(array(), $importJobs, array("jobs", "")));

		$this->assertEquals($imports, $import->getImportList("jobs"));
	}
	
	function testImportListMandatory(): void {
		$array = array();
		$array["scalar"] = "value";

		$importJobs = new ImportGeneric();
		$importJobs->addScalar("source", UserValue::asMandatory());
		$importJobs->addScalar("target", UserValue::asMandatory());
		
		
		$importMain = new ImportGeneric();
		$importMain->addScalar("scalar", UserValue::asMandatory());
		$importMain->addImportList("jobs", $importJobs);

		$this->expectException(ImportException::class);
		$this->expectExceptionMessage("[\"jobs\"][][\"source\"]: value is mandatory");
		new Import($array, $importMain);
	}

	
	function testRecursion(): void {
		$array = [];
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
	
	
	function testRecursionError(): void {
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

		$this->expectException(ImportException::class);
		$this->expectExceptionMessage("[\"level1\"][\"level2\"][\"level3\"][\"scalar\"]: value is mandatory");
		$import = new Import($array, $importModel);
	}

	
	
	function testUnexpected(): void {
		$array = array("name"=>"Maggie", "species"=>"Magpie", "beak"=>"nice");
		$importGeneric = new ImportGeneric();
		$importGeneric->addScalar("name", UserValue::asMandatory());
		$importGeneric->addScalar("species", UserValue::asMandatory());

		$this->expectException(ImportException::class);
		$this->expectExceptionMessage("Unexpected scalar at key: [\"beak\"]");
		$import = new Import($array, $importGeneric);
	}
	
	function testUnexpectedArray(): void {
		$array = array("name"=>"Maggie", "species"=>"Magpie", "chicks"=>array("Pecky"));
		$importGeneric = new ImportGeneric();
		$importGeneric->addScalar("name", UserValue::asMandatory());
		$importGeneric->addScalar("species", UserValue::asMandatory());

		$this->expectException(ImportException::class);
		$this->expectExceptionMessage("Unexpected array at key: [\"chicks\"]");
		$import = new Import($array, $importGeneric);
	}
}