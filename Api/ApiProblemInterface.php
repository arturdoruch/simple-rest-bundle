<?php

namespace ArturDoruch\SimpleRestBundle\Api;

/**
 * @author Artur Doruch <arturdoruch@interia.pl>
 */
interface ApiProblemInterface
{
    /**
     * Gets API problem properties as array.
     *
     * @return array
     */
    public function toArray(): array;
}
