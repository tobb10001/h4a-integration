<?php declare(strict_types=1);

namespace Tobb10001\H4aIntegration\Util;

/**
 * Helperfunctions to work with JSON-API-Responses.
 */
abstract class Json {
	/**
	 * Parses $input using intval(), if $input is not equal to / in $nullVals.
	 * Otherwise returns null.
	 *
	 * @param string $input
	 * @param array<string>|string $nullVal
	 */
	static function int_or_null(string $input, array|string $nullVal = [" "]): ?int {

		if (is_string($nullVal))
			$nullVal = [$nullVal];
		
		return in_array($input, $nullVal) ? null : intval($input);
	}
}
