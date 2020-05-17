<?php

namespace ArturDoruch\SimpleRestBundle\Exception;

/**
 * @author Artur Doruch <arturdoruch@interia.pl>
 */
interface HttpExceptionInterface
{
    /**
     * Gets HTTP status code.
     *
     * @return int
     */
    public function getStatusCode(): int;

    /**
     * Gets error details.
     *
     * @return array
     */
    public function getDetails(): array;

    /**
     * Gets error type.
     *
     * @return string
     */
    public function getType(): ?string;
}
