<?php declare(strict_types=1);

namespace Tobb10001\H4aIntegration\Models;

/**
 * Represents a table.
 * Oriented at the ["scores"]-Json.
 * @implements \ArrayAccess<int, TabScore>
 * @implements \IteratorAggregate<int, TabScore>
 */
class Table implements \ArrayAccess, \Countable, \IteratorAggregate {

	/** @var array<TabScore> $tabScores */
	public array $tabScores;

	/**
	 * @param array<TabScore> $tabScores
	 */
	function __construct(array $tabScores) {
		$this->tabScores = $tabScores;
	}

	/* region Interface IteratorAggregate */
	function getIterator() {
		yield from $this->tabScores;
	}
	/* endregion */

	/* region Interface Array Access */
	function offsetSet($offset, $value) {
		if (is_null($offset)) {
			$this->tabScores[] = $value;
		} else {
			$this->tabScores[$offset] = $value;
		}
	}

	function offsetExists($offset) {
		return isset($this->tabScores[$offset]);
	}

	function offsetUnset($offset) {
		unset($this->tabScores[$offset]);
	}

	function offsetGet($offset) {
		return isset($this->tabScores[$offset]) ? $this->tabScores[$offset] : null;
	}
	/* endregion */

	/* region Interface Countable */
	function count() {
		return count($this->tabScores);
	}
	/* endregion */
}
