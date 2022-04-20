<?php

declare(strict_types=1);

namespace Tobb10001\H4aIntegration\Models;

use ArrayAccess;
use Countable;
use IteratorAggregate;
use Traversable;

/**
 * Represents a set of games, along with it's metadata.
 * Oriented at the ["futureGames"]-Json.
 * @implements \ArrayAccess<int, Game>
 * @implements \IteratorAggregate<int, Game>
 */
class GameSchedule implements ArrayAccess, Countable, IteratorAggregate
{
    /** @var string $gClassID H4A's internal identification. */
    public string $gClassID;
    /** @var string $gClassSname Short representation of the class name. */
    public string $gClassSname;
    /** @var string $gClassLname Long representation of the class name. */
    public string $gClassLname;

    public string $gRefAllocType;
    public string $gRefRespOrg;

    /** @var array<Game> $games */
    public array $games;

    /**
     * @param array<mixed> $metadata
     * @param array<Game> $games
     */
    public function __construct(array $metadata, array $games)
    {
        $this->gClassID = $metadata["gClassID"];
        $this->gClassSname = $metadata["gClassSname"];
        $this->gClassLname = $metadata["gClassLname"];
        $this->gRefAllocType = $metadata["gRefAllocType"];
        $this->gRefRespOrg = $metadata["gRefRespOrg"];

        $this->games = $games;
    }

    /**
     * Create a GameSchedule-Object from JSON-Data.
     * @param array<mixed> $jsonAssoc
     * @return self
     */
    public static function fromJson(array $jsonAssoc): self
    {
        $games = array_map(
            function ($item) {
                return new Game($item);
            },
            $jsonAssoc["games"]
        );
        return new self($jsonAssoc, $games);
    }

    /* region Interface Array Access */
    /**
     * @codeCoverageIgnore
     */
    public function offsetSet($offset, $value): void
    {
        if (is_null($offset)) {
            $this->games[] = $value;
        } else {
            $this->games[$offset] = $value;
        }
    }

    /**
     * @codeCoverageIgnore
     */
    public function offsetExists($offset): bool
    {
        return isset($this->games[$offset]);
    }

    /**
     * @codeCoverageIgnore
     */
    public function offsetUnset($offset): void
    {
        unset($this->games[$offset]);
    }

    /**
     * @codeCoverageIgnore
     */
    public function offsetGet($offset)
    {
        return isset($this->games[$offset]) ? $this->games[$offset] : null;
    }
    /* endregion */

    /* region Interface Countable */
    /**
     * @codeCoverageIgnore
     */
    public function count(): int
    {
        return count($this->games);
    }
    /* endregion */

    /* region Interface IteratorAggregate */
    /**
     * @codeCoverageIgnore
     */
    public function getIterator(): Traversable
    {
        yield from $this->games;
    }
    /* endregion */
}
