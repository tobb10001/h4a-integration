<?php

declare(strict_types=1);

namespace Tobb10001\H4aIntegration\Util;

/**
 * Summarize whether the steps taken to update a team were successful or not.
 */
class UpdateResultlet
{
    public const NOT_TRIED = - 0x1;

    public const SUCCESS = 0x0;

    public const HTTP_EXCEPTION = 0x1;


    /**
     * @var int<-1, max> $leagueStatus
     * Stores the result of the update process. The update can be considered
     * successful, if this field is set to UpdateResultlet::SUCCESS
     */
    public int $leagueStatus = self::SUCCESS;

    /**
     * @var ?string $leagueErrorMessage
     * If $leagueSuccess != UpdateResultlet::SUCCESS, then this field will hold
     * a message to describe why the update failed.
     * In all other cases this field has no function and SHOULD remain null.
     */
    public ?string $leagueErrorMessage = null;

    /**
     * @return bool True, if any operation associated with this instance was
     * unsuccessful, otherwise false.
     */
    public function hasFailure(): bool
    {
        return $this->leagueStatus > 0;
    }
}
