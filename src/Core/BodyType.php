<?php

namespace Drewlabs\HttpClient\Core;

/** @package Drewlabs\HttpClient\Core */
class BodyType
{
    /**
     * MULTIPART HTTP REQUEST
     * 
     * @var string
     */
    const MULTIPART = 'multipart';

    /**
     * JSON HTTP REQUEST
     * 
     * @var string
     */
    const JSON = 'json';

    /**
     * FORM DATA HTTP REQUEST
     * 
     * @var string
     */
    const FORM_DATA = 'form_params';
}