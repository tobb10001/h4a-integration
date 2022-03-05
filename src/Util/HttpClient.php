<?php

declare(strict_types=1);

namespace Tobb10001\H4aIntegration\Util;

class HttpClient
{
    public function get(string $url): ?string
    {
        // TODO
        return "";
    }

    /**
     * @param string $url
     * @param ?bool $associative Passed to json_decode().
     * @param int<1, max> $depth Passed to json_decode().
     * @param int $flags Passed to json_decode().
     * @return mixed The response, or null if the request was unsuccessful.
     */
    public function getJson(
        string $url,
        ?bool $associative = true,
        int $depth = 512,
        int $flags = 0
    ): mixed {
        $response = $this->get($url);
        if (is_null($response)) {
            return null;
        }

        return json_decode($response, $associative, $depth, $flags);
    }
}
