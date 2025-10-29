<?php
declare(strict_types=1);
use PHPUnit\Framework\TestCase;
/**
 * @copyright (c) 2025, Claus-Christoph Küthe
 * @author Claus-Christoph Küthe <floss@vm01.telton.de>
 * @license LGPL
 */

/**
 * Unit tests for ArrayFetch
 */
class ArrayFetchTest extends TestCase {
	static function getExample(): array {
		$example = [];
		$example["name"] = "Joe";
		$example["surname"] = "Doe";
		$example["height"] = "182";
		$example["gender"] = "m";
		$example["weight"] = 82.3;
		$example["pastime"] = array("gaming", "walking", "travelling");
		$example["born"] = "1970-07-03";
		$example["died"] = null;
		$example["empty"] = "";
		$career[0]["from"] = "2000-01-01";
		$career[0]["to"] = "2002-31-01";
		$career[0]["description"] = "Warp Drive Engineer";
		$career[1]["from"] = "2003-01-01";
		$career[1]["to"] = "2010-31-12";
		$career[1]["description"] = "Chief Engineer";
		$example["career"] = $career;
	return $example;
	}
	
	function testConstruct(): void {
		$example = self::getExample();
		$access = new ArrayFetch($example);
		$this->assertInstanceOf(ArrayFetch::class, $access);
	}
	
	/**
	 * @dataProvider hasKeyProvider 
	 * @return void
	 */
	function testHasKey(array $example, bool $expected): void {
		$fetch = new ArrayFetch($example);
		$this->assertSame($expected, $fetch->hasKey("exists"));
	}
	
	function hasKeyProvider(): array {
		$test = [];
		$test[] = array(array("exists" => "yes"), true);
		$test[] = array(array("exists" => true), true);
		$test[] = array(array("exists" => null), true);
		$test[] = array(array("exists" => ""), true);
		$test[] = array(array("exists" => 0), true);
		$test[] = array(array(), false);
	return $test;
	}

	/**
	 * @dataProvider asBoolProvider 
	 * @return void
	 */
	public function testBool(array $example, bool $expected): void {
		$fetch = new ArrayFetch($example);
		$this->assertSame($expected, $fetch->asBool("boolean"));
	}
	
	public function asBoolProvider(): array {
		$test = [];
		$test[] = array(array("boolean" => true), true);
		$test[] = array(array("boolean" => false), false);
	return $test;
	}
	
	/**
	 * @dataProvider StringProvider
	 */
	function testString(array $example, string $expected): void {
		$fetch = new ArrayFetch($example);
		$this->assertSame($expected, $fetch->asString("key"));
	}
	
	function StringProvider(): array {
		$test = [];
		$test[] = array(array("key" => "Dog"), "Dog");
		$test[] = array(array("key" => "15"), "15");
		$test[] = array(array("key" => 15), "15");
		$test[] = array(array("key" => 15.0), "15");
		$test[] = array(array("key" => 15.03), "15.03");
	return $test;
	}
	
	function testDefaultedString() {
		$example = self::getExample();
		$fetch = new ArrayFetch($example);
		$this->assertSame("alive", $fetch->asString("state", "alive"));
	}
	
	function testStringMissingKey() {
		$example = self::getExample();
		$fetch = new ArrayFetch($example);
		$this->expectException(OutOfBoundsException::class);
		$this->assertSame("nationality", $fetch->asString("nationality"));
	}
	
	function testStringNull() {
		$example = self::getExample();
		$fetch = new ArrayFetch($example);
		$this->expectException(RuntimeException::class);
		$this->assertSame("died", $fetch->asString("died"));
	}

	function testStringArray() {
		$example = self::getExample();
		$fetch = new ArrayFetch($example);
		$this->expectException(RuntimeException::class);
		$fetch->asString("pastime");
	}

	/**
	 * @dataProvider intProvider
	 */
	function testInt($example, $expected) {
		$fetch = new ArrayFetch($example);
		$this->assertSame($expected, $fetch->asInt("key"));
	}

	function IntProvider(): array {
		$test = [];
		$test[] = array(array("key" => "15"), 15);
		$test[] = array(array("key" => 15), 15);
		$test[] = array(array("key" => 0xF), 15);
	return $test;
	}
	
	function testDefaultedInt() {
		$example = self::getExample();
		$fetch = new ArrayFetch($example);
		$this->assertSame(90, $fetch->asInt("whateva", 90));
	}

	function testIntMissingKey() {
		$example = self::getExample();
		$fetch = new ArrayFetch($example);
		$this->expectException(OutOfBoundsException::class);
		$this->assertSame("nationality", $fetch->asInt("nationality"));
	}
	
	function testIntNull() {
		$example = self::getExample();
		$fetch = new ArrayFetch($example);
		$this->expectException(RuntimeException::class);
		$this->assertSame("died", $fetch->asInt("died"));
	}

	function testIntArray() {
		$example = self::getExample();
		$fetch = new ArrayFetch($example);
		$this->expectException(RuntimeException::class);
		$fetch->asInt("pastime");
	}

	function testIntBogus() {
		$example = self::getExample();
		$fetch = new ArrayFetch($example);
		$this->expectException(RuntimeException::class);
		$fetch->asInt("name");
	}
	/**
	 * 
	 * @dataProvider FloatProvider
	 */
	function testFloat($example, $expected) {
		$fetch = new ArrayFetch($example);
		$this->assertSame($expected, $fetch->asFloat("key"));
	}
	
	function FloatProvider(): array {
		$test = [];
		$test[] = array(array("key" => "15.03"), 15.03);
		$test[] = array(array("key" => 15), 15.0);
	return $test;
	}

	function testDefaultedFloat(): void {
		$example = self::getExample();
		$fetch = new ArrayFetch($example);
		$this->assertSame(99.9, $fetch->asFloat("unknown", 99.9));
	}

	function testFloatMissingKey(): void {
		$example = self::getExample();
		$fetch = new ArrayFetch($example);
		$this->expectException(OutOfBoundsException::class);
		$fetch->asFloat("nationality");
	}

	function testFloatNull(): void {
		$example = self::getExample();
		$fetch = new ArrayFetch($example);
		$this->expectException(RuntimeException::class);
		$fetch->asFloat("died");
	}

	function testFloatArray(): void {
		$example = self::getExample();
		$fetch = new ArrayFetch($example);
		$this->expectException(RuntimeException::class);
		$fetch->asFloat("pastime");
	}

	function testFloatBogus(): void {
		$example = self::getExample();
		$fetch = new ArrayFetch($example);
		$this->expectException(RuntimeException::class);
		$fetch->asFloat("name");
	}
	
	/**
	 * 
	 * @dataProvider ArrayProvider
	 */
	function testArray($example, $expected) {
		$fetch = new ArrayFetch($example);
		$this->assertSame($expected, $fetch->asArray("key"));
	}
	
	function ArrayProvider(): array {
		$test = [];
		$test[] = array(array("key" => [1,2,3]), [1,2,3]);
	return $test;
	}
	
	function testArrayNotSet(): void {
		$example = self::getExample();
		$fetch = new ArrayFetch($example);
		$this->expectException(OutOfBoundsException::class);
		$fetch->asArray("passtime");
	}
	
	function testArrayBogus(): void {
		$example = self::getExample();
		$fetch = new ArrayFetch($example);
		$this->expectException(RuntimeException::class);
		$fetch->asArray("height");
	}
	
	function testArrayFetch(): void {
		$example = self::getExample();
		$fetch = new ArrayFetch($example);
		$fetchPastime = new ArrayFetch($example["pastime"]);
		$fetchCareer = new ArrayFetch($example["career"]);
		$this->assertEquals($fetchPastime, $fetch->asArrayFetch("pastime"));
		$this->assertEquals($fetchCareer, $fetch->asArrayFetch("career"));
	}
	
	function testByUserValue(): void {
		$date = UserValue::asMandatory();
		$date->setValidate(new ValidateDate(ValidateDate::ISO));
		$date->setConvert(new ConvertDate(ConvertDate::ISO, ConvertDate::GERMAN));
		$example = self::getExample();
		$fetch = new ArrayFetch($example);
		$this->assertEquals("03.07.1970", $fetch->byUserValue("born", $date));
	}
	
	function testByUserValueBogus(): void {
		$date = UserValue::asMandatory();
		$date->setValidate(new ValidateDate(ValidateDate::ISO));
		$date->setConvert(new ConvertDate(ConvertDate::ISO, ConvertDate::GERMAN));
		$example = self::getExample();
		$fetch = new ArrayFetch($example);
		$this->expectException(ValidateException::class);
		$this->assertEquals("03.07.1970", $fetch->byUserValue("name", $date));
	}

	function testByUserValueMandatory(): void {
		$date = UserValue::asMandatory();
		$date->setValidate(new ValidateDate(ValidateDate::ISO));
		$date->setConvert(new ConvertDate(ConvertDate::ISO, ConvertDate::GERMAN));
		$example = self::getExample();
		$fetch = new ArrayFetch($example);
		$this->expectException(MandatoryException::class);
		$this->assertEquals("03.07.1970", $fetch->byUserValue("empty", $date));
	}

	function testByUserValueMandatoryDefaulted(): void {
		$date = UserValue::asMandatory();
		$date->setDefault("1970-07-03");
		$date->setValidate(new ValidateDate(ValidateDate::ISO));
		$date->setConvert(new ConvertDate(ConvertDate::ISO, ConvertDate::GERMAN));
		$example = self::getExample();
		$fetch = new ArrayFetch($example);
		$this->expectException(MandatoryException::class);
		$this->assertEquals("03.07.1970", $fetch->byUserValue("empty", $date));
	}

	/**
	 * @dataProvider isNullProvider
	 * @return void
	 */
	function testIsNull(array $example, bool $expected): void {
		$fetch = new ArrayFetch($example);
		$this->assertSame($expected, $fetch->isNull("exists"));
	}
	
	function isNullProvider(): array {
		$test = [];
		$test[] = array(array("exists" => null), true);
		$test[] = array(array("exists" => ""), false);
		$test[] = array(array("exists" => 0), false);
	return $test;
	}

	function testIsNullNoKey(): void {
		$fetch = new ArrayFetch(array());
		$this->expectException(\OutOfBoundsException::class);
		$fetch->isNull("madeup");
	}
	
	/**
	 * @dataProvider isBoolProvider
	 * @return void
	 */
	function testIsBool(array $example, bool $expected): void {
		$fetch = new ArrayFetch($example);
		$this->assertSame($expected, $fetch->isBool("exists"));
	}
	
	function isBoolProvider(): array {
		$test = [];
		$test[] = array(array("exists" => null), false);
		$test[] = array(array("exists" => ""), false);
		$test[] = array(array("exists" => 0), false);
		$test[] = array(array("exists" => 1), false);
		$test[] = array(array("exists" => true), true);
		$test[] = array(array("exists" => false), true);
	return $test;
	}
	
	function testIsBoolNoKey(): void {
		$fetch = new ArrayFetch(array());
		$this->expectException(\OutOfBoundsException::class);
		$fetch->isBool("madeup");
	}

	/**
	 * @dataProvider countProvider
	 * @return void
	 */
	function testCount($example, $expected): void {
		$fetch = new ArrayFetch($example);
		$this->assertSame($expected, $fetch->count());
	}
	
	function countProvider(): array {
		$test = [];
		$test[] = array(array("one" => 1, "two" => 2, "three" => 3), 3);
		$test[] = array(array(), 0);
		$test[] = array(array(1, 2, 3), 3);
	return $test;
	}
	
	function testSetArray(): void {
		$fetch = new ArrayFetch([0, 1, 2, 3]);
		$this->assertSame(2, $fetch->asInt("2"));
		$fetch->setArray([4, 5, 6, 7]);
		$this->assertSame(6, $fetch->asInt("2"));
	}
}