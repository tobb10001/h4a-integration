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
     * Stores the result of the league update process. The update can be considered
     * successful, if this field is set to UpdateResultlet::SUCCESS
     */
    public int $leagueStatus = self::SUCCESS;

    /**
     * @var int<-1, max> $cupStatus
     * Stores the result of the cup update process. The update can be considered
     * successful, if this field is set to UpdateResultlet::SUCCESS
     */
    public int $cupStatus = self::SUCCESS;

    /**
     * @var ?string $leagueErrorMessage
     * If $leagueSuccess != UpdateResultlet::SUCCESS, then this field will hold
     * a message to describe why the update failed.
     * In all other cases this field has no function and SHOULD remain null.
     */
    public ?string $leagueErrorMessage = null;

    /**
     * @var ?string $cupErrorMessage
     * If $cupSuccess != UpdateResultlet::SUCCESS, then this field will hold
     * a message to describe why the update failed.
     * In all other cases this field has no function and SHOULD remain null.
     */
    public ?string $cupErrorMessage = null;

    /**
     * @param string $message Message to set.
     * @param int<-1, max> $status Status to set. One of the classes constants.
     */
    public function leagueFailure(string $message, int $status): void
    {
        $this->leagueStatus = $status;
        $this->leagueErrorMessage = $message;
    }

    /**
     * @param string $message Message to set.
     * @param int<-1, max> $status Status to set. One of the classes constants.
     */
    public function cupFailure(string $message, int $status): void
    {
        $this->cupStatus = $status;
        $this->cupErrorMessage = $message;
    }

    /**
     * @return bool True, if any operation associated with this instance was
     * unsuccessful, otherwise false.
     */
    public function hasFailure(): bool
    {
        return $this->leagueStatus > 0 && $this->cupStatus > 0;
    }
}
