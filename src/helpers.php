<?php

if (!function_exists('array_is_assoc')) {
    /**
     * Checks if an array is an associative array.
     *
     * @return bool
     */
    function array_is_assoc(array $value)
    {
        return array_keys($value) !== range(0, count($value) - 1);
    }
}