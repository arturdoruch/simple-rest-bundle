<?php

namespace ArturDoruch\SimpleRestBundle\Error;

use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @author Artur Doruch <arturdoruch@interia.pl>
 */
class ErrorResponse extends JsonResponse
{
    /**
     * @param Error $error
     * @param array $headers
     */
    public function __construct(Error $error, array $headers = [])
    {
        //$headers = array_merge(['Content-Type' => 'application/problem+json'], $headers);
        $headers = array_merge(['Content-Type' => 'application/json'], $headers);

        parent::__construct($error->toArray(), $error->getStatusCode(), $headers);
    }
}
 