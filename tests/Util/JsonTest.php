<?php

namespace Tobb10001\H4aIntegration\Util;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Tobb10001\H4aIntegration\Util\Json
 */
class JsonTest extends TestCase
{
    public function testIntOrNull()
    {
        $this->assertNull(Json::intOrNull(" ", " "));

        $this->assertEquals(27, Json::intOrNull("27"));

        $this->assertNull(Json::intOrNull("", [" ", ""]));
    }
}
