<?php

declare(strict_types=1);

namespace Tobb10001\H4aIntegration\Models;

/**
 * Represents a table.
 * Oriented at the ["scores"]-Json.
 * @implements \ArrayAccess<int, TabScore>
 * @implements \IteratorAggregate<int, TabScore>
 */
class Table implements \ArrayAccess, \Countable, \IteratorAggregate
{
    /** @var array<TabScore> $tabScores */
    public array $tabScores;

    /**
     * @param array<TabScore> $tabScores
     */
    public function __construct(array $tabScores)
    {
        $this->tabScores = $tabScores;
    }

    /**
     * Construct a Table from JSON.
     * @param array<mixed> $jsonAssoc
     * @return self
     */
    public static function fromJson(array $jsonAssoc): self
    {
        $scores = array_map(
            function ($item) {
                return new TabScore($item);
            },
            $jsonAssoc
        );
        return new self($scores);
    }

    /* region Interface IteratorAggregate */
    public function getIterator()
    {
        yield from $this->tabScores;
    }
    /* endregion */

    /* region Interface Array Access */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->tabScores[] = $value;
        } else {
            $this->tabScores[$offset] = $value;
        }
    }

    public function offsetExists($offset)
    {
        return isset($this->tabScores[$offset]);
    }

    public function offsetUnset($offset)
    {
        unset($this->tabScores[$offset]);
    }

    public function offsetGet($offset)
    {
        return isset($this->tabScores[$offset]) ? $this->tabScores[$offset] : null;
    }
    /* endregion */

    /* region Interface Countable */
    public function count()
    {
        return count($this->tabScores);
    }
    /* endregion */
}
