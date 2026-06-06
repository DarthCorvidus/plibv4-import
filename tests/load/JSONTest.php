<?php
declare(strict_types=1);
/**
 * @copyright (c) 2025, Claus-Christoph Küthe
 * @author Claus-Christoph Küthe <floss@vm01.telton.de>
 * @license LGPL
 */
namespace plibv4\import\load;

use JsonException;
use PHPUnit\Framework\TestCase;
use plibv4\import\ArrayFetch;
use RuntimeException;

final class JSONTest extends TestCase {
	function testJsonFromString(): void {
		$expected = new ArrayFetch(["type" => "json"]);
	
		$json = new JSON('{"type": "json"}');
		$this->assertEquals($expected, $json->getArrayFetch());

		$this->assertEquals(["type" => "json"], $json->getArray());
	}

	function testInvalidJson(): void {
		$this->expectException(JsonException::class);
		new JSON('{"type": "json}');
	}

	function testJsonFromFile(): void {
		$json = JSON::FromFile(__DIR__."/hello.json");
		$this->assertEquals(["msg" => "Hello World!"], $json->getArray());
	}
}