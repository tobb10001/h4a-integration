<?php

declare(strict_types=1);

namespace Tobb10001\H4aIntegration\Util;

use Tobb10001\H4aIntegration\Exceptions\HttpException;

class HttpClient
{
    /**
     * @param string $url
     * @param array<int, mixed> $curl_opts Additional options to be set for
     * the curl_handle.
     * @throws HttpException
     */
    public function get(string $url, array $curl_opts = []): string
    {
        $ch = curl_init($url);

        if ($ch === false) {
            throw new HttpException("Could not initialize curl handle.");
        }

        $curl_opt_defaults = [
            CURLOPT_POST => 0,
            CURLOPT_RETURNTRANSFER => true,
        ];

        // since the options are numeric, I don't trust array_merge here.
        foreach ($curl_opt_defaults as $key => $value) {
            if (!array_key_exists($key, $curl_opts)) {
                $curl_opts[$key] = $value;
            }
        }

        curl_setopt_array($ch, $curl_opts);

        $response = curl_exec($ch);

        if (!is_string($response)) {
            throw new HttpException("Error on curl request: " . curl_error($ch));
        }

        // omit curl_close, since it does nothing as per PHP 8.0.0
        // https://www.php.net/manual/en/function.curl-close.php

        return $response;
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
        if (empty($response)) {
            return null;
        }

        return json_decode($response, $associative, $depth, $flags);
    }
}
