<?php

namespace Drewlabs\HttpClient\Core;

class ClientHelpers
{
    /**
     * Retry an operation a given number of times.
     *
     * @param  int  $times
     * @param  callable  $callback
     * @param  int  $sleep
     * @param  callable|null  $when
     * @return mixed
     *
     * @throws \Exception
     */
    public static function retry($times, callable $callback, $sleep = 0, $when = null)
    {
        $attempts = 0;
        $iterations = 0;
        beginning: $attempts++;
        $iterations++;
        try {
            return $callback($attempts);
        } catch (\Exception $e) {
            if (($iterations === $times) || ($when && !$when($e))) {
                throw $e;
            }
            if ($sleep) {
                usleep($sleep * 1000);
            }
            goto beginning;
        }
    }
}
