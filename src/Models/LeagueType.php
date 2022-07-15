<?php

declare(strict_types=1);

namespace Tobb10001\H4aIntegration\Models;

/**
 * Types of Leagues that are available.
 * Used to distinguish which particular competition a game (the associated
 * LeagueData) belongs to.
 */
enum LeagueType: string
{
case League = 'league';
case Cup = 'cup';
    }
