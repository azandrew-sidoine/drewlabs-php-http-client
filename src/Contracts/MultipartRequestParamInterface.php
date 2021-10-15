<?php

namespace Drewlabs\HttpClient\Contracts;

interface MultipartRequestParamInterface
{
    /**
     * Return the name associated with the file parameter
     *
     * @return string
     */
    public function name();

    /**
     * Returns the client original / original name of the file
     *
     * @return string|null
     */
    public function filename();

    /**
     * Return the binary file attached the object
     *
     * @return resource|string
     */
    public function content();

    /**
     * List of content-disposition headers attached to the file request
     *
     * @return array
     */
    public function headers();
}