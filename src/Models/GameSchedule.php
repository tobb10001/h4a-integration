<?php

declare(strict_types=1);

namespace Tobb10001\H4aIntegration\Models;

/**
 * Represents a set of games, along with it's metadata.
 * Oriented at the ["futureGames"]-Json.
 * @implements \ArrayAccess<int, Game>
 * @implements \IteratorAggregate<int, Game>
 */
class GameSchedule implements \ArrayAccess, \Countable, \IteratorAggregate
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
     * @param array<string, string> $metadata
     * @param array<Game> $games
     */
    public function __construct(array $metadata, array $games)
    {
        $this->gClassID = $metadata["gClassID"];
        $this->gClassSname = $metadata["gClassSname"];
        $this->gClassLname = $metadata["gClassLname"];
        $this->gRefAllocType = $metadata["gRefAllocType"];
        $this->gRefRespOrg = $metadata["gRefRepOrg"];

        $this->games = $games;
    }

    /* region Interface Array Access */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->games[] = $value;
        } else {
            $this->games[$offset] = $value;
        }
    }

    public function offsetExists($offset)
    {
        return isset($this->games[$offset]);
    }

    public function offsetUnset($offset)
    {
        unset($this->games[$offset]);
    }

    public function offsetGet($offset)
    {
        return isset($this->games[$offset]) ? $this->games[$offset] : null;
    }
    /* endregion */

    /* region Interface Countable */
    public function count()
    {
        return count($this->games);
    }
    /* endregion */

    /* region Interface IteratorAggregate */
    public function getIterator()
    {
        yield from $this->games;
    }
    /* endregion */
}
