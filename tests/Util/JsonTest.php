<?php

namespace Tobb10001\H4aIntegration\Util;

use PHPUnit\Framework\TestCase;

use Tobb10001\H4aIntegration\Exceptions\InvalidUrlException;
use Tobb10001\H4aIntegration\Exceptions\UnsuccessfulRequestException;

/**
 * @covers \Tobb10001\H4aIntegration\Util\Json
 */
class JsonTest extends TestCase {

	function test_int_or_null() {

		$this->assertNull(Json::int_or_null(" ", " "));

		$this->assertEquals(27, Json::int_or_null("27"));

		$this->assertNull(Json::int_or_null("", [" ", ""]));
	}
}
