<?php

declare(strict_types=1);

namespace Tobb10001\H4aIntegration\Util;

interface HttpClientInterface
{
    public function get(string $url): string;

    public function getJson(
        string $url,
        ?bool $associative = true,
        int $depth = 512,
        int $flags = 0
    ): mixed;
}
