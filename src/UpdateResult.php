<?php

declare(strict_types=1);

namespace Tobb10001\H4aIntegration;

use ArrayAccess;
use Countable;
use IteratorAggregate;
use Traversable;

use Tobb10001\H4aIntegration\Util\UpdateResultlet;

/**
 * Summarize the activity of an update process.
 * Contains a mapping of teams, that were tried to update and their
 * corresponding UpdateResultlets. These UpdateResultlets contain information
 * about which updates were tried to run, whether they were successful or not
 * and information about errors.
 *
 * @implements \ArrayAccess<int, UpdateResultlet>
 * @implements \IteratorAggregate<int, UpdateResultlet>
 */
class UpdateResult implements ArrayAccess, Countable, IteratorAggregate
{
    /**
     * @var array<int, UpdateResultlet> $map
     * Contains the mapping: teamId -> UpdatResultlet
     */
    private array $map = [];

    /**
     * @return bool True, if any of the associated UpdateResultets contains has
     * a failure, otherwiese false.
     */
    public function hasFailure(): bool
    {
        foreach ($this->map as $value) {
            if ($value->hasFailure()) {
                return true;
            }
        }

        return false;
    }

    /* region Interface ArrayAccess */
    /**
     * @codeCoverageIgnore
     */
    public function offsetSet($offset, $value): void
    {
        if (is_null($offset)) {
            $this->map[] = $value;
        } else {
            $this->map[$offset] = $value;
        }
    }

    /**
     * @codeCoverageIgnore
     */
    public function offsetExists($offset): bool
    {
        return isset($this->map[$offset]);
    }

    /**
     * @codeCoverageIgnore
     */
    public function offsetUnset($offset): void
    {
        unset($this->map[$offset]);
    }

    /**
     * @codeCoverageIgnore
     */
    public function offsetGet($offset)
    {
        return isset($this->map[$offset]) ? $this->map[$offset] : null;
    }
    /* endregion */

    /* region Interface Countable */
    /**
     * @codeCoverageIgnore
     */
    public function count(): int
    {
        return count($this->map);
    }
    /* endregion */

    /* region Interface IteratorAggregate */
    /**
     * @codeCoverageIgnore
     */
    public function getIterator(): Traversable
    {
        yield from $this->map;
    }
    /* endregion */
}
