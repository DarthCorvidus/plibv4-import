<?php
declare(strict_types=1);
/**
 * @copyright (c) 2025, Claus-Christoph Küthe
 * @author Claus-Christoph Küthe <floss@vm01.telton.de>
 * @license LGPL
 */
namespace plibv4\import;
use PHPUnit\Framework\TestCase;
use plibv4\convert\ConvertDate;
use plibv4\validate\ValidateDate;
use plibv4\validate\ValidateException;
use plibv4\uservalue\UserValue;
use plibv4\uservalue\MandatoryException;
use RuntimeException;
use OutOfBoundsException;
/**
 * Unit tests for ArrayFetch
 */
namespace plibv4\import\load;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class FileTest extends TestCase {
	function testLoadExistingFile(): void {
		$contents = File::fromPath(__DIR__."/hello.txt");
		$this->assertEquals("Hello World!", $contents);
	}

	function testLoadNonExistingFile(): void {
		$this->expectException(RuntimeException::class);
		File::fromPath(__DIR__."/hello.doc");
	}
}