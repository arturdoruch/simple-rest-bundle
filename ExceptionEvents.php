<?php

namespace ArturDoruch\SimpleRestBundle;

/**
 * @author Artur Doruch <arturdoruch@interia.pl>
 */
class ExceptionEvents
{
    /**
     * The event is dispatched when API endpoint has been requested and exception was thrown.
     *
     * @Event("Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent").
     */
    const KERNEL_EXCEPTION = 'arturdoruch.simple_rest.kernel_exception';
}
 